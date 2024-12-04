<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'scope',
        'amount',
        'role_id',
        'transaction_type_id',
    ];

    public $timestamps = false;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function transaction_type()
    {
        return $this->belongsTo(TransactionType::class);
    }
}
