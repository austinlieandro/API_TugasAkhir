<?php

namespace App\Http\Controllers;

use App\Models\Prioritas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrioritasController extends Controller
{
    public function inputPrioritas(request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kendaraan' => 'required|array',
            'jenis_kerusakan' => 'required|array',
            'bobot_nilai' => 'required|array',
            'bobot_estimasi' => 'required|array',
            'bobot_urgensi' => 'required|array',
            'bobot_harga' => 'required|array',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenis_kendaraan = $request->jenis_kendaraan;
        $jenis_kerusakan = $request->jenis_kerusakan;
        $bobot_nilai = $request->bobot_nilai;
        $bobot_estimasi = $request->bobot_estimasi;
        $bobot_urgensi = $request->bobot_urgensi;
        $bobot_harga = $request->bobot_harga;
        $bengkels_id = (int)$request->bengkels_id;

        $prioritasData = [];
        foreach ($jenis_kendaraan as $index => $kendaraan) {
            $prioritasData[] = [
                'jenis_kendaraan' => $kendaraan,
                'jenis_kerusakan' => $jenis_kerusakan[$index],
                'bobot_nilai' => $bobot_nilai[$index],
                'bobot_estimasi' => $bobot_estimasi[$index],
                'bobot_urgensi' => $bobot_urgensi[$index],
                'bobot_harga' => $bobot_harga[$index],
                'bengkels_id' => $bengkels_id,
            ];
        }

        $prioritas = Prioritas::insert($prioritasData);

        if ($prioritas) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan Prioritas',
                'prioritas' => $prioritasData
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menambahkan prioritas',
        ], 409);
    }
}
