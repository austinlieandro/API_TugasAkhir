<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FavoritController extends Controller
{
    public function favoritBengkel(Request $request)
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
            return response()->json([
                'status' => false,
                'message' => 'Bengkel sudah ditambahkan ke favorit',
            ], 400);
        } else {
            DB::table('favorit')->insert([
                'users_id' => $request->users_id,
                'bengkels_id' => $request->bengkels_id,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Bengkel berhasil ditambahkan ke favorit',
            ], 200);
        }
    }

    public function unfavoritBengkel(Request $request)
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
            DB::table('favorit')
                ->where('users_id', $request->users_id)
                ->where('bengkels_id', $request->bengkels_id)
                ->delete();

            return response()->json([
                'status' => true,
                'message' => 'Bengkel berhasil dihapus dari favorit',
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Bengkel belum ditambahkan ke favorit',
            ], 400);
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
