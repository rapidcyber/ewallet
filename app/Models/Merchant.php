<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Merchant extends Authenticatable implements HasMedia
{
    use HasApiTokens, Notifiable, HasFactory, HasRelationships, InteractsWithMedia, SoftDeletes;

    protected $hidden = [
        'app_id',
        'user_id',
        'merchant_category_id',
        'invoice_prefix',
        'apply_for_realholmes',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $fillable = [
        'app_id',
        'account_number',
        'user_id',
        'merchant_category_id',
        'name',
        'email',
        'website',
        'phone_iso',
        'phone_number',
        'invoice_prefix',
        'apply_for_realholmes',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function details(): HasOne
    {
        return $this->hasOne(MerchantDetail::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MerchantCategory::class, 'merchant_category_id');
    }

    public function incoming_transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'recipient');
    }

    public function outgoing_transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'sender');
    }

    public function incoming_invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'recipient');
    }

    public function outgoing_invoices(): MorphMany
    {
        return $this->morphMany(Invoice::class, 'sender');
    }

    public function balances(): MorphMany
    {
        return $this->morphMany(Balance::class, 'entity');
    }

    public function latest_balance(): MorphOne
    {
        return $this->morphOne(Balance::class, 'entity')->orderBy('id', 'desc');
    }

    public function bills(): MorphMany
    {
        return $this->morphMany(Bill::class, 'entity');
    }

    public function owned_services()
    {
        return $this->hasMany(Service::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function bookings_through_services()
    {
        return $this->hasManyThrough(Booking::class, Service::class, 'merchant_id', 'service_id');
    }

    public function warehouses(): HasMany
    {
        return $this->hasMany(Warehouse::class);
    }

    public function owned_products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders_through_products()
    {
        return $this->hasManyThrough(ProductOrder::class, Product::class, 'merchant_id', 'product_id', 'id', 'id');
    }

    public function return_orders_through_products(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->orders_through_products(), (new ProductOrder)->return_orders());
    }

    public function return_orders()
    {
        return $this->hasManyThrough(ReturnOrder::class, ProductOrder::class, 'buyer_id', 'product_order_id', 'id', 'id')
            ->where('buyer_type', $this->getMorphClass());
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'recipient');
    }

    public function invoices_from_service_bookings()
    {
        return $this->hasManyDeepFromRelations($this->bookings_through_services(), (new Booking)->invoice());
    }

    public function transactions_from_service_bookings()
    {
        return $this->hasManyDeepFromRelations($this->bookings_through_services(), (new Booking)->transactions());
    }

    public function received_reviews(): MorphMany
    {
        return $this->morphMany(EntityReview::class, 'entity');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(EntityReview::class, 'reviewer');
    }

    public function shipping_options()
    {
        return $this->belongsToMany(ShippingOption::class, 'merchant_shipping_option', 'merchant_id', 'shipping_option_id');
    }

    public function transaction_disputes()
    {
        return $this->hasManyThrough(TransactionDispute::class, Transaction::class, 'sender_id', 'transaction_id', 'id', 'id')
            ->where('sender_type', Merchant::class);
    }

    public function logo()
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', 'merchant_logo');
    }

    public function product_orders(): MorphMany
    {
        return $this->morphMany(ProductOrder::class, name: 'buyer');
    }

    public function service_bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'entity');
    }

    public function linked_ub_accounts(): MorphMany
    {
        return $this->morphMany(UnionbankLinkedAccount::class, 'owner');
    }

    public function location(): MorphMany
    {
        return $this->morphMany(Location::class, 'entity');
    }

    public function profile_picture()
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', 'merchant_photo');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'merchant_role');
    }

    public function generated_qrs(): MorphMany
    {
        return $this->morphMany(QrGeneratedData::class, 'client');
    }

    public function billing_requests()
    {
        return $this->hasMany(BillingRequest::class);
    }

    public function transaction_requests()
    {
        return $this->hasMany(TransactionRequest::class);
    }
}
