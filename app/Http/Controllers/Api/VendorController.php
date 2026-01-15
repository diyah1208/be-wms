<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VendorModel;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    // READ - list
    public function index()
    {
        return response()->json([
            'data' => VendorModel::orderBy('vendor_name')->get()
        ]);
    }

    // READ - detail
    public function show($id)
    {
        return response()->json(
            VendorModel::findOrFail($id)
        );
    }

    // CREATE
public function store(Request $request)
{
    $request->validate([
        'vendor_no'   => 'required|unique:vendors,vendor_no',
        'vendor_name' => 'required|unique:vendors,vendor_name',
        'telephone'   => 'nullable|numeric|digits_between:1,13',
    ], [
        'vendor_no.unique'     => 'No Vendor sudah digunakan',
        'vendor_name.unique'   => 'Nama Vendor sudah digunakan',
        'telephone.numeric'    => 'Nomor telepon hanya boleh angka',
        'telephone.digits_between' => 'Nomor telepon maksimal 13 digit',
    ]);

    $vendor = VendorModel::create($request->all());

    return response()->json([
        'message' => 'Vendor berhasil dibuat',
        'data' => $vendor
    ], 201);
}


    // UPDATE
public function update(Request $request, $id)
{
    $vendor = VendorModel::findOrFail($id);

    $request->validate([
        'vendor_name' => 'required|unique:vendors,vendor_name,' . $vendor->id,
        'telephone'   => 'nullable|numeric|digits_between:1,13',
        'contact_name'=> 'nullable|string',
    ], [
        'vendor_name.unique'   => 'Nama Vendor sudah digunakan',
        'telephone.numeric'    => 'Nomor telepon hanya boleh angka',
        'telephone.digits_between' => 'Nomor telepon maksimal 13 digit',
    ]);

    $vendor->update([
        'vendor_name'  => $request->vendor_name,
        'telephone'    => $request->telephone,
        'contact_name' => $request->contact_name,
    ]);

    return response()->json([
        'message' => 'Vendor berhasil diperbarui',
        'data' => $vendor
    ]);
}


    // DELETE (soft logic recommended)
    public function destroy($id)
    {
        VendorModel::findOrFail($id)->delete();

        return response()->json([
            'message' => 'Vendor deleted'
        ]);
    }

    // SUSPEND / ACTIVATE
    public function toggleStatus($id)
    {
        $vendor = VendorModel::findOrFail($id);
        $vendor->update([
            'is_active' => !$vendor->is_active
        ]);

        return response()->json($vendor);
    }
}
