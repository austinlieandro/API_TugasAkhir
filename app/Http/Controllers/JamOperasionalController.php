<?php

namespace App\Http\Controllers;

use App\Models\Bengkels;
use App\Models\JamOperasional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class JamOperasionalController extends Controller
{
    public function inputJam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_operasional' => 'required|array',
            'hari_operasional' => 'required|array',
            'slot' => 'required|array',
            'bengkels_id' => 'required|exists:bengkels,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jam_operasional = $request->jam_operasional;
        $hari_operasional = $request->hari_operasional;
        $slot = $request->slot;
        $bengkels_id = (int)$request->bengkels_id;

        $jamOperasionalData = [];
        foreach ($jam_operasional as $index => $jam) {
            $jamOperasionalData[] = [
                'jam_operasional' => $jam,
                'hari_operasional' => $hari_operasional[$index],
                'slot' => $slot[$index],
                'bengkels_id' => $bengkels_id,
            ];
        }

        $jamOperasional = JamOperasional::insert($jamOperasionalData);

        if ($jamOperasional) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambahkan jam operasional',
                'jamOperasional' => $jamOperasionalData,
            ], 201);
        }

        return response()->json([
            'status' => false,
            'message' => 'Gagal menambahkan jam operasional',
        ], 409);
    }

    public function inputJamSatu(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jam_operasional' => 'required',
            'hari_operasional' => 'required',
            'slot' => 'required|integer',
            'bengkels_id' => 'required|exists:bengkels,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::create([
            'jam_operasional' => $request->jam_operasional,
            'hari_operasional' => $request->hari_operasional,
            'slot' => (int)$request->slot,
            'bengkels_id' => $request->bengkels_id
        ]);

        if ($jamOperasional) {
            return response()->json([
                'status' => true,
                'message' => 'Berhasil menambah jam operasional',
                'jamOperasional' => $jamOperasional
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => 'Gagal menambah jam operasional',
        ], 409);
    }

    public function updateJamOperasional(Request $request, $bengkelId, $id)
    {
        $validator = Validator::make($request->all(), [
            'jam_operasional' => 'required|string',
            'slot' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $jamOperasional = JamOperasional::where('id', $id)
            ->where('bengkels_id', $bengkelId)
            ->first();

        if (!$jamOperasional) {
            return response()->json(['message' => 'Jam Operasional tidak ditemukan'], 404);
        }

        $jamOperasional->update([
            'jam_operasional' => $request->jam_operasional,
            'slot' => (int)$request->slot,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Berhasil memperbarui jam operasional',
            'jamOperasional' => $jamOperasional,
        ], 200);
    }
}
