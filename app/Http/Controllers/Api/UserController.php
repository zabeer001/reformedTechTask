<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource (READ all).
     */
    public function index(Request $request)
    {
        // return $request;
        $query = User::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage (CREATE).
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            // Role should be validated against a list of allowed roles
            'role' => 'required|string|in:admin,user,editor', 
            'password' => 'required|string|min:8|confirmed', // 'confirmed' checks for 'password_confirmation' field
        ]);

        // The 'password' attribute is automatically hashed by the model's cast
        $user = User::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => $user->only(['id', 'name', 'email', 'role'])
        ], 201); // 201 Created
    }

    /**
     * Display the specified resource (READ one).
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => $user->only(['id', 'name', 'email', 'role', 'created_at'])
        ]);
    }

    /**
     * Update the specified resource in storage (UPDATE).
     */
    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            // Ensure email is unique, but ignore the current user's email
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'sometimes|required|string|in:admin,user,editor',
            // Password update is optional
            'password' => 'nullable|string|min:8|confirmed', 
        ]);
        
        // Remove password if it was not provided or is null/empty
        if (empty($validatedData['password'])) {
            unset($validatedData['password']);
        }
        
        $user->update($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => $user->only(['id', 'name', 'email', 'role'])
        ]);
    }

    /**
     * Remove the specified resource from storage (DELETE).
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.'
        ], 204); // 204 No Content
    }
}