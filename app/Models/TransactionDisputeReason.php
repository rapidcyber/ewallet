<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDisputeReason extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function disputes()
    {
        return $this->hasMany(TransactionDispute::class);
    }
}
