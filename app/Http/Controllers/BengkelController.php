<?php

namespace App\Http\Controllers;

use App\Models\Bengkels;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BengkelController extends Controller
{
    public function daftarBengkel(request $request){
        $validator = Validator::make($request->all(),[
            'nama_bengkel' => 'required',
            'lokasi_bengkel' => 'required',
            'number_bengkel' => 'required',
            'alamat_bengkel' => 'required',
            'gmaps_bengkel' => 'required',
            'jenis_kendaraan' => 'required|array',
            'jenis_layanan' => 'required|array',
            'hari_operasional' => 'required|array',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
            'users_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $bengkel = Bengkels::create([
            'nama_bengkel' => $request->nama_bengkel,
            'lokasi_bengkel' => $request->lokasi_bengkel,
            'number_bengkel' => $request->number_bengkel,
            'alamat_bengkel' => $request->alamat_bengkel,
            'gmaps_bengkel' => $request->gmaps_bengkel,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'jenis_layanan' => $request->jenis_layanan,
            'hari_operasional' => $request->hari_operasional,
            'jam_buka' => $request->jam_buka,
            'jam_tutup' => $request->jam_tutup,
            'users_id' => (int)$request->users_id,
        ]);

        $user = Users::find($request->users_id);
        $user->user_bengkel = 'owner';
        $user->save();

        if ($bengkel){
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan bengkel',
                'bengkel' => $bengkel,
            ], 201);
        }
        return response()->json([
            'status' => true,
            'message' => 'Gagal mendaftarkan bengkel',
        ], 409);
    }

    public function showAllbengkels(){
        $bengkels = Bengkels::all();

        if($bengkels){
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan bengkel',
                'bengkel' => $bengkels,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menampilkan bengkel',
        ], 209);
    }

    public function detailBengkels($id){
        $bengkel = Bengkels::find($id);

        if(!$bengkel){
            return response()->json([
                'status' => false,
                'message' => 'Bengkel tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bengkel ditemukan',
            'bengkel' => $bengkel,
        ], 201);
    }
}
