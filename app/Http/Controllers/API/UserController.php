<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Register a new user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'birthdate' => ['required', 'date'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'birthdate' => $validatedData['birthdate'],
            'password' => $validatedData['password'],
        ]);

        $user->assignRole('user');

        $token = $user->createToken('auth_token')->accessToken;

        return response()->json([
            'data' => $user,
            'token' => $token,
        ], 201);
    }

    /**
     * Get user profile. If no ID is provided, returns the authenticated user's profile.
     * If ID is provided, it checks permissions and returns the requested user.
     *
     * @param  Request $request
     * @param  int|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id = null)
    {
        // If no ID provided, return authenticated user
        if ($id === null) {
            return new UserResource($request->user());
        }
        
        // If ID is provided, check if user can view the requested profile
        $requestedUser = User::findOrFail($id);
        
        // Only allow if it's the same user or has admin role
        if ($request->user()->id == $id || $request->user()->hasRole('admin')) {
            return new UserResource($requestedUser);
        }
        
        return response()->json([
            'message' => 'You do not have permission to view this profile',
        ], 403);
    }

    /**
     * Update user profile. If no ID is provided, updates the authenticated user.
     * If ID is provided, it checks permissions and updates the requested user.
     *
     * @param  Request $request
     * @param  int|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id = null)
    {
        // If no ID provided, update authenticated user
        if ($id === null) {
            $user = $request->user();
        } else {
            // If ID is provided, check permissions
            $user = User::findOrFail($id);
            
            if ($request->user()->id != $id && !$request->user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'You do not have permission to update this profile',
                ], 403);
            }
        }
        
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'birthdate' => ['sometimes', 'date'],
            'password' => ['sometimes', 'confirmed', 'min:8'],
        ]);
        
        // If password is provided, it will be automatically hashed by the model
        $user->update($validated);
        
        return new UserResource($user);
    }
}