<?php

namespace App\Http\Controllers;

use App\Models\MerekKendaraan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MerekKendaraanController extends Controller
{
    public function inputMerekKendaraan(request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kendaraan' => 'required',
            'merek_kendaraan' => 'required',
            'users_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $merekKendaraan = MerekKendaraan::create([
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'merek_kendaraan' => $request->merek_kendaraan,
            'users_id' => (int)$request->users_id,
        ]);

        if ($merekKendaraan) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan merek kendaraan',
                'merek_kendaraan' => $merekKendaraan,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal mendaftarkan merek kendaraan',
        ], 409);
    }

    public function displayMerekKendaraan()
    {
        $merekKendaraan = MerekKendaraan::orderBy('merek_kendaraan', 'asc')->get();

        if ($merekKendaraan) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan merek kendaraan',
                'merek_kendaraan' => $merekKendaraan,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menampilkan merek kendaraan',
        ], 209);
    }

    public function updateMerekKendaraan(request $request, $users_id, $merek_kendaraan_id)
    {
        $validator = Validator::make($request->all(), [
            'merek_kendaraan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $merekKendaraan = MerekKendaraan::where('id', $merek_kendaraan_id)->where('users_id', $users_id)->first();
        if (!$merekKendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'Merek kendaraan tidak ditemukan',
            ], 404);
        }

        $merekKendaraan->merek_kendaraan = $request->merek_kendaraan;
        $merekKendaraan->save();

        return response()->json([
            'status' => true,
            'message' => 'kendaraan berhasil diperbarui',
            'merek_kendaraan' => $merekKendaraan,
        ], 200);
    }
}
