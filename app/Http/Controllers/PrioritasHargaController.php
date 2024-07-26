<?php

namespace App\Http\Controllers;

use App\Models\PrioritasHarga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrioritasHargaController extends Controller
{
    public function inputPrioritasHarga(request $request)
    {
        $validator = Validator::make($request->all(), [
            'harga' => 'required|array',
            'bobot_nilai' => 'required|array',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $harga = $request->harga;
        $bobot_nilai = $request->bobot_nilai;
        $bengkels_id = (int)$request->bengkels_id;

        $prioritasData = [];
        foreach ($harga as $index => $price) {
            $prioritasData[] = [
                'harga' => $price,
                'bobot_nilai' => $bobot_nilai[$index],
                'bengkels_id' => $bengkels_id,
            ];
        }

        $prioritas = PrioritasHarga::insert($prioritasData);

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

    public function displayPrioritasHarga($bengkels_id)
    {
        $prioritas = PrioritasHarga::where('bengkels_id', $bengkels_id)
            ->orderBy('harga', 'asc')
            ->get();

        if (!$prioritas) {
            return response()->json([
                'status' => false,
                'message' => 'prioritas harga tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'prioritas harga ditemukan',
            'prioritas' => $prioritas,
        ], 200);
    }

    public function editPrioritasHarga(request $request, $bengkels_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'harga' => 'required',
            'bobot_nilai' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $prioritas = PrioritasHarga::where('id', $id)
            ->where('bengkels_id', $bengkels_id)
            ->first();

        if (!$prioritas) {
            return response()->json([
                'status' => false,
                'message' => 'Prioritas tidak ditemukan',
            ], 404);
        }

        $prioritas->update([
            'harga' => (int)$request->harga,
            'bobot_nilai' => (int)$request->bobot_nilai,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil memperbarui prioritas',
            'prioritas' => $prioritas,
        ], 200);
    }
}
