<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /* ================= SUMMARY ================= */
        $summary = [
            'total_stock'       => DB::table('tb_stock')->sum('stk_qty'),
            'stock_min_warning' => DB::table('tb_stock')
                ->whereColumn('stk_qty', '<=', 'stk_min')
                ->count(),

            'total_mr'          => DB::table('tb_material_request')->count(),
            'mr_open'           => DB::table('tb_material_request')
                ->where('mr_status', 'open')
                ->count(),

            'total_po_open'     => DB::table('tb_purchase_order')
                ->where('po_status', 'open')
                ->count(),

            'total_delivery'    => DB::table('tb_delivery')->count(),
            'total_receive'     => DB::table('tb_receive_item')->count(),
        ];

        /* ================= STOCK WARNING ================= */
        $stockWarning = DB::table('tb_stock')
            ->join('tb_barang', 'tb_barang.part_id', '=', 'tb_stock.part_id')
            ->whereColumn('stk_qty', '<=', 'stk_min')
            ->select(
                'tb_barang.part_number',
                'tb_barang.part_name',
                'tb_stock.stk_qty',
                'tb_stock.stk_min',
                'tb_stock.stk_location'
            )
            ->orderBy('tb_stock.stk_qty')
            ->limit(5)
            ->get();

        /* ================= LATEST MR ================= */
        $latestMR = DB::table('tb_material_request')
            ->select(
                'mr_kode',
                'mr_lokasi',
                'mr_status',
                DB::raw('DATE(created_at) as tanggal')
            )
            ->orderByDesc('created_at')
            // ->limit(5)
            ->get();

        /* ================= LATEST DELIVERY ================= */
        $latestDelivery = DB::table('tb_delivery')
            ->select(
                'dlv_kode',
                'dlv_dari_gudang',
                'dlv_ke_gudang',
                'dlv_status',
                DB::raw('DATE(created_at) as tanggal')
            )
            ->orderByDesc('created_at')
            // ->limit(5)
            ->get();

        /* ================= LATEST RECEIVE ================= */
        $latestReceive = DB::table('tb_receive_item')
            ->select(
                'ri_kode',
                'ri_lokasi',
                DB::raw('DATE(created_at) as tanggal')
            )
            ->orderByDesc('created_at')
            // ->limit(5)
            ->get();

        return response()->json([
            'status'  => true,
            'summary' => $summary,
            'details' => [
                'stock_warning'   => $stockWarning,
                'latest_mr'       => $latestMR,
                'latest_delivery' => $latestDelivery,
                'latest_receive'  => $latestReceive,
            ],
        ]);
    }
}
