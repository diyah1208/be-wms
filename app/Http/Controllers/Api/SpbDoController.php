<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SpbModel;
use App\Models\SpbDoMOdel;
use Illuminate\Http\Request;

class SpbDoController extends Controller
{
    public function index()
    {
        $data = SpbDoModel::with('spb')
            ->orderByDesc('spb_do_id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'do_no' => 'required',
            'do_date' => 'required',
        ]);

        $do = SpbDoModel::create([
            'spb_id' => $request->spb_id,
            'do_no' => $request->do_no,
            'do_date' => $request->do_date,
            'do_status_part' => $request->do_status_part,
        ]);

        SpbModel::where('spb_id', $request->spb_id)
            ->update(['spb_status' => 'DELIVERED']);

        return response()->json($do, 201);
    }
}
