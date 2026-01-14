<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReceiveModel;
use App\Models\ReceiveDetailModel;
use App\Models\PurchaseOrderModel;
use App\Models\StockModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReceiveListExport;

class ReceiveController extends Controller
{

    public function getPoPurchased(Request $request)
    {
        $data = PurchaseOrderModel::with([
                'details',          
                'purchaseRequest.details.mr'  
            ])
            ->where('po_status', 'purchased')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }
    public function index()
    {
        $data = ReceiveModel::with([
            'purchaseOrder',
            'details'
        ])
        ->orderBy('ri_tanggal', 'desc')
        ->get();

        return response()->json($data);
    }

    public function showByKode($kode)
    {
        $receive = ReceiveModel::with([
            'purchaseOrder',
            'details',
        ])
        ->where('ri_kode', $kode)
        ->firstOrFail();

        return response()->json($receive);
    }

    public function store(Request $request)
    {
        $request->validate([
            "ri_kode" => "required|unique:tb_receive_item,ri_kode",
            "po_id"   => "required|exists:tb_purchase_order,po_id",
            "ri_lokasi" => "required",
            "ri_tanggal" => "required|date",
            "details" => "required|array|min:1",

            "details.*.part_id" => "required|exists:tb_barang,part_id",
            "details.*.mr_id"   => "required|exists:tb_material_request,mr_id",
            "details.*.dtl_ri_part_number" => "required",
            "details.*.dtl_ri_part_name" => "required",
            "details.*.dtl_ri_satuan" => "required",
            "details.*.dtl_ri_qty" => "required|integer|min:1",
        ]);

        $receive = null;

        DB::transaction(function () use ($request, &$receive) {
            $receive = ReceiveModel::create([
                "ri_kode"       => $request->ri_kode,
                "po_id"         => $request->po_id,
                "ri_lokasi"     => $request->ri_lokasi,
                "ri_tanggal"    => $request->ri_tanggal,
                "ri_keterangan" => $request->ri_keterangan,
                "ri_pic"        => $request->ri_pic ?? auth()->user()->name,
                "ri_status"     => "confirmed"
            ]);

            foreach ($request->details as $item) {
                $poDetail = DB::table('dtl_purchase_order')
                    ->where('po_id', $request->po_id)
                    ->where('part_id', $item['part_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$poDetail) {
                    throw new \Exception("Detail PO tidak ditemukan");
                }

                $sisaPo = $poDetail->qty_order - $poDetail->qty_received;

                if ($item['dtl_ri_qty'] > $sisaPo) {
                    throw new \Exception(
                        "Qty receive melebihi sisa PO untuk part {$item['dtl_ri_part_number']}"
                    );
                }

                ReceiveDetailModel::create([
                    "ri_id" => $receive->ri_id,
                    "mr_id" => $item['mr_id'],
                    "part_id" => $item['part_id'],
                    "dtl_ri_part_number" => $item['dtl_ri_part_number'],
                    "dtl_ri_part_name" => $item['dtl_ri_part_name'],
                    "dtl_ri_satuan" => $item['dtl_ri_satuan'],
                    "dtl_ri_qty" => $item['dtl_ri_qty'],
                ]);

                DB::table('dtl_purchase_order')
                    ->where('po_id', $request->po_id)
                    ->where('part_id', $item['part_id'])
                    ->update([
                        'qty_received' => $poDetail->qty_received + $item['dtl_ri_qty']
                    ]);

                $stock = StockModel::firstOrCreate(
                    [
                        "part_id" => $item['part_id'],
                        "stk_location" => $request->ri_lokasi
                    ],
                    [
                        "stk_qty" => 0,
                        "stk_min" => 0,
                        "stk_max" => 0
                    ]
                );
                $stock->increment("stk_qty", $item['dtl_ri_qty']);

                $mrDetail = MaterialRequestItemModel::where("mr_id", $item["mr_id"])
                    ->where("part_id", $item["part_id"])
                    ->lockForUpdate()
                    ->firstOrFail();

                $sisaMr = $mrDetail->dtl_mr_qty_request - $mrDetail->dtl_mr_qty_received;
                $qtyMasukMr = min($item['dtl_ri_qty'], $sisaMr);

                $mrDetail->increment("dtl_mr_qty_received", $qtyMasukMr);
            }

            $mrIds = collect($request->details)->pluck('mr_id')->unique();
            foreach ($mrIds as $mrId) {
                $mr = MaterialRequestModel::with('details')->find($mrId);
                $mr->update([
                    "mr_status" => $mr->details->every(
                        fn ($d) => $d->dtl_mr_qty_received >= $d->dtl_mr_qty_request
                    ) ? "close" : "open"
                ]);
            }

            $poHasSisa = DB::table('dtl_purchase_order')
                ->where('po_id', $request->po_id)
                ->whereRaw('qty_received < qty_order')
                ->exists();

            PurchaseOrderModel::where("po_id", $request->po_id)->update([
                "po_status" => $poHasSisa ? "partial_received" : "received"
            ]);
        });

        return response()->json([
            "status" => true,
            "message" => "Receive item berhasil dibuat",
            "ri_id" => $receive->ri_id,
            "ri_kode" => $receive->ri_kode
        ]);
    }

    public function exportReceive()
    {
        $deliveries = ReceiveModel::with('purchaseOrder')->get();

        return Excel::download(
            new ReceiveListExport($deliveries), 
            'DAFTAR_RECEIVE.xlsx'
        );
    }

}
