<?php

namespace App\Http\Controllers\Bill;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bill\BillDetailsRequest;
use App\Http\Requests\Bill\BillListRequest;
use App\Http\Requests\Bill\DeleteRequest;
use App\Http\Requests\Bill\ShareBillRequest;
use App\Http\Requests\Bill\ShareListRequest;
use App\Http\Requests\Bill\ShareRemoveRequest;
use App\Models\ShareBill;
use App\Models\User;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use Exception;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{


    use WithEntity, WithHttpResponses, WithNumberGeneration, WithNotification;

    /**
     * Listing of bills
     * 
     * @param \App\Http\Requests\Bill\BillListRequest $request
     * @return void
     */
    public function list(BillListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;
        $status = $validated['status'] ?? 'all';

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }


        $query = $entity->bills()->with('fields');
        if ($status == 'paid') {
            $query = $query->whereNotNull('payment_date');
        } else if ($status == 'due') {
            /// Date now is greater than the due_date and not paid.
            $query = $query->where('due_date', '<', now()->format('Y-m-d'))->whereNull('payment_date');
        } else if ($status == 'upcoming') {
            /// Date now is greater than the due_date and not paid.
            $query = $query->where('due_date', '>=', now()->format('Y-m-d'))->whereNull('payment_date');
        } else {
            /// None, retrieve all bills..
        }

        $paginate = $query->orderByDesc('due_date')
            ->orWhereHas(
                'shared_bills',
                function ($q) use ($entity, $status) {
                    $q->where('entity_id', $entity->id);
                    $q->where('entity_type', get_class($entity));
                    if ($status == 'paid') {
                        $q->whereNotNull('payment_date');
                    } else if ($status == 'due') {
                        /// Date now is greater than the due_date and not paid.
                        $q->where('due_date', '<', now()->format('Y-m-d'))->whereNull('payment_date');
                    } else if ($status == 'upcoming') {
                        /// Date now is greater than the due_date and not paid.
                        $q->where('due_date', '>=', now()->format('Y-m-d'))->whereNull('payment_date');
                    } else {
                        /// None, retrieve all bills..
                    }
                }
            )
            ->paginate(
                $per_page,
                ['*'],
                'bills',
                $page
            );

        foreach ($paginate->items() as $bill) {
            if ($bill->entity_id == $entity->id and $bill->entity_type == get_class($entity)) {
                $bill->shared = false;
            } else {
                $bill->shared = true;
            }
        }

        return $this->success([
            'bills' => $paginate->items(),
            'last_page' => $paginate->lastPage(),
            'total_item' => $paginate->total(),
        ]);
    }

    /**
     * 
     * @param \App\Http\Requests\Bill\BillDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(BillDetailsRequest $request)
    {
        $validated = $request->validated();
        $ref_no = $validated['ref_no'];
        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = $entity->bills()
            ->where('ref_no', $ref_no)
            ->orWhereHas(
                'shared_bills',
                function ($q) use ($entity, $ref_no) {
                    $q->where('ref_no', $ref_no);
                    $q->where('entity_id', $entity->id);
                    $q->where('entity_type', get_class($entity));
                }
            )
            ->with('fields')
            ->first();

        if (empty($bill)) {
            return $this->error('Invalid bill reference number', 499);
        }

        if ($bill->entity_id == $entity->id and $bill->entity_type == get_class($entity)) {
            $bill->shared = false;
        } else {
            $bill->shared = true;
            $bill->load([
                'entity',
                'shared_bills' => function ($q) use ($entity): void {
                    $q->where([
                        'entity_id' => $entity->id,
                        'entity_type' => get_class($entity)
                    ]);
                }
            ]);

            $bill = $bill->toArray();
            unset($bill['entity']['profile']);
        }

        return $this->success($bill);
    }


    /**
     * Summary of delete
     * @param \App\Http\Requests\Bill\DeleteRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function delete(DeleteRequest $request)
    {
        $validated = $request->validated();
        $ref_no = $validated['ref_no'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = $entity->bills()->where('ref_no', $ref_no)->first();
        if (empty($bill)) {
            return $this->error("Invalid bill reference number", 499);
        }

        if (empty($bill->payment_date) == false) {
            return $this->error("Paid bills cannot be deleted", 499);
        }

        if (empty($bill->deleted_at) == false) {
            return $this->success();
        }

        DB::beginTransaction();
        try {
            $bill->delete();
            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of share
     * @param \App\Http\Requests\Bill\ShareBillRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function share(ShareBillRequest $request)
    {
        $validated = $request->validated();
        $ref_no = $validated['ref_no'];
        $recipient_number = str_replace('+', '', $validated['recipient_number']);
        $is_payable = $validated['payable'] ?? false;
        $note = $validated['note'] ?? null;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        if ($recipient_number == $entity->phone_number && get_class($entity) == User::class) {
            return $this->error('You cannot share this bill to yourself.', 499);
        }

        $bill = $entity->bills()->where('ref_no', $ref_no)->first();
        if (empty($bill)) {
            return $this->error("Invalid bill reference number", 499);
        }

        $recipient = User::where('phone_number', $recipient_number)
            ->whereHas('profile', function ($q) {
                $q->whereNotIn('status', ['deactivated']);
            })->first();


        if (empty($recipient)) {
            return $this->error("Recipient number is not registered to Repay", 499);
        }

        DB::beginTransaction();
        try {
            $share = ShareBill::firstOrNew([
                'bill_id' => $bill->id,
                'entity_id' => $recipient->id,
                'entity_type' => get_class($recipient),
            ], [
                'bill_id' => $bill->id,
                'entity_id' => $recipient->id,
                'entity_type' => get_class($recipient),
            ]);

            $share->is_payable = $is_payable;
            $share->note = $note;

            if (empty($share->id)) {
                $this->alert(
                    $recipient,
                    'bill',
                    $bill->ref_no,
                    "Someone shared you a bill!",
                );
            }
            $share->save();
            DB::commit();

            $share->load('entity');
            $share = $share->toArray();
            unset($share['entity']['profile']);
            return $this->success($share);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    /**
     * Summary of share_list
     * @param \App\Http\Requests\Bill\ShareListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function share_list(ShareListRequest $request)
    {
        $validated = $request->validated();
        $ref_no = $validated['ref_no'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = $entity->bills()->where('ref_no', $ref_no)->first();
        if (empty($bill)) {
            return $this->error("Invalid bill reference number", 499);
        }

        $list = $bill->shared_bills()
            ->with('entity.profile')
            ->get()
            ->toArray();

        $items = [];
        foreach ($list as $item) {
            unset($item['entity']['profile']);
            $items[] = $item;
        }

        return $this->success($items);
    }

    /**
     * Summary of share_remove
     * @param \App\Http\Requests\Bill\ShareRemoveRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function unshare(ShareRemoveRequest $request)
    {
        $validated = $request->validated();
        $ref_no = $validated['ref_no'];
        $recipient_number = str_replace('+', '', $validated['recipient_number']);

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $bill = $entity->bills()->where('ref_no', $ref_no)->first();
        if (empty($bill)) {
            return $this->error("Invalid bill reference number", 499);
        }

        $share = $bill->shared_bills()
            ->whereHas('entity', function ($q) use ($recipient_number) {
                $q->where('phone_number', $recipient_number);
            })->first();

        if (empty($share)) {
            return $this->success();
        }

        DB::beginTransaction();
        try {
            $share->delete();
            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
