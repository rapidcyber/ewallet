<?php

namespace App\Traits;
use App\Models\Merchant;
use App\Models\NotificationModule;
use App\Models\User;
use App\Models\Notification;

trait WithNotification
{

    use WithPushNotification;

    /**
     * Available Modules
     * - notification : general info - no needs action
     * - transaction : transaction info - view transaction (transaction no)
     * - invoice : invoice info - view invoice (invoice no)
     * - expired : for expired affiliation invitation (none)
     * - affiliation : for employee onboarding (merchant acc no)
     * - bill : for sharing bills. (bill ref no)
     * - order : for product order (order no)
     * - booking : for service bookings (booking id)
     * 
     * @param string $slug
     * @param bool $needs_action
     * @return \App\Models\NotificationModule
     */
    private function get_module(string $slug)
    {
        $module = NotificationModule::where('slug', $slug)->first();
        return $module;
    }

    public function alert(
        Merchant|User $recipient,
        string $module_slug,
        string $ref_id,
        string $message,
        ?array $extras = null,
    ) {

        $notif = new Notification;

        $notif->type = 'alert';
        $notif->status = 'unread';
        $notif->recipient_id = $recipient->id;
        $notif->recipient_type = get_class($recipient);

        $notif->ref_id = $ref_id;
        $notif->message = $message;
        $notif->extras = $extras;

        $notif->notification_module_id = $this->get_module($module_slug)->id;

        $this->sendPushNotification($notif);

        return $notif->save();
    }
}
