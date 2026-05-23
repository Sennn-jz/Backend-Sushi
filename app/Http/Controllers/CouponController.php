<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'status' => false,
                'message' => 'Kupon tidak valid'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kupon berhasil digunakan',
            'discount' => $coupon->discount
        ]);
    }
}