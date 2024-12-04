<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class User extends Authenticatable implements HasMedia, FilamentUser
{
    use HasApiTokens, HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'phone_iso',
        'phone_number',
        'pin',
        'apply_for_realholmes',
        'app_id',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'pin',
        'phone_verified_at',
        'created_at',
        'updated_at',
        'app_id',
        'apply_for_realholmes',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
            'pin' => 'hashed',
        ];
    }

    protected $appends = ['name', 'status'];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            do {
                $uuid = str()->uuid();
            } while (self::where('app_id', $uuid)->exists());

            $model->app_id = $uuid;
        });
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->fit(Fit::Max, 250, 250)
            ->optimize()
            ->nonQueued();
    }

    public function canAccessFilament()
    {
        return $this->id === 1;
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->canAccessFilament();
    }

    public function profile_picture()
    {
        return $this->morphOne(Media::class, 'model')->where('collection_name', 'profile_picture');
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNotNull('email_verified_at');
        $query->whereNotNull('phone_verified_at');
        $query->whereHas('profile', function ($profile) {
            $profile->whereNot(function ($query) {
                $query->where('status', 'rejected')->orWhere('status', 'deactivated');
            });
        });
    }

    public function getStatusAttribute()
    {
        return $this->profile->status;
    }

    public function getNameAttribute()
    {
        return $this->profile->first_name . ' ' . $this->profile->surname;
    }

    public function auth_attempt(): HasOne
    {
        return $this->hasOne(AuthAttempt::class);
    }

    // public function findForPassport(string $phonenumber): User
    // {
    //     return $this->where('phone_number', $phonenumber)->first();
    // }

    public function merchants(): HasMany
    {
        return $this->hasMany(Merchant::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function employer()
    {
        return $this->belongsToMany(Merchant::class);
    }

    public function employee()
    {
        return $this->hasMany(Employee::class, 'user_id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function incoming_transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'recipient');
    }

    public function outgoing_transactions(): MorphMany
    {
        return $this->morphMany(Transaction::class, 'sender');
    }

    public function owned_merchants(): HasMany
    {
        return $this->hasMany(Merchant::class);
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

    public function location(): MorphMany
    {
        return $this->morphMany(Location::class, 'entity');
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'recipient');
    }

    public function hasRole($roleName)
    {
        return $this->roles()->where('slug', $roleName)->exists();
    }

    public function transaction_disputes()
    {
        return $this->hasManyThrough(TransactionDispute::class, Transaction::class, 'sender_id', 'transaction_id', 'id', 'id')
            ->where('sender_type', User::class);
    }

    public function product_orders(): MorphMany
    {
        return $this->morphMany(ProductOrder::class, name: 'buyer');
    }

    public function return_orders()
    {
        return $this->hasManyThrough(ReturnOrder::class, ProductOrder::class, 'buyer_id', 'product_order_id', 'id', 'id')
            ->where('buyer_type', $this->getMorphClass());
    }

    public function service_bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'entity');
    }

    public function linked_ub_accounts(): MorphMany
    {
        return $this->morphMany(UnionbankLinkedAccount::class, 'owner');
    }

    public function generated_qrs(): MorphMany
    {
        return $this->morphMany(QrGeneratedData::class, 'client');
    }

    public function kyc(): HasOne
    {
        return $this->hasOne(UserKyc::class);
    }

    public function linked_rh_account(): MorphOne
    {
        return $this->morphOne(LinkedRealholmesAccount::class, 'entity');
    }

    public function reviews()
    {
        return $this->morphMany(EntityReview::class, 'reviewer');
    }

    public function profile_update_request()
    {
        return $this->hasOne(ProfileUpdateRequest::class);
    }
}
