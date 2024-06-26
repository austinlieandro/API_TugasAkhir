<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Bengkels;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            'email' => "required|email",
            'username' => "required",
            'password' => "required",
            'phone' => "required",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $existingUser = Users::where('email', $request->email)->orWhere('username', $request->username)->first();

        if ($existingUser) {
            $message = '';
            if ($existingUser->email === $request->email) {
                $message = 'Email sudah digunakan.';
            }
            if ($existingUser->username === $request->username) {
                $message = 'Username sudah digunakan.';
            }
            if ($existingUser->username === $request->username && $existingUser->email === $request->email) {
                $message = 'Email & Username sudah digunakan.';
            }
            return response()->json([
                'status' => false,
                'message' => $message,
            ], 400);
        }

        $user = Users::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'user_bengkel' => 'pelanggan',
        ]);

        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'Register Berhasil',
                'user' => $user,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Register kamu gagal'
        ], 409);
    }

    public function login(Request $request)
    {
        $user = Users::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        $bengkel = Bengkels::where('users_id', $user->id)->first();
        $bengkel_id = $bengkel ? $bengkel->id : 0;

        return response()->json([
            'success' => true,
            'message' => 'Login Berhasil',
            'user' => $user,
            'bengkels_id' => $bengkel_id,
        ], 200);
    }

    public function updateProfile(Request $request, $users_id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'username' => 'required',
            'phone' => 'required',
            'password' => 'nullable|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Users::find($users_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $existingUser = Users::where('id', '!=', $users_id)
            ->where(function ($query) use ($request) {
                $query->where('email', $request->email)
                    ->orWhere('username', $request->username);
            })
            ->first();

        if ($existingUser) {
            $message = '';
            if ($existingUser->email === $request->email) {
                $message = 'Email sudah digunakan.';
            }
            if ($existingUser->username === $request->username) {
                $message = 'Username sudah digunakan.';
            }
            if ($existingUser->username === $request->username && $existingUser->email === $request->email) {
                $message = 'Email & Username sudah digunakan.';
            }
            return response()->json([
                'status' => false,
                'message' => $message,
            ], 400);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->phone = $request->phone;

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password); // Enkripsi password baru
        }

        if ($user->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to update profile'
        ], 500);
    }


    public function profile($id)
    {
        $users = Users::find($id);

        if ($users) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan profile',
                'users' => $users,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menampilkan profile',
        ], 404);
    }
}
