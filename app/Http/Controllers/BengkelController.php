<?php

namespace App\Http\Controllers;

use App\Models\Bengkels;
use App\Models\JamOperasional;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BengkelController extends Controller
{
    public function daftarBengkel(request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_bengkel' => 'required',
            'lokasi_bengkel' => 'required',
            'number_bengkel' => 'required',
            'alamat_bengkel' => 'required',
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

        if ($bengkel) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mendaftarkan bengkel',
                'bengkel' => $bengkel,
                'user' => $user,
            ], 201);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal mendaftarkan bengkel',
        ], 409);
    }

    public function showAllbengkels()
    {
        $bengkels = Bengkels::all();

        if ($bengkels) {
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

    public function detailBengkels($users_id, $bengkels_id)
    {
        $validator = Validator::make(
            ['users_id' => $users_id, 'bengkels_id' => $bengkels_id],
            ['users_id' => 'required|exists:users,id', 'bengkels_id' => 'required|exists:bengkels,id']
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $bengkel = Bengkels::find($bengkels_id);

        if (!$bengkel) {
            return response()->json([
                'status' => false,
                'message' => 'Bengkel tidak ditemukan',
            ], 404);
        }

        $bengkel->jenis_kendaraan = is_string($bengkel->jenis_kendaraan) ? json_decode($bengkel->jenis_kendaraan) : $bengkel->jenis_kendaraan;
        $bengkel->jenis_layanan = is_string($bengkel->jenis_layanan) ? json_decode($bengkel->jenis_layanan) : $bengkel->jenis_layanan;
        $bengkel->hari_operasional = is_string($bengkel->hari_operasional) ? json_decode($bengkel->hari_operasional) : $bengkel->hari_operasional;

        $jamOperasional = JamOperasional::where('bengkels_id', $bengkels_id)->get();

        $favorit = DB::table('favorit')
        ->where('users_id', $users_id)
            ->where('bengkels_id', $bengkels_id)
            ->first();

        $statusFavorit = $favorit ? $favorit->status_favorit : '0';

        return response()->json([
            'status' => true,
            'message' => 'Bengkel ditemukan',
            'bengkel' => $bengkel,
            'jam_operasional' => $jamOperasional,
            'status_favorit' => $statusFavorit,
        ], 201);
    }


    public function editBengkel(Request $request, $users_id, $id)
    {
        $validator = Validator::make($request->all(), [
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
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $bengkel = Bengkels::find($id);

        if (!$bengkel) {
            return response()->json([
                'status' => false,
                'message' => 'Bengkel tidak ditemukan',
            ], 404);
        }

        $bengkel->nama_bengkel = $request->nama_bengkel;
        $bengkel->lokasi_bengkel = $request->lokasi_bengkel;
        $bengkel->number_bengkel = $request->number_bengkel;
        $bengkel->alamat_bengkel = $request->alamat_bengkel;
        $bengkel->gmaps_bengkel = $request->gmaps_bengkel;
        $bengkel->jenis_kendaraan = $request->jenis_kendaraan;
        $bengkel->jenis_layanan = $request->jenis_layanan;
        $bengkel->hari_operasional = $request->hari_operasional;
        $bengkel->jam_buka = $request->jam_buka;
        $bengkel->jam_tutup = $request->jam_tutup;
        $bengkel->users_id = (int)$users_id;

        if ($bengkel->save()) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil mengedit bengkel',
                'bengkel' => $bengkel,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal mengedit bengkel',
        ], 500);
    }
}
