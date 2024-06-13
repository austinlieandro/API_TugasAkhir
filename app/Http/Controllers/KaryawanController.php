<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KaryawanController extends Controller
{
    public function karyawan(request $request){
        $validator = Validator::make($request->all(),[
            'nama_karyawan' => 'required',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $karyawan = karyawan::create([
            'nama_karyawan' => $request->nama_karyawan,
            'bengkels_id' => (int)$request->bengkels_id,
        ]);

        if($karyawan){
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan karyawan',
                'karyawan' => $karyawan,
            ], 201);
        }

        return response()->json([
            'status' => true,
            'message' => 'Gagal mendaftarkan karyawan',
        ], 409);
    }
    public function daftarKaryawan($id){
        $karyawan = karyawan::where('bengkels_id', $id)->get();

        if (!$karyawan) {
            return response()->json([
                'status' => false,
                'message' => 'karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'karyawan ditemukan',
            'bengkel' => $karyawan,
        ], 201);
    }
}
