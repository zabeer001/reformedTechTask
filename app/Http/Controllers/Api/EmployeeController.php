<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User; // <-- Need to import the User Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // <-- Need to import DB for transactions
use Illuminate\Support\Facades\Hash; // <-- Need to import Hash for password
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource (READ - Index).
     */


    public function index(Request $request)
    {
        $query = Employee::with('user');

        $search = $request->get('search');

        if ($search) {
            $query->where(function ($q) use ($search) {

                // Search employee fields
                $q->where('employee_id', 'like', "%{$search}%")

                    // Search related user email
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%");
                    });
            });
        }

        $employees = $query->latest()->paginate(15);

        return response()->json([
            'data' => $employees
        ]);
    }

    /**
     * Store a newly created resource in storage (CREATE - Store).
     */
    public function store(Request $request)
    {
        // 1. Unified Validation
        $validated = $request->validate([
            // User and Employee Shared Fields
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'full_name' => ['required', 'string', 'max:200'],

            // Required for User Creation
            'password' => ['required', 'string', 'min:8'], // Password for the new user account

            // Employee-Specific Fields
            'employee_id' => ['nullable', 'string', 'max:20', 'unique:employees,employee_id'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['Active', 'On Leave', 'Terminated'])],
        ]);
        // return 0;

        // Use a database transaction to ensure both User and Employee are created successfully
        try {
            DB::beginTransaction();

            // 2. Create the User Account
            $user = User::create([
                'name' => $validated['full_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),

            ]);

            // 3. Create the Employee Record, linked to the new User
            $employee = Employee::create([
                'user_id' => $user->id,
                'employee_id' => $validated['employee_id'] ?? $this->generateEmployeeId(),
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'job_title' => $validated['job_title'],
                'department' => $validated['department'],
                'hire_date' => $validated['hire_date'],
                'salary' => $validated['salary'],
                'status' => $validated['status'],
            ]);

            DB::commit();

            return response()->json([
                'message' => 'User and Employee created successfully.',
                'employee' => $employee,
                'user' => $user,
            ], 201); // 201 Created

        } catch (\Exception $e) {
            DB::rollBack();

            // Log the error for debugging
            Log::error('Employee creation failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to create employee profile and user account.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource (READ - Show).
     */
    public function show(Employee $employee)
    {
        return response()->json([
            'employee' => $employee
        ]);
    }

    /**
     * Update the specified resource in storage (UPDATE - Update).
     */
    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            // Ignore current employee's ID/email when checking uniqueness
            'employee_id' => ['required', 'string', 'max:20', Rule::unique('employees')->ignore($employee)],
            'full_name' => ['required', 'string', 'max:200'],
            'email' => ['required', 'email', 'max:100', Rule::unique('employees')->ignore($employee)],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'job_title' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['Active', 'On Leave', 'Terminated'])],
        ]);

        $employee->update($validated);

        return response()->json([
            'message' => 'Employee updated successfully.',
            'employee' => $employee
        ]);
    }

    /**
     * Remove the specified resource from storage (DELETE - Destroy).
     */
    public function destroy(Employee $employee)
    {
        // Use a database transaction to ensure atomicity
        try {
            DB::beginTransaction();

            // 1. Get the linked User ID
            $userId = $employee->user_id;

            // 2. Delete the Employee profile
            // This is done BEFORE the user to maintain the 'user_id' reference temporarily.
            // Even though you have onDelete('set null'), we will delete the user explicitly.
            $employee->delete();

            // 3. Delete the linked User account (if one exists)
            if ($userId) {
                // Find the user model instance and delete it
                $user = \App\Models\User::find($userId);

                if ($user) {
                    $user->delete();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Employee and linked User deleted successfully.'
            ], 204); // 204 No Content

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Employee/User deletion failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to delete employee and linked user account.',
                'error' => $e->getMessage()
            ], 500); // 500 Internal Server Error
        }
    }

    private function generateEmployeeId(): string
    {
        return 'EMP-' . now()->format('ymd-Hi');
    }
}
