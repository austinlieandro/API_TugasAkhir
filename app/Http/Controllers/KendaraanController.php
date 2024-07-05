<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\Kendaraan;
use App\Models\Users;

class KendaraanController extends Controller
{
    public function kendaraan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kendaraan' => 'required',
            'plat_kendaraan' => 'required',
            'users_id' => 'required|integer',
            'merek_kendaraan_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $existingKendaraan = Kendaraan::where('plat_kendaraan', $request->plat_kendaraan)->first();

        if ($existingKendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'Plat kendaraan telah terdaftar'
            ], 400);
        }

        $kendaraan = Kendaraan::create([
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'plat_kendaraan' => $request->plat_kendaraan,
            'users_id' => (int)$request->users_id,
            'merek_kendaraan_id' => (int)$request->merek_kendaraan_id,
        ]);

        if ($kendaraan) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil memasukan data kendaraan',
                'kendaraan' => $kendaraan,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal memasukan data kendaraan'
        ], 409);
    }

    public function kendaraanUser($id)
    {
        $kendaraan = Kendaraan::where('kendaraan.users_id', $id)
            ->join('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->orderBy('kendaraan.plat_kendaraan', 'asc')
            ->select('kendaraan.*', 'merek_kendaraan.jenis_kendaraan', 'merek_kendaraan.merek_kendaraan')
            ->get();

        if (!$kendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'Kendaraan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kendaraan ditemukan',
            'kendaraan' => $kendaraan,
        ], 201);
    }


    public function updateKendaraan(Request $request, $users_id, $kendaraan_id)
    {
        $validator = Validator::make($request->all(), [
            'plat_kendaraan' => 'required',
            'merek_kendaraan_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kendaraan = Kendaraan::where('id', $kendaraan_id)->where('users_id', $users_id)->first();
        if (!$kendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'Data kendaraan tidak ditemukan',
            ], 404);
        }

        $kendaraan->plat_kendaraan = $request->plat_kendaraan;
        $kendaraan->merek_kendaraan_id = (int)$request->merek_kendaraan_id;
        $kendaraan->save();

        return response()->json([
            'status' => true,
            'message' => 'kendaraan berhasil diperbarui',
            'kendaraan' => $kendaraan,
        ], 200);
    }

    public  function deleteKendaraan(request $request, $users_id, $kendaraan_id)
    {
        $validator = Validator::make($request->all(), []);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $kendaraan = Kendaraan::where('id', $kendaraan_id)->where('users_id', $users_id)->first();

        if (!$kendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'kendaraan tidak ditemukan',
            ], 404);
        }

        if ($kendaraan->delete()) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menghapus data kendaraan',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Gagal menghapus data kendaraan',
            ], 500);
        }
    }

    public function kendaraanDetail($users_id, $kendaraan_id)
    {
        $kendaraan = Kendaraan::where('kendaraan.id', $kendaraan_id)
            ->where('kendaraan.users_id', $users_id)
            ->join('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->select('kendaraan.*', 'merek_kendaraan.jenis_kendaraan', 'merek_kendaraan.merek_kendaraan')
            ->first();

        if (!$kendaraan) {
            return response()->json([
                'status' => false,
                'message' => 'Kendaraan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Kendaraan ditemukan',
            'kendaraan' => $kendaraan,
        ], 200);
    }
}
