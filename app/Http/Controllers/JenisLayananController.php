<?php

namespace App\Http\Controllers;

use App\Models\JenisLayanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisLayananController extends Controller
{
    public function inputJenisLayanan(request $request)
    {
        $validator = Validator::make($request->all(), [
            "nama_layanan" => "required",
            "jenis_layanan" => "required|array",
            "harga_layanan" => "required",
            "bengkels_id" => "required",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisLayanan = JenisLayanan::create([
            'nama_layanan' => $request->nama_layanan,
            'jenis_layanan' => $request->jenis_layanan,
            'harga_layanan' => (int)$request->harga_layanan,
            'bengkels_id' => (int)$request->bengkels_id,
        ]);

        if ($jenisLayanan) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan jenis layanan',
                'jenis_layanan' => $jenisLayanan,
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal mendaftarkan jenis layanan',
        ], 409);
    }

    public function displayJenisLayanan($bengkels_id)
    {
        $jenisLayanan = JenisLayanan::where('bengkels_id', $bengkels_id)
            ->orderBy('harga_layanan', 'desc')
            ->get();

        if (!$jenisLayanan) {
            return response()->json([
                'status' => false,
                'message' => 'jenis layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'jenis layanan ditemukan',
            'jenis_layanan' => $jenisLayanan,
        ], 201);
    }

    public function editJenisLayanan(Request $request, $bengkels_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_layanan' => 'required',
            'jenis_layanan' => 'required|array',
            'harga_layanan' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisLayanan = JenisLayanan::where('id', $id)
            ->where('bengkels_id', $bengkels_id)
            ->first();

        if (!$jenisLayanan) {
            return response()->json([
                'status' => false,
                'message' => 'Jenis layanan tidak ditemukan',
            ], 404);
        }

        $jenisLayanan->update([
            'nama_layanan' => $request->nama_layanan,
            'jenis_layanan' => $request->jenis_layanan,
            'harga_layanan' => $request->harga_layanan,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil memperbarui jenis layanan',
            'jenis_layanan' => $jenisLayanan,
        ], 200);
    }

    public function detailJenisLayanan($id)
    {
        $jenisLayanan = JenisLayanan::where('id', $id)->first();

        if (!$jenisLayanan) {
            return response()->json([
                'status' => false,
                'message' => 'jenis layanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'jenis layanan ditemukan',
            'jenis_layanan' => $jenisLayanan,
        ], 200);
    }
}
