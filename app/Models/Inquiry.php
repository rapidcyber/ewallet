<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $hidden = ['id', 'created_at', 'updated_at'];

    protected $fillable = ['full_name', 'email', 'subject', 'message'];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->withTrashed()->where($this->getRouteKeyName(), $value)->firstOrFail();
    }
}
