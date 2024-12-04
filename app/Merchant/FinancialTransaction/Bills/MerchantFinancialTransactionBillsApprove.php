<?php

namespace App\Merchant\FinancialTransaction\Bills;

use App\Models\BillingRequest;
use App\Models\Employee;
use App\Models\Merchant;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithECPayFunctions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantFinancialTransactionBillsApprove extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithECPayFunctions;

    public Employee $employee;
    public Merchant $merchant;

    public $filter = 'pending';
    
    public $biller_request_id = null;

    public $approveModal = false;
    public $rejectModal = false;

    #[Locked]
    public $billDetails;

    protected $allowedFilters = [
        'pending',
        'approved',
        'rejected',
    ];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
        $this->employee = $this->merchant->employees()->where('user_id', auth()->id())->firstOrFail();
    }

    public function updatedFilter()
    {
        if (! in_array($this->filter, $this->allowedFilters)) {
            $this->filter = 'pending';
        }

        $this->resetPage();
    }

    #[Computed]
    public function get_pending_count()
    {
        return $this->merchant->billing_requests()->whereNull(['approved_at', 'approved_by'])->count();
    }

    #[Computed]
    public function get_approved_count()
    {
        return $this->merchant->billing_requests()->whereNotNull('approved_at')->whereNotNull('approved_by')->count();
    }

    #[Computed]
    public function get_rejected_count()
    {
        return $this->merchant->billing_requests()->whereNull(['approved_at', 'approved_by'])->whereNotNull('deleted_at')->withTrashed()->count();
    }

    // For Bill Payment
    #[Computed(persist: true, seconds: 3600, cache: true, key: 'ecpay_billers_list')]
    public function billers_list()
    {
        $token = $this->generate_ecpay_token();
        $response = $this->ecpay_get_request('/api/v1/Ecbills/Billers', [], $token);
        if ($response->failed()) {
            return [];
        }

        $data = json_decode($response->body(), true)['Data'];
        foreach ($data as $key => $value) {
            if (isset($value['Category'])) {
                $data[$key]['Category'] = str_replace(["\r", "\n"], '', $value['Category']);
                $data[$key]['Description'] = str_replace(["\r", "\n"], '', $value['Description']);
            }
        }

        return $data;
    }

    public function show_request($billing_request_id)
    {
        if ($this->billDetails && $this->billDetails['id'] === $billing_request_id) {
            return $this->billDetails = null;
        }

        $billing_request = $this->merchant->billing_requests()
            ->where('id', $billing_request_id)
            ->first();

        if (! $billing_request) {
            return session()->flash('error', 'Billing request not found');
        }

        $billing_key = array_search($billing_request->name, array_column($this->billers_list, 'Description'));

        $biller = $this->billers_list[$billing_key];

        if ($biller['Status'] !== true) {
            return session()->flash('error', 'This biller is not available at the moment');
        }

        $this->billDetails = [
            'id' => $billing_request->id,
            'name' => $biller['Description'],
            'category' => $biller['Category'],
            'remarks' => $biller['Remarks'],
            'amount' => $billing_request->amount,
            'service_charge' => $billing_request->service_charge,
            'total' => $billing_request->amount + $billing_request->service_charge,
            'infos' => $billing_request->infos,
            'created_at' => $billing_request->created_at,
        ];
    }

    public function reset_modal()
    {
        $this->reset(['biller_request_id']);
    }

    public function action_set($billing_request_id)
    {
        $billDetails = $this->merchant->billing_requests()
            ->where('id', $billing_request_id)
            ->whereNull(['approved_at', 'approved_by'])
            ->first();

        if (! $billDetails) {
            $this->reset(['approveModal', 'rejectModal']);
            return session()->flash('error', 'Billing request not found');
        }

        $this->biller_request_id = $billing_request_id;
    }

    public function approve_bill_request()
    {
        if (! $this->biller_request_id) {
            return session()->flash('error', 'Billing request not found');
        }

        $billing_request = $this->merchant->billing_requests()
            ->where('id', $this->biller_request_id)
            ->whereNull(['approved_at', 'approved_by'])
            ->first();

        if (! $billing_request) {
            return session()->flash('error', 'Billing request not found');
        }

        DB::beginTransaction();
        try {
            $billing_request->approved_at = now();
            $billing_request->approved_by = $this->employee->id;

            $billing_request->save();

            DB::commit();
            session()->flash('success', 'Billing request has been approved.');
        } catch (\Exception $ex) {
            Log::error('MerchantFinancialTransactionBillsApprove.approve_bill_request: '.$ex->getMessage());
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again later.');
        }

        return $this->reset(['biller_request_id', 'approveModal']);
    }
    
    public function reject_bill_request()
    {
        if (! $this->biller_request_id) {
            return session()->flash('error', 'Billing request not found');
        }

        $billing_request = $this->merchant->billing_requests()
            ->where('id', $this->biller_request_id)
            ->whereNull(['approved_at', 'approved_by'])
            ->first();

        if (! $billing_request) {
            return session()->flash('error', 'Billing request not found');
        }

        DB::beginTransaction();
        try {
            $billing_request->delete();

            DB::commit();
            session()->flash('success', 'Billing request has been rejected.');
        } catch (\Exception $ex) {
            Log::error('MerchantFinancialTransactionBillsApprove.reject_bill_request: '.$ex->getMessage());
            DB::rollBack();
            session()->flash('error', 'Something went wrong. Please try again later.');
        }

        return $this->reset(['biller_request_id', 'rejectModal']);
    }

    #[Layout('layouts.merchant.financial-transaction')]
    public function render()
    {
        $billing_requests = $this->merchant->billing_requests();

        if (! in_array($this->filter, $this->allowedFilters)) {
            $this->filter = 'pending';
        }

        $billing_requests = match ($this->filter) {
            'pending' => $billing_requests->whereNull(['approved_at', 'approved_by']),
            'approved' => $billing_requests->whereNotNull('approved_at')->whereNotNull('approved_by'),
            'rejected' => $billing_requests->whereNull(['approved_at', 'approved_by'])->whereNotNull('deleted_at')->withTrashed(),
        };

        $billing_requests = $billing_requests->paginate(10);

        $elements = $this->getPaginationElements($billing_requests);

        return view('merchant.financial-transaction.bills.merchant-financial-transaction-bills-approve')->with([
            'billing_requests' => $billing_requests,
            'elements' => $elements,
        ]);
    }
}
