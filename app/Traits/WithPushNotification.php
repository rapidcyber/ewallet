<?php

namespace App\Traits;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

trait WithPushNotification
{

    public function sendPushNotification(Notification $notification): bool
    {
        if (empty($notification->recipient->fcm_token)) {
            return false;
        }

        $firebase = (new Factory)->withServiceAccount(storage_path('/firebase') . '/private-key.json');
        $messaging = $firebase->createMessaging();
        $message = CloudMessage::new()->fromArray([
            'notification' => [
                'body' => $notification->message,
            ],

        ])
            ->withData(['notif_id' => $notification->id])
            ->toToken($notification->recipient->fcm_token);

        try {
            $messaging->send($message);
            return true;
        } catch (MessagingException $e) {
            Log::error('sendPushNotification: ' . $e->getMessage());
            return false;
        }
    }
}
