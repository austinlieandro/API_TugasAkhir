<?php

namespace App\Http\Controllers;

use App\Models\JamOperasional;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReservasiController extends Controller
{
    private function hitungPrioritas($jenis_kendaraan, $jenis_kerusakan)
    {
        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenis_kerusakan = strtolower($jenis_kerusakan);

        $bobot_nilai = 0;
        if ($jenis_kendaraan == 'mobil') {
            switch ($jenis_kerusakan) {
                case 'oli':
                    $bobot_nilai = 6;
                    break;
                case 'rem':
                    $bobot_nilai = 10;
                    break;
                case 'busi':
                    $bobot_nilai = 5;
                    break;
                case 'aki':
                    $bobot_nilai = 7;
                    break;
                case 'listrik':
                    $bobot_nilai = 9;
                    break;
                case 'suspensi':
                    $bobot_nilai = 8;
                    break;
                case 'mesin':
                    $bobot_nilai = 10;
                    break;
                case 'ban':
                    $bobot_nilai = 7;
                    break;
                case 'rantai':
                    $bobot_nilai = 8;
                    break;
                case 'karburator':
                    $bobot_nilai = 7;
                    break;
                case 'body':
                    $bobot_nilai = 6;
                    break;
                case 'filter':
                    $bobot_nilai = 5;
                    break;
                case 'ac':
                    $bobot_nilai = 6;
                    break;
                case 'transmisi':
                    $bobot_nilai = 9;
                    break;
                case 'radiator':
                    $bobot_nilai = 8;
                    break;
                default:
                    $bobot_nilai = 0;
                    break;
            }
        } elseif ($jenis_kendaraan == 'motor') {
            switch ($jenis_kerusakan) {
                case 'oli':
                    $bobot_nilai = 4;
                    break;
                case 'rem':
                    $bobot_nilai = 8;
                    break;
                case 'busi':
                    $bobot_nilai = 3;
                    break;
                case 'aki':
                    $bobot_nilai = 5;
                    break;
                case 'listrik':
                    $bobot_nilai = 6;
                    break;
                case 'suspensi':
                    $bobot_nilai = 5;
                    break;
                case 'mesin':
                    $bobot_nilai = 8;
                    break;
                case 'ban':
                    $bobot_nilai = 5;
                    break;
                case 'rantai':
                    $bobot_nilai = 6;
                    break;
                case 'karburator':
                    $bobot_nilai = 5;
                    break;
                case 'body':
                    $bobot_nilai = 3;
                    break;
                case 'filter':
                    $bobot_nilai = 4;
                    break;
                case 'transmisi':
                    $bobot_nilai = 7;
                    break;
                case 'radiator':
                    $bobot_nilai = 6;
                    break;
                default:
                    $bobot_nilai = 0;
                    break;
            }
        }

        $prioritas = $bobot_nilai * $this->hitungFaktorUrgensi($jenis_kendaraan, $jenis_kerusakan);

        return $prioritas;
    }

    private function hitungFaktorUrgensi($jenis_kendaraan, $jenis_kerusakan)
    {
        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenis_kerusakan = strtolower($jenis_kerusakan);

        $faktor_urgensi = 0;
        if ($jenis_kendaraan == 'mobil') {
            switch ($jenis_kerusakan) {
                case 'oli':
                case 'rem':
                case 'busi':
                case 'aki':
                case 'listrik':
                case 'suspensi':
                    $faktor_urgensi = 4;
                    break;
                case 'mesin':
                case 'ban':
                case 'rantai':
                case 'karburator':
                case 'body':
                case 'filter':
                case 'ac':
                case 'transmisi':
                case 'radiator':
                    $faktor_urgensi = 5;
                    break;
                default:
                    $faktor_urgensi = 0;
                    break;
            }
        } elseif ($jenis_kendaraan == 'motor') {
            switch ($jenis_kerusakan) {
                case 'oli':
                case 'rem':
                case 'busi':
                case 'aki':
                case 'listrik':
                case 'suspensi':
                    $faktor_urgensi = 3;
                    break;
                case 'mesin':
                case 'ban':
                case 'rantai':
                case 'karburator':
                case 'body':
                case 'filter':
                case 'transmisi':
                case 'radiator':
                    $faktor_urgensi = 4;
                    break;
                default:
                    $faktor_urgensi = 0;
                    break;
            }
        }

        return $faktor_urgensi;
    }

    public function userReservasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_reservasi' => 'required|date',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required',
            'detail_reservasi' => 'required',
            'kendaraan_reservasi' => 'required',
            'bengkels_id' => 'required|integer',
            'users_id' => 'required|integer',
            'kendaraan_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $tanggal_reservasi = $request->tanggal_reservasi;
        $jam_reservasi = $request->jam_reservasi;
        $bengkels_id = (int) $request->bengkels_id;
        $kendaraan_id = (int) $request->kendaraan_id;

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

        $status_reservasi = 'menunggu';

        $jamOperasional = JamOperasional::where('bengkels_id', $bengkels_id)
            ->where('hari_operasional', $dayOfWeek)
            ->where('jam_operasional', $jam_reservasi)
            ->first();

        if ($jamOperasional && $jamOperasional->slot <= 1) {
            $createdAtNow = now();
            $prioritasBaru = $this->hitungPrioritas($request->kendaraan_reservasi, $request->jeniskendala_reservasi);

            $reservasiSama = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
                ->where('jam_reservasi', $jam_reservasi)
                ->where('bengkels_id', $bengkels_id)
                ->orderBy('prioritas', 'desc')
                ->get();

            if ($reservasiSama->count() >= 1) {
                $prioritasTertinggi = $reservasiSama->first()->prioritas;
                if ($prioritasBaru > $prioritasTertinggi) {
                    foreach ($reservasiSama as $reservasi) {
                        $reservasi->status_reservasi = 'dibatalkan';
                        $reservasi->save();
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Prioritas reservasi baru lebih rendah atau sama dengan reservasi yang ada',
                    ], 409);
                }
            }

            $reservasi = Reservasi::create([
                'status_reservasi' => $status_reservasi,
                'tanggal_reservasi' => $tanggal_reservasi,
                'jam_reservasi' => $jam_reservasi,
                'jeniskendala_reservasi' => $request->jeniskendala_reservasi,
                'detail_reservasi' => $request->detail_reservasi,
                'kendaraan_reservasi' => $request->kendaraan_reservasi,
                'bengkels_id' => $bengkels_id,
                'users_id' => (int) $request->users_id,
                'kendaraan_id' => (int)$request->kendaraan_id,
                'prioritas' => $prioritasBaru,
                'created_at' => $createdAtNow,
            ]);

            $jamOperasional->slot = 0;
            $jamOperasional->save();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil melakukan reservasi',
                'bengkel' => $reservasi
            ], 201);
        } else if ($jamOperasional && $jamOperasional->slot > 1) {
            $createdAtNow = now();
            $prioritasBaru = $this->hitungPrioritas($request->kendaraan_reservasi, $request->jeniskendala_reservasi);

            if ($jamOperasional->slot == 1) {
                $reservasiSama = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
                    ->where('jam_reservasi', $jam_reservasi)
                    ->where('bengkels_id', $bengkels_id)
                    ->orderBy('prioritas', 'desc')
                    ->get();

                if ($reservasiSama->count() >= 1) {
                    $prioritasTertinggi = $reservasiSama->first()->prioritas;
                    if ($prioritasBaru > $prioritasTertinggi) {
                        foreach ($reservasiSama as $reservasi) {
                            $reservasi->status_reservasi = 'dibatalkan';
                            $reservasi->save();
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Prioritas reservasi baru lebih rendah atau sama dengan reservasi yang ada',
                        ], 409);
                    }
                }
            }

            $reservasi = Reservasi::create([
                'status_reservasi' => $status_reservasi,
                'tanggal_reservasi' => $tanggal_reservasi,
                'jam_reservasi' => $jam_reservasi,
                'jeniskendala_reservasi' => $request->jeniskendala_reservasi,
                'detail_reservasi' => $request->detail_reservasi,
                'kendaraan_reservasi' => $request->kendaraan_reservasi,
                'bengkels_id' => $bengkels_id,
                'users_id' => (int) $request->users_id,
                'kendaraan_id' => (int)$request->kendaraan_id,
                'prioritas' => $prioritasBaru,
                'created_at' => $createdAtNow,
            ]);

            $jamOperasional->slot -= 1;
            $jamOperasional->save();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil melakukan reservasi',
                'bengkel' => $reservasi
            ], 201);
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

        if ($request->filled('karyawan_id') && $request->karyawan_id != 0) {
            $reservasi->karyawan_id = (int)$request->karyawan_id;
        }

        if ($request->filled('status_reservasi')) {
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
            ->leftJoin('kendaraan', 'reservasi.kendaraan_id', '=', 'kendaraan.id')
            ->where('reservasi.users_id', $users_id)
            ->select(
                'reservasi.*',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'bengkels.gmaps_bengkel',
                'karyawan.nama_karyawan',
                'kendaraan.merek_kendaraan',
                'kendaraan.plat_kendaraan'
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
            ->leftJoin('kendaraan', 'reservasi.kendaraan_id', '=', 'kendaraan.id')
            ->where('reservasi.bengkels_id', $bengkels_id)
            ->select(
                'reservasi.*',
                'users.name as user_name',
                'users.phone as user_phone',
                'karyawan.nama_karyawan',
                'kendaraan.merek_kendaraan',
                'kendaraan.plat_kendaraan'
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

    public function detailReservasi($id)
    {
        $reservasi = DB::table('reservasi')
            ->join('users', 'reservasi.users_id', '=', 'users.id')
            ->join('bengkels', 'reservasi.bengkels_id', '=', 'bengkels.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->where('reservasi.id', $id)
            ->select(
                'reservasi.*',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'bengkels.gmaps_bengkel',
                'karyawan.nama_karyawan'
            )
            ->first();

        if (!$reservasi) {
            return response()->json([
                'status' => false,
                'message' => 'Reservasi tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Detail reservasi berhasil ditemukan',
            'reservasi' => $reservasi,
        ], 200);
    }
}
