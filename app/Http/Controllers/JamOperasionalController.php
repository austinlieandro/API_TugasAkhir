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
            'jam_operasional' => 'required|array',
            'hari_operasional' => 'required|array',
            'slot' => 'required|array',
            'bengkels_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::create([
            'jam_operasional' => $request->jam_operasional,
            'hari_operasional' => $request->hari_operasional,
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

    public function updateJamOperasional(Request $request, $bengkels_id, $id)
    {
        $validator = Validator::make($request->all(), [
            'jam_operasional' => 'required|string',
            'hari_operasional' => 'required|string',
            'slot' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::where('id', $id)
            ->where('bengkels_id', $bengkels_id)
            ->first();

        if (!$jamOperasional) {
            return response()->json(['message' => 'Jam Operasional tidak ditemukan'], 404);
        }

        $jamOperasional->update([
            'jam_operasional' => $request->jam_operasional,
            'hari_operasional' => $request->hari_operasional,
            'slot' => $request->slot,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil memperbarui jam operasional',
            'jamOperasional' => $jamOperasional,
        ], 200);
    }
}
