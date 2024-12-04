<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeRole extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'employee_role_permissions', 'employee_role_id', 'permission_id')
            ->withPivot('module_id');
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'employee_role_permissions', 'employee_role_id', 'module_id')
            ->withPivot('permission_id');
    }

    public function hasPermission($moduleId, $action): bool
    {
        return $this->permissions()
            ->where('module_id', $moduleId)
            ->where('permissions.action', $action)
            ->exists();
    }

    public function hasModule($permissionId, $name)
    {
        return $this->modules()
            ->where('permission_id', $permissionId)
            ->where('modules.name', $name)
            ->exists();
    }
}
