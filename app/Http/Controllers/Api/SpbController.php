<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryModel;
use App\Models\DeliveryDetailModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use App\Models\StockModel;
use App\Models\BarangModel;
use Exception;
use App\Models\SpbModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DeliveryListExport;

use Carbon\Carbon;

class SpbController extends Controller
{
    public function index()
    {
        return response()->json(
            SpbModel::get()
        );
    }

     public function view(Request $request)
    {
        $limit  = $request->get('limit', 10);
        $search = $request->get('search');

        $query = DB::table('v_spb_report');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('spb_no', 'like', "%$search%")
                  ->orWhere('spb_part_name', 'like', "%$search%")
                  ->orWhere('spb_part_number', 'like', "%$search%")
                  ->orWhere('po_no', 'like', "%$search%")
                  ->orWhere('do_no', 'like', "%$search%")
                  ->orWhere('invoice_no', 'like', "%$search%");
            });
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')
                  ->paginate($limit)
        );
    }

     public function store(Request $request)
    {
        $request->validate([
            'spb_tanggal' => 'required|date',
            'spb_no' => 'required',
            'part_id' => 'required|exists:tb_barang,part_id',
            'spb_qty' => 'required|integer|min:1'
        ]);

        $part = BarangModel::findOrFail($request->part_id);

        $spb = SpbModel::create([
            'spb_tanggal' => $request->spb_tanggal,
            'spb_no' => $request->spb_no,
            'spb_no_wo' => $request->spb_no_wo,
            'spb_section' => $request->spb_section,
            'spb_pic_gmi' => $request->spb_pic_gmi,
            'spb_pic_ppa' => $request->spb_pic_ppa,
            'spb_kode_unit' => $request->spb_kode_unit,
            'spb_tipe_unit' => $request->spb_tipe_unit,
            'spb_brand' => $request->spb_brand,
            'spb_hm' => $request->spb_hm,
            'spb_problem_remark' => $request->spb_problem_remark,

            'part_id' => $part->part_id,
            'spb_part_number' => $part->part_number,
            'spb_part_name' => $part->part_name,
            'spb_qty' => $request->spb_qty,
            'spb_uom' => $part->part_satuan,

            'spb_status' => 'CREATED'
        ]);

        return response()->json($spb, 201);
    }

    public function generateKodeSpb()
{
    $lokasiKode = 'TJE'; // nanti bisa ambil dari user
    $tahun = now()->format('Y'); // 2026
    $bulan = now()->format('m'); // 01

    // ambil spb_id terakhir
    $lastId = SpbModel::max('spb_id'); // null kalau belum ada

    $nextNumber = $lastId ? $lastId + 1 : 1;

    $kode = "GMITJIE/{$tahun}/{$bulan}/{$nextNumber}";

    return response()->json($kode);
}

}
