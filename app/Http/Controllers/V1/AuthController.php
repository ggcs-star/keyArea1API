<?php

namespace App\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use App\Http\Controllers\Controller;
class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        try {
            $request->validate([
                'fullName' => 'required|string|max:255',
                'email'    => 'required|email|unique:admin_users,email',
                'password' => 'required|string|min:6',
            ]);

            $user = new User();
            $user->name = $request->fullName;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            $plainTextToken = bin2hex(random_bytes(40));
            $hashedToken = hash('sha256', $plainTextToken);
            
            $now = new UTCDateTime(time() * 1000);
            DB::connection('mongodb')
                ->getCollection('personal_access_tokens')
                ->insertOne([
                    'tokenable_id' => (string) $user->_id,
                    'tokenable_type' => 'App\Models\User',
                    'name' => 'auth-token',
                    'token' => $hashedToken,
                    'abilities' => ['*'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data'    => [
                    'user' => [
                        'id'    => (string) $user->_id,
                        'name'  => $user->name,
                        'email' => $user->email,
                    ],
                    'token' => $plainTextToken
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $plainTextToken = bin2hex(random_bytes(40));
            $hashedToken = hash('sha256', $plainTextToken);
            
            $now = new UTCDateTime(time() * 1000);
            DB::connection('mongodb')
                ->getCollection('personal_access_tokens')
                ->insertOne([
                    'tokenable_id' => (string) $user->_id,
                    'tokenable_type' => 'App\Models\User',
                    'name' => 'auth-token',
                    'token' => $hashedToken,
                    'abilities' => ['*'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data'    => [
                    'user' => [
                        'id'    => (string) $user->_id,
                        'name'  => $user->name,
                        'email' => $user->email,
                        'role'  => $user->role ?? 'user',
                    ],
                    'token' => $plainTextToken
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed: ' . $e->getMessage()
            ], 500);
        }
    }

   
    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if ($token) {
                $hashedToken = hash('sha256', $token);
                DB::connection('mongodb')
                    ->getCollection('personal_access_tokens')
                    ->deleteOne(['token' => $hashedToken]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    
    public function user(Request $request)
    {
        try {
            $token = $request->bearerToken();
            
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token required'
                ], 401);
            }
            
            $hashedToken = hash('sha256', $token);
            
            // Find token in database
            $tokenRecord = DB::connection('mongodb')
                ->getCollection('personal_access_tokens')
                ->findOne(['token' => $hashedToken]);
                
            if (!$tokenRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token'
                ], 401);
            }
            
            // Get user
            $user = User::where('_id', $tokenRecord->tokenable_id)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => (string) $user->_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role ?? 'user',
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}