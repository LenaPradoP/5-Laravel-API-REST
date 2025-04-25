<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * Display a listing of users or the authenticated user profile.
     * Admin sees all users, regular users see only their profile.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\App\Http\Resources\UserResource
     */
    public function index(Request $request)
    {
        if ($request->user()->hasRole('admin')) {
            return UserResource::collection(User::all());
        }
        
        return UserResource::collection(User::where('id', $request->user()->id)->get());
    }

    /**
     * Display the specified user.
     *
     * @param  Request $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\UserResource
     */
    public function show(Request $request, $id)
    {
        $requestedUser = User::findOrFail($id);
        
        // Solo permitir si es el mismo usuario o tiene rol de administrador
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

    /**
     * Delete user account.
     *
     * @param  Request $request
     * @param  int|null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id = null)
    {
        // If no ID provided, delete authenticated user
        if ($id === null) {
            $user = $request->user();
        } else {
            // If ID is provided, check permissions
            $user = User::findOrFail($id);
            
            if ($request->user()->id != $id && !$request->user()->hasRole('admin')) {
                return response()->json([
                    'message' => 'You do not have permission to delete this account',
                ], 403);
            }
        }
        
        // Revoke all tokens
        $user->tokens()->delete();
        
        // Delete the user
        $user->delete();
        
        return response()->json(null, 204);
    }
}