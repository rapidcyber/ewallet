<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemIssueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'module',
        'description',
        'resolution',
    ];
}
