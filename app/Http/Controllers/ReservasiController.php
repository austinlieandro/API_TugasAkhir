<?php

namespace App\Http\Controllers;

use App\Models\JamOperasional;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservasiController extends Controller
{
    public function userReservasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_reservasi' => 'required|date',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required',
            'detail_reservasi' => 'required',
            'bengkels_id' => 'required|integer',
            'users_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tanggal_reservasi = $request->tanggal_reservasi;
        $jam_reservasi = $request->jam_reservasi;
        $bengkels_id = (int) $request->bengkels_id;

        $englishDayOfWeek = date('l', strtotime($tanggal_reservasi));

        $daysInIndonesian = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        $dayOfWeek = $daysInIndonesian[$englishDayOfWeek];

        $jamOperasional = JamOperasional::where('bengkels_id', $bengkels_id)
            ->where('hari_operasional', $dayOfWeek)
            ->where('jam_operasional', $jam_reservasi)
            ->first();

        if ($jamOperasional && $jamOperasional->slot > 0) {
            $jamOperasional->slot -= 1;
            $jamOperasional->save();

            $reservasi = Reservasi::create([
                'status_reservasi' => 'menunggu',
                'tanggal_reservasi' => $tanggal_reservasi,
                'jam_reservasi' => $jam_reservasi,
                'jeniskendala_reservasi' => $request->jeniskendala_reservasi,
                'detail_reservasi' => $request->detail_reservasi,
                'bengkels_id' => $bengkels_id,
                'users_id' => (int) $request->users_id,
            ]);

            if ($reservasi) {
                return response()->json([
                    'status' => true,
                    'message' => 'Berhasil melakukan reservasi',
                    'bengkel' => $reservasi,
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal melakukan reservasi',
                ], 409);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Waktu reservasi tidak tersedia',
            ], 409);
        }
    }

    public function assignKaryawan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'karyawan_id' => 'nullable|integer',
            'status_reservasi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reservasi = Reservasi::find($request->id);

        if (!$reservasi) {
            return response()->json([
                'status' => false,
                'message' => 'Reservasi tidak ditemukan',
            ], 404);
        }

        if ($request->has('karyawan_id')) {
            $reservasi->karyawan_id = (int)$request->karyawan_id;
        }

        if ($request->has('status_reservasi')) {
            $reservasi->status_reservasi = $request->status_reservasi;
        }

        $reservasi->save();

        return response()->json([
            'status' => true,
            'message' => 'Karyawan berhasil diassign dan status reservasi diubah',
            'reservasi' => $reservasi,
        ], 200);
    }

    public function displayReservasiUser($users_id)
    {
        $reservasi = DB::table('reservasi')
            ->join('bengkels', 'reservasi.bengkels_id', '=', 'bengkels.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->where('reservasi.users_id', $users_id)
            ->select(
                'reservasi.*',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'karyawan.nama_karyawan'
            )
            ->get();

        if ($reservasi->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada reservasi untuk user ini',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menampilkan reservasi untuk user ini',
            'reservasi' => $reservasi,
        ], 200);
    }

    public function displayReservasiBengkel($bengkels_id)
    {
        $reservasi = DB::table('reservasi')
            ->join('users', 'reservasi.users_id', '=', 'users.id')
            ->join('bengkels', 'reservasi.bengkels_id', '=', 'bengkels.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->where('reservasi.bengkels_id', $bengkels_id)
            ->select(
                'reservasi.*',
                'users.name as user_name',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'karyawan.nama_karyawan'
            )
            ->get();

        if ($reservasi->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Tidak ada reservasi untuk bengkel ini',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menampilkan reservasi untuk bengkel ini',
            'reservasi' => $reservasi,
        ], 200);
    }
}
