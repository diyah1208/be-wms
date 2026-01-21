<?php



namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // LIST
    public function index()
    {
        return response()->json([
            'data' => Customer::orderBy('customer_name')->get()
        ]);
    }

    // CREATE
    public function store(Request $request)
    {
        $request->validate([
            'customer_no'   => 'required|unique:customers,customer_no',
            'customer_name' => 'required|unique:customers,customer_name',
            'telephone'     => 'nullable|numeric|digits_between:1,13',
        ], [
            'customer_no.unique'   => 'No Customer sudah digunakan',
            'customer_name.unique' => 'Nama Customer sudah digunakan',
            'telephone.numeric'    => 'Nomor telepon hanya boleh angka',
            'telephone.digits_between' => 'Nomor telepon maksimal 13 digit',
        ]);

        $customer = Customer::create($request->all());

        return response()->json([
            'message' => 'Customer berhasil dibuat',
            'data' => $customer
        ], 201);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|unique:customers,customer_name,' . $customer->id,
            'telephone'     => 'nullable|numeric|digits_between:1,13',
            'contact_name'  => 'nullable|string',
        ]);

        $customer->update([
            'customer_name' => $request->customer_name,
            'telephone'     => $request->telephone,
            'contact_name'  => $request->contact_name,
        ]);

        return response()->json([
            'message' => 'Customer berhasil diperbarui',
            'data' => $customer
        ]);
    }

    // TOGGLE STATUS
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->update([
            'is_active' => !$customer->is_active
        ]);

        return response()->json($customer);
    }
}
