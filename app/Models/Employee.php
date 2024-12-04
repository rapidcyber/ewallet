<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Employee extends Model
{
    use HasFactory, HasRelationships;

    protected $fillable = [
        'merchant_id',
        'user_id',
        'employee_role_id',
        'occupation',
        'salary',
        'salary_type_id',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(EmployeeRole::class, 'employee_role_id');
    }

    public function salary_type(): BelongsTo
    {
        return $this->belongsTo(SalaryType::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(EmployeeDeduction::class);
    }

    public function access_level()
    {
        return $this->hasOne(EmployeeRole::class, 'id', 'employee_role_id');
    }

    public function recent_payslip()
    {
        return $this->user->incoming_transactions();
    }

    public function employer()
    {
        return $this->hasOne(Merchant::class, 'id', 'merchant_id');
    }
}
