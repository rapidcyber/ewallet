<?php

namespace App\Http\Controllers\Invoice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Invoice\E2MIssueRequest;
use App\Http\Requests\Invoice\E2PIssueRequest;
use App\Http\Requests\Invoice\InvoiceDetailsRequest;
use App\Http\Requests\Invoice\InvoiceListRequest;
use App\Models\Invoice;
use App\Models\InvoiceInclusion;
use App\Models\InvoiceItem;
use App\Models\Merchant;
use App\Models\User;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use DateTime;
use Exception;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{

    use WithEntity,
        WithNotification,
        WithHttpResponses,
        WithNumberGeneration,
        WithValidPhoneNumber;

    /**
     * Summary of issue
     * @param \App\Models\Merchant $merchant
     * @param \App\Models\Merchant|\App\Models\User $recipient
     * @param \DateTime $due_date
     * @param array $items
     * @param array $inclusions
     * @param float $minimum_partial
     * @param mixed $message
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    private function issue(
        Merchant|User $sender,
        Merchant|User $recipient,
        ?string $currency,
        ?string $message,
        array $items,
        string $due_date,
        array $inclusions,
        float $minimum_partial,
    ) {
        $invoice = new Invoice;
        $invoice->currency = $currency;
        $invoice->sender_id = $sender->id;
        $invoice->sender_type = get_class($sender);
        $invoice->recipient_id = $recipient->id;
        $invoice->recipient_type = get_class($recipient);
        $invoice->invoice_no = $this->generate_invoice_number($sender, $recipient);
        $invoice->message = $message;
        $invoice->due_date = $due_date;
        $invoice->minimum_partial = $minimum_partial ?? 0;

        try {
            DB::transaction(function () use ($invoice, $recipient, $items, $inclusions, $sender) {
                $invoice->save();

                foreach ($items as $item) {
                    $invoice_item = new InvoiceItem;
                    $invoice_item->invoice_id = $invoice->id;
                    $invoice_item->name = $item['name'];
                    $invoice_item->description = $item['description'];
                    $invoice_item->price = $item['price'];
                    $invoice_item->quantity = $item['quantity'];
                    $invoice_item->save();
                }

                foreach ($inclusions as $inclusion) {
                    $invoice_inclusion = new InvoiceInclusion;
                    $invoice_inclusion->invoice_id = $invoice->id;
                    $invoice_inclusion->name = $inclusion['name'];
                    $invoice_inclusion->amount = $inclusion['amount'];
                    $invoice_inclusion->deduct = $inclusion['deduct'];
                    $invoice_inclusion->save();
                }

                $sender_name = get_class($sender) == User::class ? "+$sender->phone_number" : $sender->name;

                $this->alert(
                    $recipient,
                    'invoice',
                    $invoice->invoice_no,
                    "You have received an invoice from $sender_name. Invoice no: $invoice->invoice_no."
                );
            });

            
            $invoice = $this->attach_query(Invoice::where('id', $invoice->id))->first();
            if (empty($invoice)) {
                return $this->error('Invalid invoice number', 499);
            }
    
            $invoice->load(['items', 'inclusions', 'sender', 'recipient']);
            $invoice->inbound = false;

            return $this->success($this->to_array($invoice));
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }

    /**
     * Summary of attach_query
     * @param mixed $query
     * @return mixed
     */
    private function attach_query($query) {
        return $query
        ->withSum('logs as total_paid', 'amount')
        ->withSum(['inclusions as additional_amount' => fn($query) => $query->where('deduct', 0)], 'amount')
        ->withSum(['inclusions as discounted_amount' => fn($query) => $query->where('deduct', 1)], 'amount')
        ->addSelect(['total_item_price' => InvoiceItem::query()
            ->whereColumn('invoice_id', 'invoices.id')
            ->selectRaw('sum(quantity * price) as total_item_price')
        ]);
    }

    /**
     * Transform invoice to array for response
     * 
     * @param \App\Models\Invoice $invoice
     * @return array
     */
    private function to_array(Invoice $invoice): array {
        $invoice = $invoice->toArray();
        unset(
            $invoice['sender']['profile'],
            $invoice['sender']['status'],
            $invoice['recipient']['profile'],
            $invoice['recipient']['status'],
        );

        return $invoice;
    }

    /**
     * Entity to Personal (Account) Invoice Issuance
     * 
     * @param \App\Http\Requests\Invoice\E2PIssueRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function E2PIssue(E2PIssueRequest $request)
    {
        $validated = $request->validated();

        $phone_iso = $validated['phone_iso'];
        $phone_number = $validated['phone_number'];
        $merc_ac = $validated['merc_ac'] ?? null;

        $phone_info = $this->phonenumber_info($phone_number, $phone_iso);
        if ($phone_info == false) {
            return $this->error('Invalid recipient phone number', 499);
        }

        $recipient = User::where(
            'phone_number',
            $phone_info->getCountryCode() . $phone_info->getNationalNumber(),
        )->first();
        
        $entity = $this->get(auth()->user(), $merc_ac);
        if (empty($entity)) {
            return $this->error('Invalid merchant account number', 499);
        }
        
        if (empty($recipient) || $this->is_same($entity, $recipient)) {
            return $this->error('Invalid recipient phone number', 499);
        }

        return $this->issue(
            $entity,
            $recipient,
            $validated['currency'] ?? 'PHP',
            $validated['message'] ?? '',
            $validated['items'],
            $validated['due_date'],
            $validated['inclusions'] ?? [],
            $validated['minimum_partial'] ?? 0,
        );
    }

    /**
     * Entity to Merchant Invoice Issuance
     * 
     * @param \App\Http\Requests\Invoice\E2MIssueRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function E2MIssue(E2MIssueRequest $request)
    {
        $validated = $request->validated();
        $account_number = $validated['account_number'];

        $recipient = Merchant::where('account_number', $account_number)->first();
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity) || $this->is_same($entity, $recipient)) {
            return $this->error('Invalid merchant account number', 499);
        }

        return $this->issue(
            $entity,
            $recipient,
            $validated['currency'] ?? 'PHP',
            $validated['message'] ?? '',
            $validated['items'],
            $validated['due_date'],
            $validated['inclusions'] ?? [],
            $validated['minimum_partial'] ?? 0,
        );
    }

    /**
     * Summary of list
     * 
     * @param \App\Http\Requests\Invoice\InvoiceListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(InvoiceListRequest $request) {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $start = $validated['start'] ?? 'week';
        $status = $validated['status'] ?? 'unpaid';
        $inbound = $validated['inbound'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $id = $entity->id;
        $type = get_class($entity);

        $end = now();
        $start_date = now();
        switch ($start) {
            case 'year':
                $start_date = $start_date->startOfYear();
                break;

            case 'quarter':
                $start_date = $start_date->subMonths(3)->startOfMonth();
                break;

            case 'month':
                $start_date = $start_date->startOfMonth();
                break;

            case 'week':
                $start_date = $start_date->subDays(7);
                break;

            default:
                $start_date = $start_date->startOfDay();
                break;
        }

        $query = Invoice::query();
        if ($inbound === null) { 
            $query = $query->where(function ($q) use ($id, $type) {
                $q->where(['sender_id' => $id, 'sender_type' => $type])
                    ->orWhere(function($q2) use ($id, $type) {
                        $q2->where(['recipient_id' => $id, 'recipient_type' => $type]);
                    });
            });
        } else if ($inbound) {
            $query = $entity->incoming_invoices()->where('type', 'payable');
        } else {           
            $query = $entity->outgoing_invoices()->where('type', 'payable');
        }

        if ($status == 'paid') {
            $query = $query->where('status', 'paid');
        } else {
            $query = $query->whereIn('status', ['unpaid', 'partial']);
        }

        $query = $this->attach_query($query);
        $paginate = $query->whereBetween('created_at', [
            $start_date->format('Y-m-d H:i:s'),
            $end->format('Y-m-d H:i:s'),
        ])
        ->with([
            'sender' => function ($q) {
                $q->constrain([
                    User::class => function ($query) {
                        $query->with(["profile"]);
                    },
                ]);
            }, 
            'recipient' => function ($q) {
                $q->constrain([
                    User::class => function ($query) {
                        $query->with(["profile"]);
                    },
                ]);
            }
        ])
        ->orderByDesc('due_date')
        ->paginate(
            $per_page,
            ['*'],
            'invoices',
            $page
        );

        $invoices = array_map(function ($invoice) use ($entity) {        
            $invoice->inbound = $this->is_inbound_invoice($invoice, $entity);
            return $this->to_array($invoice);
        }, $paginate->items());

        return $this->success([
            'invoices' => $invoices,
            'last_page' => $paginate->lastPage(),
            'total_item' => $paginate->total(),
        ]);
    }

    /**
     * Summary of details
     * 
     * @param \App\Http\Requests\Invoice\InvoiceDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(InvoiceDetailsRequest $request)
    {
        $validated = $request->validated();
        $invoice_no = $validated['invoice_no'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error('Invalid merchant account number', 499);
        }

        $id = $entity->id;
        $type = get_class($entity);
        $invoice = Invoice::where([
            'sender_id' => $id,
            'sender_type' => $type,
            'invoice_no' => $invoice_no
        ])->orWhere(function ($q) use ($id, $type, $invoice_no) {
            $q->where([
                'recipient_id' => $id,
                'recipient_type' => $type,
                'invoice_no' => $invoice_no
            ]);
        });
       
        $invoice = $this->attach_query($invoice)->first();
        if (empty($invoice)) {
            return $this->error('Invalid invoice number', 499);
        }

        $invoice->load(['items', 'inclusions', 'sender', 'recipient', 'logs' => function ($q) {
            $q->orderByDesc('created_at');
        }]);
        $invoice->inbound = $this->is_inbound_invoice($invoice, $entity);
        $invoice = $this->to_array($invoice);
        return $this->success($invoice);
    }
}
