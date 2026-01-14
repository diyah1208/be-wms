<?php

// app/Http/Controllers/Api/SpbPoController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpbModel;
use App\Models\SpbPoModel;
use Illuminate\Http\Request;

class SpbPoController extends Controller
{
    public function index()
    {
        $data = SpbPoModel::with('spb')
            ->orderByDesc('spb_po_id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_no' => 'required',
            'so_no' => 'nullable',
            'so_date' => 'nullable|date',
        ]);

        $po = SpbPoModel::create([
            'spb_id' => $request->spb_id,
            'po_no' => $request->po_no,
            'so_no' => $request->so_no,
            'so_date' => $request->so_date,
        ]);

        // UPDATE STATUS SPB
        SpbModel::where('spb_id', $request->spb_id)
            ->update(['spb_status' => 'PO_ATTACHED']);

        return response()->json($po, 201);
    }
}
