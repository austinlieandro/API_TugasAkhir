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
}
