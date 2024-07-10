<?php

namespace App\Http\Controllers;

use App\Models\JamOperasional;
use App\Models\JenisLayanan;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReservasiController extends Controller
{
    private function hitungPrioritas($jenis_kendaraan, $jeniskendala_reservasi, $hargaLayanan)
    {
        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenisLayanan = JenisLayanan::where('nama_layanan', $jeniskendala_reservasi)->first();

        if (!$jenisLayanan) {
            return 0;
        }

        $jenisLayananData = is_array($jenisLayanan->jenis_layanan) ? $jenisLayanan->jenis_layanan : json_decode($jenisLayanan->jenis_layanan, true);

        $bobot_nilai = 0;

        $prioritasMapMobil = [
            'oli' => 6,
            'rem' => 10,
            'busi' => 5,
            'aki' => 7,
            'listrik' => 9,
            'suspensi' => 8,
            'mesin' => 10,
            'ban' => 7,
            'rantai' => 8,
            'karburator/injektor' => 7,
            'body' => 6,
            'filter' => 5,
            'ac' => 6,
            'transmisi' => 9,
            'radiator' => 8
        ];

        $prioritasMapMotor = [
            'oli' => 4,
            'rem' => 8,
            'busi' => 3,
            'aki' => 5,
            'listrik' => 6,
            'suspensi' => 5,
            'mesin' => 8,
            'ban' => 5,
            'rantai' => 6,
            'karburator/injektor' => 5,
            'body' => 3,
            'filter' => 4,
            'transmisi' => 7,
            'radiator' => 6
        ];

        $estimasiWaktuMapMobil = [
            'oli' => 1,
            'rem' => 2,
            'busi' => 1,
            'aki' => 1,
            'listrik' => 3,
            'suspensi' => 4,
            'mesin' => 4,
            'ban' => 1,
            'rantai' => 4,
            'karburator/injektor' => 2,
            'body' => 5,
            'filter' => 1,
            'ac' => 3,
            'transmisi' => 5,
            'radiator' => 2
        ];

        $estimasiWaktuMapMotor = [
            'oli' => 1,
            'rem' => 1,
            'busi' => 1,
            'aki' => 1,
            'listrik' => 2,
            'suspensi' => 1,
            'mesin' => 3,
            'ban' => 1,
            'rantai' => 1,
            'karburator/injektor' => 1,
            'body' => 3,
            'filter' => 1,
            'transmisi' => 3,
            'radiator' => 1
        ];

        $max_bobot = 0;
        $max_estimasi_waktu = 0;
        foreach ($jenisLayananData as $jenis_kerusakan) {
            $jenis_kerusakan_lower = strtolower($jenis_kerusakan);
            if ($jenis_kendaraan == 'mobil') {
                $bobot = $prioritasMapMobil[$jenis_kerusakan_lower] ?? 0;
                $estimasi_waktu = $estimasiWaktuMapMobil[$jenis_kerusakan_lower] ?? 0;
                $max_bobot = max($max_bobot, $bobot);
                $max_estimasi_waktu = max($max_estimasi_waktu, $estimasi_waktu);
            } elseif ($jenis_kendaraan == 'motor') {
                $bobot = $prioritasMapMotor[$jenis_kerusakan_lower] ?? 0;
                $estimasi_waktu = $estimasiWaktuMapMotor[$jenis_kerusakan_lower] ?? 0;
                $max_bobot = max($max_bobot, $bobot);
                $max_estimasi_waktu = max($max_estimasi_waktu, $estimasi_waktu);
            }
        }

        $faktorUrgensi = $this->hitungFaktorUrgensi($jenis_kendaraan, $jeniskendala_reservasi);
        $bobotHarga = $this->hitungBobotHarga($hargaLayanan);

        $prioritas = ($max_bobot * $faktorUrgensi) / ($bobotHarga * $max_estimasi_waktu);
        return $prioritas;
    }

    private function hitungFaktorUrgensi($jenis_kendaraan, $jeniskendala_reservasi)
    {
        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenisLayanan = JenisLayanan::where('nama_layanan', $jeniskendala_reservasi)->first();

        if (!$jenisLayanan) {
            return 0;
        }

        $jenisLayananData = is_array($jenisLayanan->jenis_layanan) ? $jenisLayanan->jenis_layanan : json_decode($jenisLayanan->jenis_layanan, true);

        $faktor_urgensi = 0;

        $faktorUrgensiMapMobil = [
            'oli' => 3,
            'rem' => 4,
            'busi' => 3,
            'aki' => 3,
            'listrik' => 4,
            'suspensi' => 4,
            'mesin' => 5,
            'ban' => 3,
            'rantai' => 4,
            'karburator/injektor' => 3,
            'body' => 4,
            'filter' => 3,
            'ac' => 3,
            'transmisi' => 5,
            'radiator' => 4
        ];

        $faktorUrgensiMapMotor = [
            'oli' => 2,
            'rem' => 3,
            'busi' => 2,
            'aki' => 2,
            'listrik' => 3,
            'suspensi' => 2,
            'mesin' => 4,
            'ban' => 2,
            'rantai' => 2,
            'karburator/injektor' => 2,
            'body' => 3,
            'filter' => 2,
            'transmisi' => 3,
            'radiator' => 2
        ];

        $max_faktor_urgensi = 0;
        foreach ($jenisLayananData as $jenis_kerusakan) {
            $jenis_kerusakan_lower = strtolower($jenis_kerusakan);
            if ($jenis_kendaraan == 'mobil') {
                $faktor_urgensi = $faktorUrgensiMapMobil[$jenis_kerusakan_lower] ?? 0;
                $max_faktor_urgensi = max($max_faktor_urgensi, $faktor_urgensi);
            } elseif ($jenis_kendaraan == 'motor') {
                $faktor_urgensi = $faktorUrgensiMapMotor[$jenis_kerusakan_lower] ?? 0;
                $max_faktor_urgensi = max($max_faktor_urgensi, $faktor_urgensi);
            }
        }

        return $max_faktor_urgensi;
    }

    private function hitungBobotHarga($hargaLayanan)
    {
        if ($hargaLayanan <= 100000) {
            return 5;
        } elseif ($hargaLayanan <= 200000) {
            return 4;
        } elseif ($hargaLayanan <= 300000) {
            return 3;
        } elseif ($hargaLayanan <= 400000) {
            return 2;
        } else {
            return 1;
        }
    }

    public function userReservasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_reservasi' => 'required|date',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required|string',
            // 'detail_reservasi' => 'required',
            'kendaraan_reservasi' => 'required',
            'bengkels_id' => 'required|integer',
            'users_id' => 'required|integer',
            'kendaraan_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jenisLayanan = JenisLayanan::where('nama_layanan', $request->jeniskendala_reservasi)->first();
        if (!$jenisLayanan) {
            return response()->json([
                'status' => false,
                'message' => 'Jenis layanan tidak ditemukan'
            ], 404);
        }

        $hargaLayanan = $jenisLayanan->harga_layanan;

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
            // $createdAtNow = '2023-10-01 00:00:00';
            $prioritas = $this->hitungPrioritas($request->kendaraan_reservasi, $request->jeniskendala_reservasi, $hargaLayanan);

            $reservasiSama = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
                ->where('jam_reservasi', $jam_reservasi)
                ->where('bengkels_id', $bengkels_id)
                ->where('created_at', $createdAtNow)
                ->orderBy('jeniskendala_reservasi', 'desc')
                ->get();

            if ($reservasiSama->count() >= 1) {
                $prioritasTertinggi = $reservasiSama->first()->prioritas;
                if ($prioritas > $prioritasTertinggi) {
                    foreach ($reservasiSama as $reservasi) {
                        $jamOperasional->slot += 1;
                        $reservasi->status_reservasi = 'dibatalkan';
                        $reservasi->save();
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'prioritas reservasi baru lebih rendah atau sama dengan reservasi yang ada',
                    ], 409);
                }
            }

            $reservasi = Reservasi::create([
                'status_reservasi' => $status_reservasi,
                'tanggal_reservasi' => $tanggal_reservasi,
                'jam_reservasi' => $jam_reservasi,
                'jeniskendala_reservasi' => $jenisLayanan->id,
                'detail_reservasi' => $request->detail_reservasi,
                'kendaraan_reservasi' => $request->kendaraan_reservasi,
                'bengkels_id' => $bengkels_id,
                'users_id' => (int) $request->users_id,
                'kendaraan_id' => (int)$request->kendaraan_id,
                'created_at' => $createdAtNow,
                'prioritas' => (float)$prioritas,
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
            // $createdAtNow = '2023-10-01 00:00:00';
            $hargaLayanan = $jenisLayanan->harga_layanan;

            $prioritas = $this->hitungPrioritas($request->kendaraan_reservasi, $request->jeniskendala_reservasi, $hargaLayanan);

            if ($jamOperasional->slot == 1) {
                $reservasiSama = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
                    ->where('jam_reservasi', $jam_reservasi)
                    ->where('bengkels_id', $bengkels_id)
                    ->where('created_at', $createdAtNow)
                    ->orderBy('jeniskendala_reservasi', 'desc')
                    ->get();

                if ($reservasiSama->count() >= 1) {
                    $prioritasTertinggi = $reservasiSama->first()->prioritas;
                    if ($prioritas > $prioritasTertinggi) {
                        foreach ($reservasiSama as $reservasi) {
                            $reservasi->status_reservasi = 'dibatalkan';
                            $reservasi->save();
                        }
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'prioritas reservasi baru lebih rendah atau sama dengan reservasi yang ada',
                        ], 409);
                    }
                }
            }

            $reservasi = Reservasi::create([
                'status_reservasi' => $status_reservasi,
                'tanggal_reservasi' => $tanggal_reservasi,
                'jam_reservasi' => $jam_reservasi,
                'jeniskendala_reservasi' => $jenisLayanan->id,
                'detail_reservasi' => $request->detail_reservasi,
                'kendaraan_reservasi' => $request->kendaraan_reservasi,
                'bengkels_id' => $bengkels_id,
                'users_id' => (int) $request->users_id,
                'kendaraan_id' => (int)$request->kendaraan_id,
                'created_at' => $createdAtNow,
                'prioritas' => (float)$prioritas,
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
            ->leftJoin('jenis_layanan', 'reservasi.jeniskendala_reservasi', '=', 'jenis_layanan.id')
            ->leftJoin('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->where('reservasi.users_id', $users_id)
            ->orderBy('reservasi.created_at', 'desc')
            ->select(
                'reservasi.*',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'bengkels.gmaps_bengkel',
                'karyawan.nama_karyawan',
                'kendaraan.plat_kendaraan',
                'jenis_layanan.nama_layanan',
                'merek_kendaraan.merek_kendaraan'
            )
            ->get();

        if ($reservasi) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan reservasi untuk user ini',
                'reservasi' => $reservasi,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Tidak ada reservasi untuk user ini',
        ], 404);
    }


    public function displayReservasiBengkel($bengkels_id)
    {
        $reservasi = DB::table('reservasi')
            ->join('users', 'reservasi.users_id', '=', 'users.id')
            ->join('bengkels', 'reservasi.bengkels_id', '=', 'bengkels.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->leftJoin('kendaraan', 'reservasi.kendaraan_id', '=', 'kendaraan.id')
            ->leftJoin('jenis_layanan', 'reservasi.jeniskendala_reservasi', '=', 'jenis_layanan.id')
            ->leftJoin('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->where('reservasi.bengkels_id', $bengkels_id)
            ->orderBy('reservasi.created_at', 'desc')
            ->select(
                'reservasi.*',
                'users.name as user_name',
                'users.phone as user_phone',
                'karyawan.nama_karyawan',
                'kendaraan.plat_kendaraan',
                'jenis_layanan.nama_layanan',
                'merek_kendaraan.merek_kendaraan'
            )
            ->get();

        if ($reservasi) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan reservasi untuk user ini',
                'reservasi' => $reservasi,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Tidak ada reservasi untuk user ini',
        ], 404);
    }

    public function detailReservasi($id)
    {
        $reservasi = DB::table('reservasi')
            ->join('bengkels', 'reservasi.bengkels_id', '=', 'bengkels.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->leftJoin('kendaraan', 'reservasi.kendaraan_id', '=', 'kendaraan.id')
            ->leftJoin('jenis_layanan', 'reservasi.jeniskendala_reservasi', '=', 'jenis_layanan.id')
            ->leftJoin('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->where('reservasi.users_id', $id)
            ->orderBy('reservasi.created_at', 'desc')
            ->select(
                'reservasi.*',
                'bengkels.nama_bengkel',
                'bengkels.lokasi_bengkel',
                'bengkels.number_bengkel',
                'bengkels.alamat_bengkel',
                'bengkels.gmaps_bengkel',
                'karyawan.nama_karyawan',
                'kendaraan.plat_kendaraan',
                'jenis_layanan.nama_layanan',
                'merek_kendaraan.merek_kendaraan'
            )
            ->get();

        if ($reservasi) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menampilkan reservasi untuk user ini',
                'reservasi' => $reservasi,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Tidak ada reservasi untuk user ini',
        ], 404);
    }
}
