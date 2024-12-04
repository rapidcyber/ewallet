<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\InboundListRequest;
use App\Http\Requests\Product\OrderDetailsRequest;
use App\Http\Requests\Product\OutboundListRequest;
use App\Http\Requests\Product\PlaceOrderRequest;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\PaymentOption;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\ShippingOption;
use App\Models\ShippingStatus;
use App\Traits\WithEntity;
use App\Traits\WithHttpResponses;
use App\Traits\WithImage;
use App\Traits\WithNumberGeneration;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    use WithHttpResponses, WithEntity, WithNumberGeneration, WithImage;

    public function shipping_options()
    {
        $options = ShippingOption::all();
        return $this->success($options);
    }

    /**
     * Summary of place_order
     * @param \App\Http\Requests\Product\PlaceOrderRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function place_order(PlaceOrderRequest $request)
    {
        $validated = $request->validated();
        $sku = $validated['sku'];
        $shipping_slug = $validated['shipping_option'];
        $payment_slug = $validated['payment_option'];
        $delivery_location = $validated['location'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $product = Product::where('sku', $sku)->first();
        if (empty($product) or $this->is_mine($product, $entity)) {
            return $this->error('Invalid product SKU', 499);
        }

        $shipping_option = ShippingOption::where('slug', $shipping_slug)->first();
        $payment_option = PaymentOption::where('slug', $payment_slug)->first();
        $shipping_status = ShippingStatus::where('slug', 'pending')->first();

        $order = new ProductOrder;
        $order->fill([
            'product_id' => $product->id,
            'buyer_id' => $entity->id,
            'buyer_type' => get_class($entity),
            'amount' => $product->price,
            'quantity' => $validated['quantity'],
            'shipping_fee' => $shipping_option->price,
            'order_number' => $this->generate_order_number(
                $product,
                $shipping_option,
                $payment_option,
            ),
            'tracking_number' => '', /// Should we generate?
            'shipping_option_id' => $shipping_option->id,
            'payment_option_id' => $payment_option->id,
            'shipping_status_id' => $shipping_status->id,
        ]);

        $location = new Location;
        $location->fill($delivery_location);

        DB::beginTransaction();
        try {
            $order->save();
            $location->fill([
                'entity_id' => $order->id,
                'entity_type' => get_class($order),
            ]);
            $location->save();
            DB::commit();

            $order->load(['product:id,name,price,currency', 'shipping_option', 'location']);
            $this->add_model_images($order->product, 'product_images', true);
            return $this->success($order);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }


    /**
     * Summary of details
     * 
     * @param \App\Http\Requests\Product\OrderDetailsRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function details(OrderDetailsRequest $request)
    {
        $validated = $request->validated();
        $order_number = $validated['order_number'];

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $class = get_class($entity);
        $order = ProductOrder::where("order_number", $order_number)
            ->where(function ($q) use ($entity, $class) {
                $q->whereHas('buyer', function ($q) use ($entity, $class) {
                    $q->where([
                        'buyer_id' => $entity->id,
                        'buyer_type' => $class,
                    ]);
                });

                if ($class == Merchant::class) {
                    $q->orWhereHas('product.merchant', function ($q) use ($entity) {
                        $q->where('account_number', $entity->account_number);
                    });
                }
            })->first();

        if (empty($order)) {
            return $this->error('Invalid order number', 499);
        }

        if ($class == Merchant::class && $order->product->merchant->account_number == $entity->account_number) {
            $order->load(['buyer']);
        }

        $order->load(['product:id,name,price,currency,sku', 'location', 'shipping_status', 'shipping_option']);
        $this->add_model_images($order->product, 'product_images');

        $order = $order->toArray();
        unset($order['buyer']['profile']);
        return $this->success($order);
    }

    /**
     * Outbound orders listing
     *  - List orders by clients
     * 
     * @param \App\Http\Requests\Product\OutboundListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function outbound(OutboundListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $entity = $this->get(auth()->user(), $validated['merc_ac'] ?? null);
        if (empty($entity)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $orders = $entity->product_orders()
            ->select('id', 'product_id', 'quantity', 'amount', 'order_number')
            ->with([
                'product:id,name,currency',
                'location',
            ])
            ->paginate(
                $per_page,
                ['*'],
                'orders',
                $page
            );

        foreach ($orders->items() as $order) {
            $this->add_model_images($order->product, 'product_images', true);
        }

        return $this->success([
            'orders' => $orders->items(),
            'last_page' => $orders->lastPage(),
            'total_item' => $orders->total(),
        ]);
    }

    /**
     * Summary of inbound
     * @param \App\Http\Requests\Product\InboundListRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function inbound(InboundListRequest $request)
    {
        $validated = $request->validated();
        $per_page = $validated['per_page'] ?? 10;
        $page = $validated['page'] ?? 1;

        $merchant = $this->get(auth()->user(), $validated['merc_ac']);
        if (empty($merchant)) {
            return $this->error(config('constants.messages.invalid_merc_ac'), 499);
        }

        $orders = $merchant->orders_through_products()
            ->with(['product:id,name,price,currency', 'location'])
            ->paginate(
                $per_page,
                ['*'],
                'orders',
                $page
            );

        foreach ($orders->items() as $order) {
            $this->add_model_images($order->product, 'product_images', true);
        }

        return $this->success([
            'orders' => $orders->items(),
            'last_page' => $orders->lastPage(),
            'total_item' => $orders->total(),
        ]);
    }
}
