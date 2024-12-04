<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug'
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'employee_role_permissions', 'module_id', 'permission_id');
    }

    public function employeeRoles()
    {
        return $this->belongsToMany(EmployeeRole::class, 'employee_role_permissions')
            ->withPivot('permission_id');
    }
}
