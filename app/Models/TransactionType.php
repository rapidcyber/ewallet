<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionType extends Model
{
    use HasFactory;

    protected $hidden = ['id', 'slug'];

    protected $fillable = ['name', 'slug', 'code'];

    public $timestamps = false;

    public function transaction_limits(): HasMany
    {
        return $this->hasMany(TransactionLimit::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
