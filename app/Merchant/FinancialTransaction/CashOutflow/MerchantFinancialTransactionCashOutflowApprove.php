<?php

namespace App\Merchant\FinancialTransaction\CashOutflow;

use App\Models\Employee;
use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionRequest;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\User;
use App\Traits\Traits\WithStringManipulation;
use App\Traits\WithAllBankFunctions;
use App\Traits\WithBalance;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use App\View\Components\Card\TransactionDetails;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionCashOutflowApprove extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithValidPhoneNumber, WithNotification, WithNumberGeneration, WithBalance, WithStringManipulation;
    use WithAllBankFunctions;

    public Merchant $merchant;
    public Employee $employee;
    public $filter = 'pending';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $approveModal = false;
    public $rejectModal = false;

    #[Locked]
    public $transactionDetails;

    protected $allowedOrderByFieldName = [
        'total_amount',
        'created_at',
    ];

    protected $allowedFilters = ['pending', 'approved', 'rejected'];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->employee = $this->merchant->employees()->where('user_id', auth()->id())->firstOrFail();
    }

    #[Computed]
    public function available_balance()
    {
        return $this->merchant->latest_balance()->first()->amount ?? 0;
    }

    public function updatedFilter()
    {
        if (! in_array($this->filter, $this->allowedFilters)) {
            $this->filter = 'pending';
        }

        $this->reset(['transactionDetails']);
        $this->resetPage();
    }

    #[Computed]
    public function get_pending_count()
    {
        return $this->merchant->transaction_requests()->whereNull(['approved_at', 'processed_by'])->count();
    }

    #[Computed]
    public function get_approved_count()
    {
        return $this->merchant->transaction_requests()->whereNotNull('approved_at')->whereNotNull('processed_by')->count();
    }

    #[Computed]
    public function get_rejected_count()
    {
        return $this->merchant->transaction_requests()->whereNull(['approved_at'])->whereNotNull('deleted_at')->withTrashed()->count();
    }

    public function show_transaction_details($id)
    {
        if ($this->transactionDetails && $this->transactionDetails['id'] === $id) {
            return $this->transactionDetails = null;
        }

        $transaction_request = $this->merchant->transaction_requests()
            ->where('id', $id)
            ->with(['recipient' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile'],
                ]);
            }, 'type', 'creator.user.profile']);

        if ($this->filter === 'rejected') {
            $transaction_request = $transaction_request->withTrashed();
        }

        $transaction_request = $transaction_request->first();

        if (! $transaction_request) {
            return session()->flash('error', 'Transaction request not found');
        }

        $this->transactionDetails = [
            'id' => $transaction_request->id,
            'label' => 'Send to:',
            'amount' => $transaction_request->amount,
            'recipient' => $transaction_request->recipient->name,
            'service_fee' => $transaction_request->service_fee,
            'total' => $transaction_request->amount + $transaction_request->service_fee,
            'type' => $transaction_request->type->name,
            'phone_number' => $transaction_request->phone_number,
            'email' => $transaction_request->email,
            'message' => $transaction_request->message,
            'created_by' => $transaction_request->creator->user->name,
            'created_at' => $transaction_request->created_at->timezone('Asia/Manila')->format('F d, Y h:i A'),
        ];

        if ($transaction_request->processed_by == null && $transaction_request->approved_at == null && $transaction_request->deleted_at == null) {
            $this->transactionDetails['allow_actions'] = true;
        } else {
            $this->transactionDetails['allow_actions'] = false;
        }

        if ($transaction_request->recipient_type === User::class) {
            $this->transactionDetails['recipient'] = $this->mask_name($transaction_request->recipient->name);
            $this->transactionDetails['recipient_phone_number'] = $this->format_phone_number_for_display($transaction_request->recipient->phone_number, $transaction_request->recipient->phone_iso);
        }

        if ($transaction_request->type->code === 'PS') {
            $this->transactionDetails['info'] = [
                'Employee Name' => $transaction_request->extras['employee_name'],
                'Occupation' => $transaction_request->extras['occupation'],
                'Salary' => $transaction_request->extras['salary'],
                'Salary Type' => $transaction_request->extras['salary_type'],
            ];

            if ($transaction_request->extras['salary_type'] === 'Per Day') {
                $this->transactionDetails['info']['Days Worked'] = $transaction_request->extras['days_worked'];
                $this->transactionDetails['info']['Deductions'] = $transaction_request->extras['deductions'];
            }

        } elseif ($transaction_request->type->code === 'CO') {
            $this->transactionDetails['recipient'] = $transaction_request->extras['bank_name'] ?? '';
            $this->transactionDetails['recipient_phone_number'] = $transaction_request->extras['account_number'];

            $this->transactionDetails['info'] = [
                'Bank Name' => $transaction_request->extras['bank_name'] ?? '',
                'Account Number' => $transaction_request->extras['account_number'],
                'Account Name' => $transaction_request->extras['account_name'],
            ];
        }
    }

    public function reset_modal()
    {
        $this->reset(['request_id']);
    }

    public function action_set($request_id)
    {
        $transactionDetails = $this->merchant->transaction_requests()
            ->where('id', $request_id)
            ->whereNull(['approved_at', 'processed_by'])
            ->first();

        if (! $transactionDetails) {
            $this->reset(['approveModal', 'rejectModal']);
            return session()->flash('error', 'Transaction request not found');
        }
    }

    public function approve_request()
    {
        if (!isset($this->transactionDetails['id'])) {
            return session()->flash('error', 'Transaction request not found');
        }

        $transaction_request = $this->merchant->transaction_requests()
            ->where('id', $this->transactionDetails['id'])
            ->whereNull(['approved_at', 'processed_by'])
            ->with([
                'provider',
                'channel',
                'type'
            ])
            ->first();

        if (! $transaction_request) {
            return session()->flash('error', 'Transaction request not found');
        }

        if (! $this->is_sufficient($this->merchant, ($transaction_request->amount + $transaction_request->service_fee))) {
            return session()->flash('error', 'Insufficient balance');
        }

        if ($transaction_request->type->code === 'CO') {
            if ($transaction_request->channel->slug === 'instapay') {
                return $this->transfer_instapay($transaction_request);
            } elseif ($transaction_request->channel->slug === 'pesonet') {
                return $this->transfer_pesonet($transaction_request);
            }
        }

        DB::beginTransaction();
        try {
            $transaction_request->approved_at = now();
            $transaction_request->processed_by = $this->employee->id;

            $transaction_request->save();

            if (in_array($transaction_request->recipient_type, [User::class, Merchant::class])) {
                $transaction = new Transaction;
                $transaction->fill([
                    'sender_id' => $this->merchant->id,
                    'sender_type' => Merchant::class,
                    'recipient_id' => $transaction_request->recipient_id,
                    'recipient_type' => $transaction_request->recipient_type,
                    'txn_no' => $this->generate_transaction_number(),
                    'ref_no' => $this->generate_transaction_reference_number(
                        $transaction_request->provider,
                        $transaction_request->channel,
                        $transaction_request->type
                    ),
                    'transaction_provider_id' => $transaction_request->provider_id,
                    'transaction_channel_id' => $transaction_request->channel_id,
                    'transaction_type_id' => $transaction_request->type_id,
                    'amount' => $transaction_request->amount,
                ]);
    
                $transaction->save();
    
                $this->credit($this->merchant, $transaction);
                $this->debit($transaction_request->recipient, $transaction);

                $message = "You have received PHP" . number_format($transaction->amount, 2) . " from " . $this->merchant->name . ". Transaction number: " . $transaction->txn_no;

                if ($transaction_request->type->name === 'Payroll Salary') {
                    $message = 'You have received a salary payment of PHP' . number_format($transaction->amount, 2) . ' from ' . $this->merchant->name . ".\n\nTransaction number: " . $transaction->txn_no . ".";
                }

                $this->alert(
                    $transaction_request->recipient,
                    'transaction',
                    $transaction->txn_no,
                    $message,
                    
                );
            }

            DB::commit();
            session()->flash('success', 'Transaction request approved successfully.');
        } catch (\Exception $ex) {
            Log::error('MerchantFinancialTransactionCashOutflowApprove.approve_request: ' . $ex->getMessage());
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again later.');
        }
        
        return $this->reset(['approveModal', 'request_id']);
    }

    private function transfer_instapay(TransactionRequest $transaction_request)
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'instapay')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
    
        [$token, $tdt] = $this->generate_token();

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'IPAYPTR',
            'acctno' => config('services.alb.opc'), // Origination Account Number
            'acctno2' => $transaction_request->extras['account_number'], // Destination Account Number
            'ln' => $transaction_request->extras['account_name'], // Destination Account Name
            'amt' => $transaction_request->amount,
            'dbk' => $transaction_request->extras['selected_bank'],
            'ref_id' => $ref_no,
        ];

        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_instapay: ' . $response->body());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('MerchantFinancialTransactionCashOutflowCreate:transfer_instapay: ' . $data['ErrorMsg']);
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->merchant->id,
                'sender_type' => get_class($this->merchant),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $transaction_request->amount,
                'extras' => [
                    'inv' => $data['inv'],
                    'ibft_id_code' => $data['ibft_id_code']
                ],
                'service_fee' => $transaction_request->service_fee,
            ]);

            $transaction->save();

            $this->credit($this->merchant, $transaction);

            $transaction_request->processed_by = $this->employee->id;
            $transaction_request->approved_at = now();
            $transaction_request->save();

            DB::commit();

            $this->reset([
                'transactionDetails',
                'approveModal',
                'rejectModal'
            ]);

            session()->flash('success', value: 'Success!');
            return session()->flash('success_message', value: 'Transaction request approved successfully.');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowApprove:transfer_instapay: ' . $ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
    }

    private function transfer_pesonet(TransactionRequest $transaction_request)
    {
        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'pesonet')->first();
        $transaction_type = TransactionType::where('slug', 'cash_out')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);
    
        [$token, $tdt] = $this->generate_token();

        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'PNETPTR',
            'acctno' => config('services.alb.opc'), // Origination Account Number
            'acctno2' => $transaction_request->extras['account_number'], // Destination Account Number
            'ln' => $transaction_request->extras['account_name'], // Destination Account Name
            'amt' => $transaction_request->amount,
            'dbk' => $transaction_request->extras['selected_bank'],
            'ref_id' => $ref_no,
        ];

        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            Log::error('MerchantFinancialTransactionCashOutflowApprove:transfer_pesonet: ' . $response->body());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            Log::error('MerchantFinancialTransactionCashOutflowApprove:transfer_pesonet: ' . $data['ErrorMsg']);
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        DB::beginTransaction();
        try {
            $transaction = new Transaction;

            $transaction->fill([
                'sender_id' => $this->merchant->id,
                'sender_type' => get_class($this->merchant),
                'recipient_id' => $provider->id,
                'recipient_type' => get_class($provider),
                'txn_no' => $this->generate_transaction_number(),
                'ref_no' => $ref_no,
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $transaction_type->id,
                'transaction_status_id' => TransactionStatus::where('slug', 'pending')->first()->id,
                'currency' => 'PHP',
                'amount' => $transaction_request->amount,
                'extras' => [
                    'peso_ref_id' => $data['peso_ref_id'],
                ],
                'service_fee' => $transaction_request->service_fee,
            ]);

            $transaction->save();

            $this->credit($this->merchant, $transaction);

            $transaction_request->processed_by = $this->employee->id;
            $transaction_request->approved_at = now();
            $transaction_request->save();

            DB::commit();

            $this->reset([
                'transactionDetails',
                'approveModal',
                'rejectModal'
            ]);

            session()->flash('success', value: 'Success!');
            return session()->flash('success_message', value: 'Transaction request approved successfully.');
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('MerchantFinancialTransactionCashOutflowApprove:transfer_instapay: ' . $ex->getMessage());
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
    }

    public function reject_request()
    {
        if (!isset($this->transactionDetails['id'])) {
            return session()->flash('error', 'Transaction request not found');
        }

        $transactionDetails = $this->merchant->transaction_requests()
            ->where('id', $this->transactionDetails['id'])
            ->whereNull(['approved_at', 'processed_by'])
            ->first();

        if (! $transactionDetails) {
            return session()->flash('error', 'Transaction request not found');
        }

        DB::beginTransaction();
        try {
            $transactionDetails->processed_by = $this->employee->id;

            $transactionDetails->save();
            $transactionDetails->delete();

            DB::commit();
            session()->flash('success', 'Transaction request has been rejected.');
        } catch (\Exception $ex) {
            Log::error('MerchantFinancialTransactionCashOutflowApprove.reject_request: ' . $ex->getMessage());
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again later.');
        }
        
        return $this->reset(['rejectModal', 'request_id']);
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $transaction_requests = $this->merchant->transaction_requests()
            ->selectRaw('transaction_requests.* , transaction_requests.amount + transaction_requests.service_fee as total_amount')
            ->with(['recipient' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile'],
                ]);
            }, 'type', 'processor.user.profile']);

        if (! in_array($this->filter, $this->allowedFilters)) {
            $this->filter = 'pending';
        }

        $transaction_requests = match ($this->filter) {
            'pending' => $transaction_requests->whereNull(['approved_at', 'processed_by']),
            'approved' => $transaction_requests->whereNotNull('approved_at')->whereNotNull('processed_by'),
            'rejected' => $transaction_requests->whereNull(['approved_at'])->whereNotNull('deleted_at')->withTrashed(),
        };

        $transaction_requests = $transaction_requests->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($transaction_requests);

        return view('merchant.financial-transaction.cash-outflow.merchant-financial-transaction-cash-outflow-approve')->with([
            'transaction_requests' => $transaction_requests,
            'elements' => $elements,
        ]);
    }
}
