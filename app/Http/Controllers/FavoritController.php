<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoritController extends Controller
{
    public function toggleFavoritBengkel(Request $request)
    {
        $request->validate([
            'users_id' => 'required|exists:users,id',
            'bengkels_id' => 'required|exists:bengkels,id',
        ]);

        $favorit = DB::table('favorit')
        ->where('users_id', $request->users_id)
            ->where('bengkels_id', $request->bengkels_id)
            ->first();

        if ($favorit) {
            $newStatus = $favorit->status_favorit == '1' ? '0' : '1';

            if ($newStatus == '0') {
                DB::table('favorit')
                ->where('users_id', $request->users_id)
                    ->where('bengkels_id', $request->bengkels_id)
                    ->delete();

                $message = 'Bengkel berhasil dihapus dari favorit';
            } else {
                DB::table('favorit')
                ->where('users_id', $request->users_id)
                    ->where('bengkels_id', $request->bengkels_id)
                    ->update(['status_favorit' => $newStatus]);

                $message = 'Bengkel berhasil ditambahkan ke favorit';
            }

            return response()->json([
                'status' => true,
                'message' => $message,
            ], 200);
        } else {
            DB::table('favorit')->insert([
                'users_id' => $request->users_id,
                'bengkels_id' => $request->bengkels_id,
                'status_favorit' => '1',
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Bengkel berhasil ditambahkan ke favorit',
            ], 200);
        }
    }


    public function displayUserFavorit($user_id)
    {
        $userExists = DB::table('users')->where('id', $user_id)->exists();

        if (!$userExists) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $favorites = DB::table('favorit')
            ->join('bengkels', 'favorit.bengkels_id', '=', 'bengkels.id')
            ->where('favorit.users_id', $user_id)
            ->select('bengkels.*')
            ->get()
            ->map(function ($bengkel) {
                $bengkel->jenis_kendaraan = json_decode($bengkel->jenis_kendaraan);
                $bengkel->jenis_layanan = json_decode($bengkel->jenis_layanan);
                $bengkel->hari_operasional = json_decode($bengkel->hari_operasional);
                return $bengkel;
            });

        return response()->json([
            'status' => true,
            'message' => 'Berhasil menampilkan favorit bengkel',
            'favorites' => $favorites,
        ], 200);
    }
}
