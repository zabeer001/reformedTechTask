<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id',
        'employee_id',
        'date_of_birth',
        'phone',
        'job_title',
        'department',
        'hire_date',
        'salary',
        'status',
    ];
    // Optional: Relationship to the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
