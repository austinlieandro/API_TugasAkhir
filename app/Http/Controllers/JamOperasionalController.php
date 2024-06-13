<?php

namespace App\Http\Controllers;

use App\Models\JamOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JamOperasionalController extends Controller
{
    public function inputJam(request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_operasional' => 'required',
            'slot' => 'required',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::create([
            'jam_operasional' => $request->jam_operasional,
            'slot' => $request->slot,
            'bengkels_id' => (int)$request->bengkels_id,
        ]);

        if ($jamOperasional) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan jam operasional',
                'jamOperasional' => $jamOperasional,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'gagal menambahkan jam operasional',
        ], 409);
    }

    public function updateJamOperasional(request $request, $bengkels_id, $jam_id){
        $validator = Validator::make($request->all(), [
            'jam_operasional' => ' required',
            'slot' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::where('id', $jam_id)->where('bengkels_id', $bengkels_id)->first();
        if (!$jamOperasional){
            return response()->json([
                'status' => false,
                'message' => 'Jam operasional tidak ditemukan'
            ], 404);
        }

        $jamOperasional->jam_operasional = $request->jam_operasional;
        $jamOperasional->slot = $request->slot;
        $jamOperasional->save();

        return response()->json([
            'status' => true,
            'message' => 'Jam operasional berhasil diperbarui',
            'jamOperasional' => $jamOperasional,
        ], 200);
    }
}
