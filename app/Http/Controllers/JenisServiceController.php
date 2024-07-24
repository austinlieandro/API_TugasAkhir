<?php

namespace App\Http\Controllers;

use App\Models\JenisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JenisServiceController extends Controller
{
    public function inputJenisService(request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_service' => 'required',
            'users_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisService = JenisService::create([
            'nama_service' => $request->nama_service,
            'users_id' => (int)$request->users_id
        ]);

        if ($jenisService) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan jenis service',
                'jenisService' => $jenisService,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal mendaftarkan jenis service',
        ], 409);
    }

    public function displayJenisService()
    {
        $jenisService = JenisService::orderBy('nama_service', 'asc')->get();

        if ($jenisService) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan merek kendaraan',
                'jenisService' => $jenisService,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menampilkan merek kendaraan',
        ], 209);
    }

    public function updateJenisService(request $request, $users_id, $service_id)
    {
        $validator = Validator::make($request->all(), [
            'nama_service' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisService = JenisService::where('id', $service_id)->where('users_id', $users_id)->first();
        if (!$jenisService) {
            return response()->json([
                'status' => false,
                'message' => 'jenis Service tidak ditemukan',
            ], 404);
        }

        $jenisService->nama_service = $request->nama_service;
        $jenisService->save();

        return response()->json([
            'status' => true,
            'message' => 'kendaraan berhasil diperbarui',
            'jenisService' => $jenisService,
        ], 200);
    }
}
