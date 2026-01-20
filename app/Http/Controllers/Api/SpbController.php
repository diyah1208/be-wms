<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryModel;
use App\Models\DeliveryDetailModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use App\Models\StockModel;
use App\Models\SpbDetailModel;
use App\Models\BarangModel;
use Exception;
use App\Models\SpbModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DeliveryListExport;
use App\Exports\SpbListExport;

use Carbon\Carbon;

class SpbController extends Controller
{
    public function index()
    {
        return response()->json(
            SpbModel::get()
        );
    }

    public function showKode($kode)
    {
        return response()->json(
            SpbModel::with(['details'])
                ->where('spb_no', $kode)
                ->firstOrFail()
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
            'spb_no'      => 'required',
            'spb_gudang' => 'required|string|max:50',
            'details'               => 'required|array|min:1',
            'details.*.part_id'     => 'required|exists:tb_barang,part_id',
            'details.*.dtl_spb_qty' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request, &$spb) {

            $spb = SpbModel::create([
                'spb_tanggal'        => $request->spb_tanggal,
                'spb_no'             => $request->spb_no,
                'spb_no_wo'          => $request->spb_no_wo,
                'spb_section'        => $request->spb_section,
                'spb_pic_gmi'        => $request->spb_pic_gmi,
                'spb_pic_ppa'        => $request->spb_pic_ppa,
                'spb_kode_unit'      => $request->spb_kode_unit,
                'spb_tipe_unit'      => $request->spb_tipe_unit,
                'spb_brand'          => $request->spb_brand,
                'spb_hm'             => $request->spb_hm,
                'spb_problem_remark' => $request->spb_problem_remark,
                'spb_status'         => 'DONE QUOT',
                'spb_gudang'  => $request->spb_gudang,
                'spb_pic'  => $request->spb_pic,
            ]);
            foreach ($request->details as $item) {

                $part = BarangModel::findOrFail($item['part_id']);
                $stock = StockModel::where('part_id', $part->part_id)
                ->where('stk_location', $request->spb_gudang)
                ->lockForUpdate()
                ->first();

                if (!$stock || $stock->stk_qty < $item['dtl_spb_qty']) {
                    throw new \Exception(
                        "Stok {$part->part_number} tidak mencukupi"
                    );
                }

                $stock->decrement('stk_qty', $item['dtl_spb_qty']);

                SpbDetailModel::create([
                    'spb_id'               => $spb->spb_id,
                    'part_id'              => $part->part_id,
                    'dtl_spb_part_number'  => $part->part_number,
                    'dtl_spb_part_name'    => $part->part_name,
                    'dtl_spb_part_satuan'  => $part->part_satuan,
                    'dtl_spb_qty'          => $item['dtl_spb_qty']?? 0,
                ]);
            }
        });

        return response()->json([
            'status' => true,
            'message' => 'SPB berhasil dibuat',
            'data' => $spb->load('details')
        ], 201);
    }


    public function generateKodeSpb()
    {
        $lokasiKode = 'TJE'; 
        $tahun = now()->format('Y'); 
        $bulan = now()->format('m'); 

        $lastId = SpbModel::max('spb_id');

        $nextNumber = $lastId ? $lastId + 1 : 1;

        $kode = "GMITJIE/{$tahun}/{$bulan}/{$nextNumber}";

        return response()->json($kode);
    }

    public function exportSpbExcel()
    {
        $data = DB::table('v_spb_report')
            ->orderBy('created_at', 'desc')
            ->get();

        return Excel::download(
            new SpbListExport($data),
            'DAFTAR_SPB.xlsx'
        );
    }

    public function printSpb($kode)
    {
        $spb = SpbModel::with('details')
            ->where('spb_no', $kode)
            ->firstOrFail();

        $pdf = Pdf::loadView('exports.spb-pdf', [
            'spb' => $spb
        ])->setPaper('A4', 'potrait');

        $filename = 'SPB-' . str_replace('/', '-', $spb->spb_no) . '.pdf';

        return $pdf->stream($filename);
    }
}
