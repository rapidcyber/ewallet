<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRolePermission extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'employee_role_id',
        'module_id',
        'permission_id',
    ];

    public function employee_role()
    {
        return $this->belongsTo(EmployeeRole::class, 'employee_role_id');
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
