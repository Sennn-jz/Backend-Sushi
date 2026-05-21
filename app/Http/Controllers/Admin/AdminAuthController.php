<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        // 2. Cek kredensial
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status'  => false,
                'message' => 'Email atau password salah',
            ], 401);
        }

        // 3. Ambil user yang login
        $user = Auth::user();

        // 4. Cek apakah role-nya admin — INI YANG BEDA DARI USER LOGIN
        if (!$user->isAdmin()) {
            // Logout dulu biar session-nya bersih
            Auth::logout();

            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak. Anda bukan admin.',
            ], 403);
        }

        // 5. Hapus token lama
        $user->tokens()->delete();

        // 6. Generate token dengan ability 'admin' — BEDA DARI USER LOGIN
        $token = $user->createToken('admin_token', ['admin'])->plainTextToken;

        // 7. Return response sukses
        return response()->json([
            'status'  => true,
            'message' => 'Login admin berhasil',
            'token'   => $token,
            'admin'   => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        // Hapus token yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil',
        ], 200);
    }
}
