<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReservasiController extends Controller
{
    public function userReservasi(request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'status_reservasi' => 'required',
            'tanggal_reservasi' => 'required',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required',
            'detail_reservasi' => 'required',
            'bengkels_id' => 'required',
            'users_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reservasi = Reservasi::create([
            'status_reservasi' => 'menunggu',
            'tanggal_reservasi' => $request->tanggal_reservasi,
            'jam_reservasi' => $request->jam_reservasi,
            'jeniskendala_reservasi' => $request->jeniskendala_reservasi,
            'detail_reservasi' => $request->detail_reservasi,
            'bengkels_id' => (int)$request->bengkels_id,
            'users_id' => (int)$request->users_id,
        ]);

        if ($reservasi) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil melakukan reservasi',
                'bengkel' => $reservasi,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal melakukan reservasi',
        ], 409);
    }
}
