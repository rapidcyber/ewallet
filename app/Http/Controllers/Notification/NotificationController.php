<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\AffiliationRequest;
use App\Http\Requests\Notification\NotificationListRequest;
use App\Http\Requests\Notification\NotificationDetailsRequest;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\SalaryType;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithNotification;
use DB;
use Exception;

class NotificationController extends Controller
{
    use WithEntity, WithHttpResponses, WithNotification;

    /**
     * 
     * 
     * 
     * @param \App\Http\Requests\Notification\NotificationListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function list(NotificationListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $notifications = Notification::select('id', 'type', 'status', 'message', 'ref_id', 'created_at', 'notification_module_id')
            ->where([
                'recipient_id' => $entity->id,
                'recipient_type' => get_class($entity),
            ])
            ->with('module')
            ->orderByDesc('created_at')
            ->paginate(
                $per_page,
                ['*'],
                'notifications',
                $page
            );

        $unread = Notification::where([
            'recipient_id' => $entity->id,
            'recipient_type' => get_class($entity),
            'status' => 'unread',
        ])->count();

        return $this->success([
            'unread_count' => $unread,
            'notifications' => $notifications->items(),
            'last_page' => $notifications->lastPage(),
            'total_item' => $notifications->total(),
        ]);
    }

    /**
     * 
     * 
     * 
     * @param \App\Http\Requests\Notification\NotificationDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function set_to_read(NotificationDetailsRequest $request)
    {
        $validated = $request->validated();
        $id = $validated['id'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $notif = $entity->notifications()->where('id', $id)
            ->with('module')->first();

        if (empty($notif)) {
            return $this->error('Invalid notification id', 499);
        }

        $notif->status = 'read';
        $notif->save();
        return $this->success(['message' => 'success']);
    }


    /**
     * Summary of affiliation
     * @param \App\Http\Requests\Notification\AffiliationRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function affiliation(AffiliationRequest $request)
    {
        $validated = $request->validated();
        $notif = auth()->user()->notifications()->where('id', $validated['id'])
            ->whereHas('module', function ($query) {
                $query->where('slug', 'affiliation');
            })
            ->first();

        if (empty($notif)) {
            return $this->error('Invalid notification ID', 499);
        }

        $merchant = Merchant::where('account_number', $notif->ref_id)->first();
        $notif->responded_at = now();
        $notif->save();

        if (!$validated['response']) {
            return $this->success();
        }

        $data = [
            'salary' => $notif->extras['salary'],
            'occupation' => $notif->extras['occupation'],
            'employee_role_id' => EmployeeRole::where('slug', $notif->extras['role_id'])->first()->id,
            'salary_type_id' => SalaryType::where('slug', $notif->extras['salary_type'])->first()->id,
        ];

        DB::beginTransaction();
        try {
            $employee = new Employee;
            $employee->fill([
                ...$data,
                'merchant_id' => $merchant->id,
                'user_id' => auth()->id(),
            ]);
            $employee->save();
            $this->alert(
                $merchant,
                'notification',
                auth()->id(),
                auth()->user()->name . ' has accepted your employee invitation.',
            );

            DB::commit();
            return $this->success();
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
