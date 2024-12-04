<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $timestamps = false;

    protected $fillable = ['action'];

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'employee_role_permissions', 'permission_id', 'module_id');
    }

    public function employeeRoles()
    {
        return $this->belongsToMany(EmployeeRole::class, 'employee_role_permissions')
            ->withPivot('module_id');
    }
}
