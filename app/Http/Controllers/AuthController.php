<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Di-import untuk kebutuhan mengambil data notifikasi & pesanan

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Register berhasil',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Email atau password salah'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login berhasil',
            'token' => 'Bearer ' . $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    // ==========================================================
    // NOTIFICATION LOGIC
    // ==========================================================
    // GET /api/notifications
    // Mengambil semua notifikasi milik user yang sedang aktif dari home screen
    public function getNotifications(Request $request)
    {
        // 1. Ambil data user yang sedang login lewat token sanctum
        $user = $request->user();

        // 2. Ambil data notifikasi milik user tersebut dari database, urutkan dari yang terbaru
        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Kembalikan response sukses berupa JSON ke Frontend/Postman
        return response()->json([
            'status'  => true,
            'message' => 'Berhasil mengambil data notifikasi',
            'data'    => $notifications
        ], 200);
    }

    // ==========================================================
    // USER PROFILE LOGIC (Opsi A - Gabungan)
    // ==========================================================
    // GET /api/user/profile
    // Mengambil data lengkap profil user sekaligus melampirkan notifikasi dan riwayat pesanan
    public function profile(Request $request)
    {
        // 1. Ambil data user yang sedang login
        $user = $request->user();

        // 2. Ambil data notifikasi milik user ini dari database (terbaru di atas)
        $notifications = DB::table('notifications')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 3. Ambil data riwayat pesanan milik user ini (Pesanan paling baru ada di atas)
        $orderHistory = DB::table('orders')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // 4. Kembalikan response gabungan berupa profile + notifikasi + riwayat pesanan
        return response()->json([
            'status'  => true,
            'message' => 'Berhasil mengambil profil, notifikasi, dan riwayat pesanan',
            'data'    => [
                'user' => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'role'    => $user->role,
                    'phone'   => $user->phone,
                    'address' => $user->address,
                ],
                'notifications' => $notifications,
                'order_history' => $orderHistory // <-- Riwayat pesanan sukses digabung ke profile!
            ]
        ], 200);
    }

    // ==========================================================
    // UPDATE ACCOUNT SETTINGS
    // ==========================================================
    // PUT /api/user/profile/update
    // Mengubah informasi akun seperti nama, email, no hp, alamat, atau ganti password
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        // 1. Validasi input (Pengecualian email unik dipasang agar email sendiri tidak bentrok)
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:15',
            'address'  => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed', // Password tidak wajib diisi
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. Data dasar yang akan diperbarui
        $updateData = [
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'address' => $request->address,
        ];

        // 3. Jika user berniat mengubah password, enkripsi password barunya
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // 4. Lakukan pembaruan data di database
        User::where('id', $user->id)->update($updateData);

        // 5. Ambil data user versi terbaru dari database
        $updatedUser = $user->fresh();

        return response()->json([
            'status'  => true,
            'message' => 'Profil akun berhasil diperbarui!',
            'data'    => [
                'id'      => $updatedUser->id,
                'name'    => $updatedUser->name,
                'email'   => $updatedUser->email,
                'role'    => $updatedUser->role,
                'phone'   => $updatedUser->phone,
                'address' => $updatedUser->address,
            ]
        ], 200);
    }
}