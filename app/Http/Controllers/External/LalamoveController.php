<?php

namespace App\Http\Controllers\External;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lalamove\LalamoveOrderRequest;
use App\Http\Requests\Lalamove\LalamoveQuotationRequest;
use App\Http\Requests\Lalamove\LalamoveWebhookRequest;
use App\Models\Balance;
use App\Models\LalamoveBalance;
use App\Models\LalamoveDriver;
use App\Models\LalamoveOrder;
use App\Models\LalamoveService;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\ProductOrder;
use App\Models\ProductOrderLog;
use App\Models\ShippingOption;
use App\Models\ShippingStatus;
use App\Models\Transaction;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionStatus;
use App\Models\TransactionType;
use App\Models\Warehouse;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithBalance;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use App\Traits\WithNumberGeneration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

class LalamoveController extends Controller
{
    use WithEntity, WithHttpResponses, WithNumberGeneration, WithImage, WithNotification, WithBalance;

    private function generate_signature($method, $endpoint, $bodyString)
    {
        $apiKey = config('services.lalamove.api_key');
        $apiSecret = config('services.lalamove.api_secret');

        $time = Carbon::now()->format('Uv'); //Unix Timestamp in milliseconds
        $rawSignature = "{$time}\r\n{$method}\r\n{$endpoint}\r\n\r\n{$bodyString}";
        $signature = hash_hmac('sha256', $rawSignature, $apiSecret);
        $token = "{$apiKey}:{$time}:{$signature}";

        return $token;
    }

    private function validate_signature($apiKey, $timestamp, $signature, $eventId, $eventType, $data)
    {
        $app_apiKey = config('services.lalamove.api_key');

        $apiSecret = config('services.lalamove.api_secret');
        $method = 'POST';
        $path = '/api/v3/lalamove/webhook';
        $bodyString = str_replace(':null', ':""', json_encode($data, JSON_UNESCAPED_SLASHES));

        $rawSignature = "{$timestamp}\r\n{$method}\r\n{$path}\r\n\r\n{$bodyString}";
        $app_signature = hash_hmac('sha256', $rawSignature, $apiSecret);

        $app_apiKey = config('services.lalamove.api_key');

        if ($app_apiKey != $apiKey) {
            return $this->error('Invalid API key', 401);
        }

        if ($signature !== $signature) {
            return $this->error('Invalid signature', 401);
        }
    }

    private function getHeaders($method, $endpoint, $bodyString)
    {
        $uuid = Uuid::uuid4()->toString(); //Nonce
        $token = $this->generate_signature($method, $endpoint, $bodyString);
        $region = config('services.lalamove.region');

        return [
            'Content-type' => 'application/json; charset=utf-8',
            'Authorization' => 'hmac ' . $token,
            'Accept' => 'application/json',
            'Market' => $region,
            'Request-ID' => $uuid,
        ];
    }

    private function post_request($endpoint, $data)
    {
        $bodyString = json_encode(['data' => $data]); //Converts the array into JSON String for the Header
        $headers = $this->getHeaders('POST', $endpoint, $bodyString);
        $url = config('services.lalamove.url');

        return Http::withHeaders($headers)->post($url . $endpoint, ['data' => $data]);
    }

    private function patch_request($endpoint, $origin_stop, $destination_stop)
    {
        $bodyString = json_encode([
            'data' => [
                'stops' => [$origin_stop, $destination_stop],
            ]
        ]);
        $headers = $this->getHeaders('PATCH', $endpoint, $bodyString);
        $url = config('services.lalamove.url');

        return Http::withHeaders($headers)->patch($url . $endpoint, [
            'data' => [
                'stops' => [$origin_stop, $destination_stop],
            ]
        ]);
    }

    private function get_request($endpoint)
    {
        $headers = $this->getHeaders('GET', $endpoint, '');
        $url = config('services.lalamove.url');

        return Http::withHeaders($headers)->get($url . $endpoint);
    }

    private function delete_request($endpoint)
    {
        $headers = $this->getHeaders('DELETE', $endpoint, '');
        $url = config('services.lalamove.url');

        return Http::withHeaders($headers)->delete($url . $endpoint);
    }

    private function change_request($endpoint, $data)
    {
        $bodyString = json_encode(['data' => $data]);
        $headers = $this->getHeaders('DELETE', $endpoint, $bodyString);
        $url = config('services.lalamove.url');

        return Http::withHeaders($headers)->delete($url . $endpoint, ['data' => $data]);
    }

    private function get_vehicle_type(ProductDetail $productDetail, $quantity)
    {
        $response = Cache::remember('cities', now()->addDays(1), function () {
            $response = $this->get_request('/v3/cities');
            return $response->json();
        });

        // PH CEB, PH MNL, PH PAM
        // TODO: get region based on postal code
        $index = array_search('PH MNL', array_column($response['data'], 'locode'));

        $region = $response['data'][$index];

        $services = [];

        foreach ($region['services'] as $service) {
            $services[] = [
                'name' => $service['key'],
                'length' => $service['dimensions']['length']['value'] * 100,
                'width' => $service['dimensions']['width']['value'] * 100,
                'height' => $service['dimensions']['height']['value'] * 100,
                'weight' => $service['load']['value'] * 1
            ];
        }

        usort($services, function ($a, $b) {
            return $a['weight'] <=> $b['weight'];
        });

        $weight = $productDetail->weight * $quantity;
        $length = $productDetail->length * $quantity;
        $width = $productDetail->width * $quantity;
        $height = $productDetail->height * $quantity;

        foreach ($services as $service) {
            if ($service['weight'] >= $weight && $service['length'] >= $length && $service['width'] >= $width && $service['height'] >= $height) {
                return $service['name'];
            }
        }

        return null;
    }

    public function quotation(LalamoveQuotationRequest $request)
    {
        $validated = $request->validated();
        $language = config('services.lalamove.language');

        $latitude = $validated['latitude'];
        $longitude = $validated['longitude'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $product = Product::where('sku', $validated['sku'])
            ->where('is_active', 1)
            ->where('approval_status', 'approved')
            ->first();

        if (empty($product)) {
            return $this->error('Invalid product sku', 499);
        }
        if ($product->on_demand == false) {
            return $this->error('Product is not available for on-demand delivery', 499);
        }
        if ($product->stock_count <= 0) {
            return $this->error('Product is out of stock', 499);
        }

        $warehouse = Warehouse::whereHas('products', function ($q) use ($product) {
            $q->where('products.id', $product->id);
        })
            ->whereHas('availabilities', function ($q) {
                $q->where('day_name', now()->timezone('Asia/Manila')->dayName);
                $q->where('start_time', '<=', now()->timezone('Asia/Manila')->toTimeString());
                $q->where('end_time', '>=', now()->timezone('Asia/Manila')->toTimeString());
            })
            ->join('product_warehouse', function ($q) use ($product) {
                $q->on('warehouses.id', '=', 'product_warehouse.warehouse_id')
                    ->where('product_warehouse.product_id', '=', $product->id)
                    ->where('product_warehouse.stocks', '>', 0);
            })
            ->join('locations', function ($q) {
                $q->on('locations.entity_id', '=', 'warehouses.id')
                    ->where('locations.entity_type', '=', Warehouse::class);
            })
            ->selectRaw('warehouses.id as id, locations.id as location_id, latitude, longitude, address, ST_DISTANCE_SPHERE(
            POINT(?,?),
            POINT(longitude,latitude)
            ) AS distance, product_warehouse.stocks', [$longitude, $latitude])
            ->with([
                'products' => function ($q) use ($product) {
                    $q->where('products.id', $product->id);
                },
                'products.productDetail'
            ])
            ->having('stocks', '>', 0)
            ->orderBy('distance')
            ->first();

        if (!$warehouse) {
            return $this->error('The seller is currently not available to deliver the product.', 499);
        }

        $productDetail = ProductDetail::where('product_id', $product->id)->firstOrFail();

        $service_type = $this->get_vehicle_type($productDetail, $validated['quantity']);

        if (!$service_type) {
            return $this->error('No service type found for product', 499);
        }

        $origin_latitude = $warehouse->latitude;
        $origin_longitude = $warehouse->longitude;
        $origin_address = $warehouse->address;

        $destination_latitude = $latitude;
        $destination_longitude = $longitude;
        $destination_address = $validated['address'];

        $stops = [
            [
                'coordinates' => [
                    'lat' => (string) $origin_latitude,
                    'lng' => (string) $origin_longitude,
                ],
                'address' => $origin_address,
            ],
            [
                'coordinates' => [
                    'lat' => (string) $destination_latitude,
                    'lng' => (string) $destination_longitude,
                ],
                'address' => $destination_address,
            ]
        ];


        $data = [
            'serviceType' => $service_type,
            'specialRequests' => [],
            'stops' => $stops,
            'language' => $language,
            // 'scheduleAt' => Carbon::now()->addMinutes(5)->toIso8601ZuluString('millisecond'),
            'isRouteOptimized' => false,
        ];



        DB::beginTransaction();
        try {
            $response = $this->post_request('/v3/quotations', $data);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            $quotationId = $response->json('data.quotationId');
            $scheduledAt = $response->json('data.scheduledAt');
            $price = $response->json('data.priceBreakdown.total');

            $timestamp = Carbon::now();

            $lalamove_service = LalamoveService::updateOrCreate(
                [
                    'quotation_id' => $quotationId,
                ],
                [
                    'scheduled_at' => $timestamp,
                    'expires_at' => $timestamp->copy()->addMinutes(5),
                    'price' => $price,
                    'product_id' => $product->id,
                    'quantity' => $validated['quantity'],
                    'warehouse_id' => $warehouse->id,
                    'buyer_id' => $entity->id,
                    'buyer_type' => get_class($entity),
                    'seller_stop_id' => $response->json('data.stops.0.stopId'),
                    'buyer_stop_id' => $response->json('data.stops.1.stopId'),
                ]
            );

            $lalamove_service->buyer_location()->updateOrCreate([
                'latitude' => $destination_latitude,
                'longitude' => $destination_longitude,
                'address' => $destination_address,
            ]);

            DB::commit();
            return $this->success([
                'quotationId' => $lalamove_service->quotation_id,
                'expiresAt' => $lalamove_service->expires_at,
                'price' => $lalamove_service->price,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->exception($e);
        }
    }

    public function place_order(LalamoveOrderRequest $request)
    {
        $validated = $request->validated();
        $shipping_slug = $validated['shipping_option'];
        $payment_slug = $validated['payment_option'];
        $quotation_id = $validated['quotation_id'];

        // Block payment options that are not repay until cod and cash payment is approved
        $payment_option = PaymentOption::where('slug', $payment_slug)->first();
        if (!$payment_option || $payment_option->slug !== 'repay') {
            return $this->error('Payment option is not available for on-demand deliveries', 499);
        }

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $lalamove_service = LalamoveService::where('quotation_id', '=', $quotation_id)
            ->with(['product', 'buyer', 'buyer_location'])
            ->first();

        if (empty($lalamove_service)) {
            return $this->error('Invalid quotation ID', 499);
        }
        if ($lalamove_service->expires_at < now()) {
            return $this->error('Quotation has expired', 499);
        }
        if ($entity->isNot($lalamove_service->buyer)) {
            return $this->error('Invalid buyer', 499);
        }

        $warehouse = Warehouse::where('id', $lalamove_service->warehouse_id)
            ->with([
                'products' => function ($q) use ($lalamove_service) {
                    $q->where('products.id', $lalamove_service->product_id)->first();
                }
            ])
            ->first();

        $product = $lalamove_service->product;
        $pivot_stocks = $warehouse->products->first()->pivot->stocks;

        $sender = [
            'stopId' => $lalamove_service->seller_stop_id,
            'name' => $product->merchant->name,
            'phone' => '+' . $product->merchant->phone_number,
        ];

        $recipient = [
            [
                'stopId' => $lalamove_service->buyer_stop_id,
                'name' => $entity->name,
                'phone' => '+' . $entity->phone_number,
            ]
        ];

        $data = [
            'quotationId' => $lalamove_service->quotation_id,
            'sender' => $sender,
            'recipients' => $recipient,
            'isPODEnabled' => true,
        ];

        try {
            $response = $this->post_request('/v3/orders', $data);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }

        $lalamove_order = new LalamoveOrder;
        $lalamove_order->fill([
            'lalamove_service_id' => $lalamove_service->id,
            'order_id' => $response->json('data.orderId'),
            'share_link' => $response->json('data.shareLink'),
            'status' => $response->json('data.status'),
        ]);

        $shipping_option = ShippingOption::where('slug', $shipping_slug)->firstOrFail();
        $payment_option = PaymentOption::where('slug', $payment_slug)->firstOrFail();
        $shipping_status = ShippingStatus::where('slug', 'ready_to_ship')->firstOrFail();

        $order = new ProductOrder;
        $order->fill([
            'product_id' => $product->id,
            'buyer_id' => $entity->id,
            'buyer_type' => get_class($entity),
            'amount' => $product->price,
            'quantity' => $lalamove_service->quantity,
            'shipping_fee' => $lalamove_service->price,
            'order_number' => $this->generate_order_number(
                $product,
                $shipping_option,
                $payment_option,
            ),
            'delivery_type' => 'on_demand',
            'warehouse_id' => $lalamove_service->warehouse->id,
            'tracking_number' => $response->json('data.orderId'),
            'shipping_option_id' => $shipping_option->id,
            'payment_option_id' => $payment_option->id,
            'shipping_status_id' => $shipping_status->id,
        ]);

        $buyer_location = $lalamove_service->buyer_location;
        $location = $buyer_location->replicate();

        if ($payment_slug == 'repay') {
            $provider = TransactionProvider::where('code', 'RPY')->firstOrFail();
            $channel = TransactionChannel::where('code', 'RPY')->firstOrFail();
            $type = TransactionType::where('code', 'OR')->firstOrFail();
            $status = TransactionStatus::where('slug', 'pending')->firstOrFail();

            $transaction = new Transaction;
            $transaction->fill([
                'sender_id' => $entity->id,
                'sender_type' => get_class($entity),
                'recipient_id' => $product->merchant_id,
                'recipient_type' => Merchant::class,
                'txn_no' => $this->generate_transaction_number(),
                'transaction_provider_id' => $provider->id,
                'transaction_channel_id' => $channel->id,
                'transaction_type_id' => $type->id,
                'transaction_status_id' => $status->id,
                'service_fee' => $lalamove_service->price,
                'currency' => 'PHP',
                'amount' => $lalamove_service->quantity * $product->price,
            ]);
        }


        DB::beginTransaction();
        try {
            $product->decrement('stock_count', $lalamove_service->quantity);
            $product->save();
            $warehouse->products()->updateExistingPivot($product->id, ['stocks' => $pivot_stocks - $lalamove_service->quantity]);

            $lalamove_order->save();
            $order->save();
            $location->fill([
                'entity_id' => $order->id,
                'entity_type' => get_class($order),
            ]);
            $location->save();

            if ($payment_slug == 'repay') {
                $transaction->ref_no = $order->order_number;
                $transaction->save();

                $this->credit($entity, $transaction);
            }

            DB::commit();

            $order->load(['product:id,name,price,currency', 'shipping_option', 'location']);
            $this->add_model_images($order->product, 'product_images', true);
            return $this->success($order);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }

    // public function city_info()
    // {
    //     try {
    //         $response = Cache::remember('cities', now()->addDays(1), function () {
    //             $response = $this->get_request('/v3/cities');
    //             return $response->json();
    //         });

    //         return response()->json($response, 200);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => 'Failed to get city info'], 500);
    //     }
    // }

    private function complete_order_transaction(Product $product, ProductOrder $order)
    {
        $merchant = $product->merchant()->first();
        $transaction = $order->transaction()->first();

        $payment_option = $order->payment_option()->first();

        if ($payment_option->slug !== 'repay') {
            return;
        }

        $status = TransactionStatus::where('slug', 'successful')->first();

        DB::beginTransaction();
        try {
            $transaction->transaction_status_id = $status->id;
            $transaction->save();

            $this->debit($merchant, $transaction);
            $product->increment('sold_count', $order->quantity);
            $product->save();

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    private function refund_order_transaction(ProductOrder $order)
    {
        $buyer = $order->buyer()->first();
        $transaction = $order->transaction()->first();

        $payment_option = $order->payment_option()->first();

        if ($payment_option->slug !== 'repay') {
            return;
        }

        $status = TransactionStatus::where('slug', 'failed')->first();

        DB::beginTransaction();
        try {
            $transaction->transaction_status_id = $status->id;
            $transaction->save();

            $this->refund($buyer, $transaction);
            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            throw $ex;
        }
    }

    // public function webhook(Request $request)
    // {
    //     return response()->json([], 200);
    public function webhook(LalamoveWebhookRequest $request)
    {
        $validated = $request->validated();

        $request_apiKey = $validated['apiKey'];
        $request_timestamp = $validated['timestamp'];
        $request_signature = $validated['signature'];
        $request_eventId = $validated['eventId'];
        $request_eventType = $validated['eventType'];
        $request_data = $validated['data'];

        $apiKey = config('services.lalamove.api_key');

        $apiSecret = config('services.lalamove.api_secret');
        $method = 'POST';
        $path = '/api/external/lalamove/webhook';
        $bodyString = str_replace(':null', ':""', json_encode($request_data, JSON_UNESCAPED_SLASHES));

        $rawSignature = "{$request_timestamp}\r\n{$method}\r\n{$path}\r\n\r\n{$bodyString}";
        $signature = hash_hmac('sha256', $rawSignature, $apiSecret);

        if ($request_apiKey != $apiKey) {
            Log::info('Invalid API key', ['request_apiKey' => $request_apiKey, 'apiKey' => $apiKey]);
            return $this->error('Invalid API key', 401);
        }

        if ($request_signature !== $signature) {
            Log::info('Invalid signature', ['request_signature' => $request_signature, 'signature' => $signature, 'data' => $request_data, 'bodyString' => $bodyString]);
            return $this->error('Invalid signature', 401);
        }

        if ($request_eventType == 'ORDER_STATUS_CHANGED') {
            $order_data = $request_data['order'];
            $order = LalamoveOrder::where('order_id', $order_data['orderId'])->firstOrFail();
            $product_order = ProductOrder::where('tracking_number', $order_data['orderId'])->with(['buyer', 'product.merchant'])->firstOrFail();
            $status = $order_data['status'];
            $updated_time = Carbon::parse($request_data['updatedAt'])->toDateTimeString();

            if ($order->updated_at > $updated_time) {
                return response()->json([], 200);
            }

            $order->updated_at = $updated_time;
            $order->previous_status = $order->status;
            $order->status = $status;

            $log = new ProductOrderLog;
            $log->product_order_id = $product_order->id;

            switch ($status) {
                case 'COMPLETED':
                    $product_order->load(['product']);
                    $product = $product_order->product;

                    $shipping_status = ShippingStatus::where('slug', 'completed')->first()->id;
                    $product_order->shipping_status_id = $shipping_status;
                    $product_order->processed_at = Carbon::now();

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Shipment delivered';
                    $log->description = 'Product has been delivered by the delivery partner.';

                    $this->complete_order_transaction($product, $product_order);

                    $this->alert(
                        $product_order->buyer,
                        'order',
                        $product_order->order_number,
                        "Your order has been successfully delivered by the delivery partner.\n\nOrder number: #{$product_order->order_number}"
                    );
                    break;
                case 'EXPIRED':
                    $shipping_status = ShippingStatus::where('slug', str('Failed Delivery')->slug('_'))->first()->id;
                    $product_order->shipping_status_id = $shipping_status;
                    $product_order->processed_at = Carbon::now();
                    $product_order->termination_reason = 'No drivers accepted the order';

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Shipment expired';
                    $log->description = 'The order expired as no drivers accepted the order.';

                    $this->alert(
                        $product_order->buyer,
                        'order',
                        $product_order->order_number,
                        "Your order has been tagged as expired due to no drivers being available at the moment. Please try again after some time.\n\nOrder number: #{$product_order->order_number}"
                    );

                    $this->refund_order_transaction($product_order);
                    break;
                case 'CANCELED':
                    $shipping_status = ShippingStatus::where('slug', str('Cancellation')->slug('_'))->first()->id;
                    $product_order->shipping_status_id = $shipping_status;
                    $product_order->processed_at = Carbon::now();
                    $product_order->termination_reason = 'Seller cancelled the order';
                    $product_order->cancelled_by = 'seller';

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Shipment canceled';
                    $log->description = 'The order has been canceled by the seller.';

                    $this->refund_order_transaction($product_order);

                    $this->alert(
                        $product_order->buyer,
                        'order',
                        $product_order->order_number,
                        "The seller has cancelled your order #{$product_order->order_number}."
                    );
                    break;
                case 'REJECTED':
                    $shipping_status = ShippingStatus::where('slug', str('Failed Delivery')->slug('_'))->first()->id;
                    $product_order->shipping_status_id = $shipping_status;
                    $product_order->processed_at = Carbon::now();
                    $product_order->termination_reason = 'Order rejected twice';

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Shipment rejected';
                    $log->description = 'The order was matched and rejected twice by two drivers in a row.';

                    $this->refund_order_transaction($product_order);

                    $this->alert(
                        $product_order->buyer,
                        'order',
                        $product_order->order_number,
                        "Your order has been tagged as expired due to the order being rejected twice. Please try again after some time.\n\nOrder number: #{$product_order->order_number}"
                    );
                    break;
                case 'ON_GOING':
                    $log->shipping_status_id = $product_order->shipping_status_id;
                    $log->title = 'Driver accepted the order';
                    $log->description = 'The driver accepted the order and is on the way to the seller.';

                    $this->alert(
                        $product_order->product->merchant,
                        'order',
                        $product_order->order_number,
                        "A driver is on the way to pick up the product with SKU #{$product_order->product->sku}.\n\nOrder number: #{$product_order->order_number}",
                    );
                    break;
                case 'PICKED_UP':
                    $shipping_status = ShippingStatus::where('slug', 'shipping')->first()->id;
                    $product_order->shipping_status_id = $shipping_status;

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Product picked up';
                    $log->description = 'The product has been picked up by the delivery partner and is on the way to the buyer.';

                    $this->alert(
                        $product_order->buyer,
                        'order',
                        $product_order->order_number,
                        "Our delivery partner has picked up your product and is on the way to your location.\n\nOrder number: #{$product_order->order_number}"                    );
                    break;
                case 'ASSIGNING_DRIVER':
                    $shipping_status = ShippingStatus::where('slug', str('Ready to Ship')->slug('_'))->first()->id;
                    $product_order->shipping_status_id = $shipping_status;

                    $order->lalamove_driver_id = null;

                    $log->shipping_status_id = $shipping_status;
                    $log->title = 'Finding driver';
                    $log->description = 'Finding a driver for the order.';

                    if ($order->previous_status == 'ON_GOING') {
                        $this->alert(
                            $product_order->product->merchant,
                            'order',
                            $product_order->order_number,
                            "The previous driver has rejected the delivery. We are searching for another driver to pick up the product.\n\nOrder number: #{$product_order->order_number}",
                        );
                    }
                    break;
                default:
                    break;
            }

            DB::beginTransaction();
            try {
                $order->save();
                $product_order->save();
                $log->save();

                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                $this->exception($ex);
            }

            Log::info($status);
            Log::info($order);
        } elseif ($request_eventType == 'DRIVER_ASSIGNED') {
            $order_data = $request_data['order'];
            $order = LalamoveOrder::where('order_id', $order_data['orderId'])->firstOrFail();

            $driver_data = $request_data['driver'];
            $driver = LalamoveDriver::updateOrCreate([
                'driver_id' => $driver_data['driverId'],
            ], [
                'name' => $driver_data['name'],
                'phone_number' => $driver_data['phone'],
                'plate_number' => $driver_data['plateNumber'],
                'photo' => $driver_data['photo'],
            ]);

            $order->lalamove_driver_id = $driver->id;

            DB::beginTransaction();
            try {
                $order->save();
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                $this->exception($ex);
            }
            Log::info($request_eventType);
        } elseif ($request_eventType == 'ORDER_AMOUNT_CHANGED') {
            $order_data = $request_data['order'];
            $order = LalamoveOrder::where('order_id', $order_data['orderId'])->firstOrFail();
            $product_order = ProductOrder::where('tracking_number', $order_data['orderId'])->firstOrFail();

            $lalamove_service = $order->service;
            $order_price = $order_data['price'];

            $lalamove_service->price = $order_price['totalPrice'];
            $lalamove_service->save();
            Log::info($request_eventType);
        } elseif ($request_eventType == 'WALLET_BALANCE_CHANGED') {
            $balance_data = $request_data['balance'];
            
            DB::beginTransaction();
            try {
                LalamoveBalance::create([
                    'currency' => $balance_data['currency'],
                    'amount' => $balance_data['amount'],
                ]);
                DB::commit();
            } catch (\Exception $ex) {
                DB::rollBack();
                Log::error($ex);
            }

            return response()->json([], 200);
        }

        return response()->json([], 200);
    }
}
