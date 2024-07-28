<?php

namespace App\Http\Controllers;

use App\Models\JamOperasional;
use App\Models\JenisLayanan;
use App\Models\Prioritas;
use App\Models\PrioritasHarga;
use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReservasiController extends Controller
{
    private function hitungPrioritas($bengkel_id, $jenis_kendaraan, $jeniskendala_reservasi)
    {
        Log::info("Memulai perhitungan prioritas untuk bengkel_id: $bengkel_id, jenis_kendaraan: $jenis_kendaraan, jeniskendala_reservasi: $jeniskendala_reservasi");

        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenisLayanan = JenisLayanan::where('nama_layanan', $jeniskendala_reservasi)->first();

        if (!$jenisLayanan) {
            Log::warning("Jenis layanan tidak ditemukan: $jeniskendala_reservasi");
            return 0;
        }

        $jenisLayananData = is_array($jenisLayanan->jenis_layanan) ? $jenisLayanan->jenis_layanan : json_decode($jenisLayanan->jenis_layanan, true);

        $max_bobot = 0;
        $max_estimasi_waktu = 0;
        $max_bobot_harga = 0;
        foreach ($jenisLayananData as $jenis_kerusakan) {
            $repairData = $this->getRepairData($bengkel_id, $jenis_kendaraan, strtolower($jenis_kerusakan));

            if ($repairData) {
                Log::info("Ditemukan repair data: ", ['bobot_estimasi' => $repairData->bobot_estimasi, 'bobot_urgensi' => $repairData->bobot_urgensi]);
                $bobotHarga = $this->getBobotHarga($jenisLayanan->harga_layanan, $bengkel_id);

                Log::info("Bobot harga yang ditemukan: $bobotHarga");
                $max_bobot = max($max_bobot, $repairData->bobot_nilai);
                $max_estimasi_waktu = max($max_estimasi_waktu, $repairData->bobot_estimasi);
                $max_bobot_harga = max($max_bobot_harga, $bobotHarga);
            } else {
                Log::warning("Repair data tidak ditemukan untuk jenis_kerusakan: $jenis_kerusakan");
            }
        }

        $faktorUrgensi = $this->hitungFaktorUrgensi($bengkel_id, $jenis_kendaraan, $jeniskendala_reservasi);

        Log::info("Nilai maksimum - bobot: $max_bobot, estimasi_waktu: $max_estimasi_waktu, bobot_harga: $max_bobot_harga, faktor_urgensi: $faktorUrgensi");

        if (
            $max_estimasi_waktu == 0 || $max_bobot_harga == 0
        ) {
            Log::warning("Estimasi waktu atau bobot harga adalah 0, menghindari pembagian dengan nol.");
            return 0;
        }

        $prioritas = ($max_bobot * $faktorUrgensi) / ($max_bobot_harga * $max_estimasi_waktu);
        Log::info("Prioritas dihitung: $prioritas");
        return $prioritas;
    }



    private function getRepairData($bengkel_id, $jenis_kendaraan, $jenis_kerusakan)
    {
        Log::info("Mencari repair data untuk bengkel_id: $bengkel_id, jenis_kendaraan: $jenis_kendaraan, jenis_kerusakan: $jenis_kerusakan");
        return Prioritas::where('bengkels_id', $bengkel_id)
            ->where('jenis_kendaraan', $jenis_kendaraan)
            ->where('jenis_kerusakan', $jenis_kerusakan)
            ->first();
    }

    private function hitungFaktorUrgensi($bengkel_id, $jenis_kendaraan, $jeniskendala_reservasi)
    {
        $jenis_kendaraan = strtolower($jenis_kendaraan);
        $jenisLayanan = JenisLayanan::where('nama_layanan', $jeniskendala_reservasi)->first();

        if (!$jenisLayanan) {
            return 0;
        }

        $jenisLayananData = is_array($jenisLayanan->jenis_layanan) ? $jenisLayanan->jenis_layanan : json_decode(
            $jenisLayanan->jenis_layanan,
            true
        );

        $max_faktor_urgensi = 0;
        foreach ($jenisLayananData as $jenis_kerusakan) {
            $repairData = $this->getRepairData($bengkel_id, $jenis_kendaraan, strtolower($jenis_kerusakan));
            if ($repairData) {
                $max_faktor_urgensi = max($max_faktor_urgensi, $repairData->bobot_urgensi);
            }
        }

        return $max_faktor_urgensi;
    }

    private function getBobotHarga($hargaLayanan, $bengkel_id)
    {
        Log::info("Mencari bobot harga untuk harga layanan: $hargaLayanan");

        $prioritasHargas = PrioritasHarga::where('bengkels_id', $bengkel_id)->orderBy('harga')->get();

        if ($prioritasHargas->isEmpty()) {
            Log::warning("Tidak ada data prioritas_harga ditemukan untuk bengkel_id: $bengkel_id");
            return 0;
        }

        $bobot_nilai = 0;

        foreach ($prioritasHargas as $prioritasHarga) {
            if ($hargaLayanan <= $prioritasHarga->harga) {
                $bobot_nilai = $prioritasHarga->bobot_nilai;
                break;
            }
        }

        if ($bobot_nilai == 0) {
            $bobot_nilai = $prioritasHargas->last()->bobot_nilai;
        }

        Log::info("Bobot harga ditemukan: ", ['bobot_nilai' => $bobot_nilai]);
        return $bobot_nilai;
    }

    public function userReservasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_reservasi' => 'required|date',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required|string',
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

        $reservasiCount = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
            ->where('jam_reservasi', $jam_reservasi)
            ->where('bengkels_id', $bengkels_id)
            ->count();

        $jamOperasionalSelected = JamOperasional::where('bengkels_id', $bengkels_id)
            ->where('hari_operasional', $dayOfWeek)
            ->where('jam_operasional', $jam_reservasi)
            ->first();

        $sisaSlot = $jamOperasionalSelected ? $jamOperasionalSelected->slot - $reservasiCount : null;
        Log::info("SISA SLOT: $sisaSlot");
        if ($jamOperasional && $sisaSlot <= 1) {
            $createdAtNow = now();
            $prioritas = $this->hitungPrioritas($request->bengkels_id, $request->kendaraan_reservasi, $request->jeniskendala_reservasi);

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
                        // $jamOperasional->slot += 1;
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
            $jamOperasional->save();

            return response()->json([
                'status' => true,
                'message' => 'Berhasil melakukan reservasi',
                'bengkel' => $reservasi
            ], 201);
        } else if ($jamOperasional && $sisaSlot > 1) {
            $createdAtNow = now();
            $hargaLayanan = $jenisLayanan->harga_layanan;

            $prioritas = $this->hitungPrioritas($request->bengkels_id, $request->kendaraan_reservasi, $request->jeniskendala_reservasi);

            if ($sisaSlot == 1) {
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
                'jenis_layanan.jenis_layanan',
                'jenis_layanan.harga_layanan',
                'merek_kendaraan.merek_kendaraan'
            )
            ->get();

        if ($reservasi) {
            $reservasi->transform(function ($item, $key) {
                $item->jenis_layanan = json_decode($item->jenis_layanan, true);
                return $item;
            });
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
            ->join('users', 'reservasi.users_id', '=', 'users.id')
            ->leftJoin('karyawan', 'reservasi.karyawan_id', '=', 'karyawan.id')
            ->leftJoin('kendaraan', 'reservasi.kendaraan_id', '=', 'kendaraan.id')
            ->leftJoin('jenis_layanan', 'reservasi.jeniskendala_reservasi', '=', 'jenis_layanan.id')
            ->leftJoin('merek_kendaraan', 'kendaraan.merek_kendaraan_id', '=', 'merek_kendaraan.id')
            ->where('reservasi.id', $id)
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
                'merek_kendaraan.merek_kendaraan',
                'users.name',
                'users.phone',
                'jenis_layanan.jenis_layanan'
            )
            ->get();

        if ($reservasi) {
            $reservasi->transform(function ($item, $key) {
                $item->jenis_layanan = json_decode($item->jenis_layanan, true);
                return $item;
            });
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

    public function updateReservasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reservasi_id' => 'required|integer',
            'tanggal_reservasi' => 'required|date',
            'jam_reservasi' => 'required',
            'jeniskendala_reservasi' => 'required|string',
            'kendaraan_reservasi' => 'required',
            'bengkels_id' => 'required|integer',
            'kendaraan_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $reservasi = Reservasi::find($request->reservasi_id);

        if (!$reservasi) {
            return response()->json([
                'status' => false,
                'message' => 'Reservasi tidak ditemukan',
            ], 404);
        }

        $jenisLayanan = JenisLayanan::where('nama_layanan', $request->jeniskendala_reservasi)->first();
        if (!$jenisLayanan) {
            return response()->json([
                'status' => false,
                'message' => 'Jenis layanan tidak ditemukan',
            ], 404);
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

        $jamOperasional = JamOperasional::where('bengkels_id', $bengkels_id)
            ->where('hari_operasional', $dayOfWeek)
            ->where('jam_operasional', $jam_reservasi)
            ->first();

        $reservasiCount = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
            ->where('jam_reservasi', $jam_reservasi)
            ->where('bengkels_id', $bengkels_id)
            ->count();

        $jamOperasionalSelected = JamOperasional::where('bengkels_id', $bengkels_id)
            ->where('hari_operasional', $dayOfWeek)
            ->where('jam_operasional', $jam_reservasi)
            ->first();

        $sisaSlot = $jamOperasionalSelected ? $jamOperasionalSelected->slot - $reservasiCount : null;
        Log::info("SISA SLOT: $sisaSlot");

        if ($jamOperasional && $sisaSlot <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Waktu reservasi tidak tersedia',
            ], 409);
        }

        $createdAtNow = now();
        $prioritas = $this->hitungPrioritas($request->bengkels_id, $request->kendaraan_reservasi, $request->jeniskendala_reservasi);

        $reservasi->update([
            'tanggal_reservasi' => $tanggal_reservasi,
            'jam_reservasi' => $jam_reservasi,
            'jeniskendala_reservasi' => $jenisLayanan->id,
            'detail_reservasi' => $request->detail_reservasi,
            'kendaraan_reservasi' => $request->kendaraan_reservasi,
            'bengkels_id' => $bengkels_id,
            'kendaraan_id' => $kendaraan_id,
            'prioritas' => (float)$prioritas,
            'created_at' => $createdAtNow,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil mengubah reservasi',
            'reservasi' => $reservasi,
            'sisaSlot' => $sisaSlot,
        ], 200);
    }
}
