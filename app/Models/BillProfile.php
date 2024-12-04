<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'biller_code',
        'biller_name',
        'amount',
        'receipt_email',
        'infos',
        'remind_date',
        'type',
    ];
}
