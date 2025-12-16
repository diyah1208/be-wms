<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BarangModel;

class BarangController extends Controller
{
    
    public function index()
    {
        return response()->json([
            'status' => true,
            'data' => BarangModel::orderBy('part_name')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'part_number' => 'required|unique:tb_barang,part_number',
            'part_name'   => 'required',
            'part_satuan' => 'required',
        ]);

        $barang = BarangModel::create($request->only([
            'part_number',
            'part_name',
            'part_satuan',
        ]));

         return response()->json([
            'status' => true,
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang
        ], 201);
    }

    public function show($id)
    {
        $barang = BarangModel::find($id);

        if (!$barang) {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $barang
        ]);
    }

    public function update(Request $request, $id)
    {
        $barang = BarangModel::find($id);

        if(!$barang)
        {
            return response()->json([
                'status' => false,
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }
        $request->validate([
            'part_number' => 'required|unique:tb_barang,part_number,' . $id . ',part_id',
            'part_name'   => 'required',
            'part_satuan' => 'required',
        ]);

        
        $barang->update($request->only([
            'part_number',
            'part_name',
            'part_satuan',
        ]));

         return response()->json([
            'status' => true,
            'message' => 'Barang berhasil diupdate',
            'data' => $barang
        ]);
    }
    
}
