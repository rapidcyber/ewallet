<?php

namespace App\Traits;

use App\Models\SmsRecords;
use Aws\Result;
use Aws\Sns\SnsClient;

trait WithSMS
{
    public function sendSMS($message, $phonenumber, $context, $sender = 'Repay'): ?Result
    {
        /// Replace all "+"
        $phonenumber = str_replace('+', '', $phonenumber);

        if (config('app.debug')) {
            /// do not send sms on tests environment ...
            return null;
        } else {
            $params = [
                'credentials' => [
                    'key' => config('services.ses.key'),
                    'secret' => config('services.ses.secret'),
                ],
                'region' => config('services.ses.region'),
                'version' => 'latest',
            ];
            $sns = new SnsClient($params);
            $args = [
                'MessageAttributes' => [
                    'AWS.SNS.SMS.SenderID' => [
                        'DataType' => 'String',
                        'StringValue' => $sender,
                    ],
                    'AWS.SNS.SMS.SMSType' => [
                        'DataType' => 'String',
                        'StringValue' => 'Transactional',
                    ],
                ],
                'Message' => $message,
                'PhoneNumber' => "+$phonenumber",
            ];

            $result = $sns->publish($args);
            SmsRecords::create([
                'recipient' => $phonenumber,
                'context' => $context,
                'env' => app()->environment(),
                'message_id' => $result['MessageId'],
                'status' => $result['@metadata']['statusCode'],
            ]); // Tracking
            return $result;
        }
    }
}
