<?php

namespace Database\Seeders;

use App\Models\ReturnOrder;
use App\Models\ReturnOrderLog;
use App\Models\ReturnOrderStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Seeder;

class ReturnOrderLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = ReturnOrderStatus::all();

        foreach (
            ReturnOrder::with(['status.parent_status', 'reason', 'product_order.buyer' => function (MorphTo $q) {
                $q->morphWith([
                    User::class => ['profile'],
                ]);
            }])->get() as $return_order
        ) {
            $createdAt = Carbon::parse($return_order->created_at);
            $updatedAt = Carbon::parse($return_order->updated_at);
            $totalDurationInDays = $createdAt->diffInDays($updatedAt);

            $dates = collect();

            $log = new ReturnOrderLog;
            $log->return_order_id = $return_order->id;
            $log->return_order_status_id = $return_order->return_order_status_id;
            $log->title = 'Buyer Submitted a return request';
            $log->description = 'Buyer ' . $return_order->product_order->buyer->name . ' submitted a return request for the following reason: ' . $return_order->reason->name;
            $log->created_at = $return_order->created_at;
            $log->updated_at = $return_order->created_at;
            $log->save();
            if ($return_order->status->parent_status?->slug == 'return_in_progress') {
                $log = new ReturnOrderLog;
                $log->return_order_id = $return_order->id;
                $log->return_order_status_id = $return_order->return_order_status_id;
                $log->title = 'Merchant accepted the return request';
                $log->description = 'Merchant has accepted the return request and the return process has started.';
                $log->created_at = $return_order->updated_at;
                $log->updated_at = $return_order->updated_at;
                $log->save();
            } elseif ($return_order->status->parent_status?->slug == 'rejected') {
                $date_between = fake()->dateTimeBetween($return_order->created_at, $return_order->updated_at);

                $has_accepted = fake()->boolean();
                if ($has_accepted) {
                    $log = new ReturnOrderLog;
                    $log->return_order_id = $return_order->id;
                    $log->return_order_status_id = $return_order->return_order_status_id;
                    $log->title = 'Merchant accepted the return request';
                    $log->description = 'Merchant has accepted the return request and the return process has started.';
                    $log->created_at = $date_between;
                    $log->updated_at = $date_between;
                    $log->save();
                }

                $log = new ReturnOrderLog;
                $log->return_order_id = $return_order->id;
                $log->return_order_status_id = $return_order->return_order_status_id;
                $log->title = 'Merchant rejected the return request';
                $log->description = 'The return request was rejected by the merchant.';
                $log->created_at = $return_order->updated_at;
                $log->updated_at = $return_order->updated_at;
                $log->save();
            } elseif ($return_order->status->parent_status?->slug == 'resolved') {
                $steps = fake()->numberBetween(1, 4);
                $interval_in_days = $totalDurationInDays / ($steps + 1);

                for ($count = 1; $count <= $steps; $count++) {
                    if ($count == $steps) {
                        $log = new ReturnOrderLog;
                        $log->return_order_id = $return_order->id;
                        $log->return_order_status_id = $return_order->return_order_status_id;
                        $log->title = 'Request has been resolved';
                        $log->description = 'The return request has been resolved.';
                        $log->created_at = $return_order->updated_at;
                        $log->updated_at = $return_order->updated_at;
                        $log->save();
                        continue;
                    }


                }
            }
        }
    }
}
