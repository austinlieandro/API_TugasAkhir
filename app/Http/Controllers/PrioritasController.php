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
        $bengkels_id = (int)$request->bengkels_id;

        $prioritasData = [];
        foreach ($jenis_kendaraan as $index => $kendaraan) {
            $prioritasData[] = [
                'jenis_kendaraan' => $kendaraan,
                'jenis_kerusakan' => $jenis_kerusakan[$index],
                'bobot_nilai' => $bobot_nilai[$index],
                'bobot_estimasi' => $bobot_estimasi[$index],
                'bobot_urgensi' => $bobot_urgensi[$index],
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

    public function displayPrioritas($bengkels_id)
    {
        $prioritas = Prioritas::where('bengkels_id', $bengkels_id)
            ->orderBy('jenis_kendaraan', 'desc')
            ->orderBy('jenis_kerusakan', 'asc')
            ->get();

        if (!$prioritas) {
            return response()->json([
                'status' => false,
                'message' => 'jenis layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'jenis layanan ditemukan',
            'prioritas' => $prioritas,
        ], 200);
    }

    public function editPrioritas(request $request, $bengkels_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'bobot_nilai' => 'required',
            'bobot_estimasi' => 'required',
            'bobot_urgensi' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $prioritas = Prioritas::where('id', $id)
            ->where('bengkels_id', $bengkels_id)
            ->first();

        if (!$prioritas) {
            return response()->json([
                'status' => false,
                'message' => 'Prioritas tidak ditemukan',
            ], 404);
        }

        $prioritas->update([
            'bobot_nilai' => (int)$request->bobot_nilai,
            'bobot_estimasi' => (int)$request->bobot_estimasi,
            'bobot_urgensi' => (int)$request->bobot_urgensi,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil memperbarui prioritas',
            'prioritas' => $prioritas,
        ], 200);
    }

    public function inputPrioritasSatu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_kendaraan' => 'required',
            'jenis_kerusakan' => 'required',
            'bobot_nilai' => 'required',
            'bobot_estimasi' => 'required',
            'bobot_urgensi' => 'required',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $prioritas = Prioritas::create([
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'jenis_kerusakan' => $request->jenis_kerusakan,
            'bobot_nilai' => (int)$request->bobot_nilai,
            'bobot_estimasi' => (int)$request->bobot_estimasi,
            'bobot_urgensi' => (int)$request->bobot_urgensi,
            'bengkels_id' => (int)$request->bengkels_id,
        ]);

        if ($prioritas) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambah prioritas',
                'prioritas' => $prioritas
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal menambah prioritas',
        ], 409);
    }
}
