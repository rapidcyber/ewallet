<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $hidden = [
        'id',
        'created_at',
        'updated_at',
        'pivot'
    ];

    protected $fillable = ['name', 'slug'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    public function merchants()
    {
        return $this->belongsToMany(Merchant::class, 'merchant_role');
    }

    public function transaction_limits()
    {
        return $this->hasMany(TransactionLimit::class, 'role_id');
    }

    public function balance_limit()
    {
        return $this->hasOne(BalanceLimit::class, 'role_id');
    }
}
