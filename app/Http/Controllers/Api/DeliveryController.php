<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryModel;
use App\Models\DeliveryDetailModel;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use App\Models\StockModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeliveryListExport;

class DeliveryController extends Controller
{
    public function index()
    {
        return response()->json(
            DeliveryModel::with(['details', 'mr.details'])
                ->orderBy('dlv_kode', 'desc')
                ->get()
        );
    }

    public function showKode($kode)
    {
        return response()->json(
            DeliveryModel::with(['details', 'mr.details'])
                ->where('dlv_kode', $kode)
                ->firstOrFail()
        );
    }


    public function store(Request $request)
    {
        $request->validate([
            'dlv_kode'        => 'required|unique:tb_delivery,dlv_kode',
            'mr_id'           => 'required',
            'dlv_dari_gudang' => 'required',
            'dlv_ke_gudang'   => 'required',
            'dlv_pic'         => 'required',
            'details'         => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request) {

            $delivery = DeliveryModel::create([
                'dlv_kode'        => $request->dlv_kode,
                'mr_id'           => $request->mr_id,
                'dlv_dari_gudang' => $request->dlv_dari_gudang,
                'dlv_ke_gudang'   => $request->dlv_ke_gudang,
                'dlv_ekspedisi'   => $request->dlv_ekspedisi,
                'dlv_no_resi'     => $request->dlv_no_resi,
                'dlv_jumlah_koli' => $request->dlv_jumlah_koli ?? 0,
                'dlv_pic'         => $request->dlv_pic,
                'dlv_status'      => 'pending',
            ]);

            foreach ($request->details as $item) {
                DeliveryDetailModel::create([
                    'dlv_id'              => $delivery->dlv_id,
                    'part_id'             => $item['part_id'],
                    'dtl_dlv_part_number' => $item['dtl_dlv_part_number'],
                    'dtl_dlv_part_name'   => $item['dtl_dlv_part_name'],
                    'dtl_dlv_satuan'      => $item['dtl_dlv_satuan'],
                    'qty_pending'         => $item['qty_pending'],
                    'qty_on_delivery'     => 0,
                    'qty_delivered'       => 0,
                    'receive_note'        => null,
                ]);
            }
        });

        return response()->json(['message' => 'Delivery created']);
    }

    public function updateStatus(Request $request, $kode)
    {
        $delivery = DeliveryModel::with('details')
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        $request->validate([
            'status' => 'required|in:packing,ready to pickup,on delivery',
        ]);

        $allowed = [
            'pending'          => ['packing'],
            'packing'          => ['ready to pickup'],
            'ready to pickup'  => ['on delivery'],
        ];

        if (!in_array($request->status, $allowed[$delivery->dlv_status] ?? [])) {
            throw new Exception('Perubahan status tidak valid');
        }

        if ($request->status === 'packing') {
            $this->moveToPacking($delivery);

            $delivery->update([
                'packing_at' => now(),
                'packing_by' => auth()->user()->name ?? $delivery->dlv_pic,
            ]);
        }

        if ($request->status === 'on delivery') {
            $delivery->update([
                'on_delivery_at' => now(),
            ]);
        }

        $delivery->update([
            'dlv_status' => $request->status,
        ]);

        return response()->json([
            'message' => 'Status updated',
            'status'  => $request->status,
        ]);
    }


    private function moveToPacking($delivery)
    {
        DB::transaction(function () use ($delivery) {
            foreach ($delivery->details as $item) {

                if ($item->qty_pending <= 0) continue;

                $origin = StockModel::where('part_id', $item->part_id)
                    ->where('stk_location', $delivery->dlv_dari_gudang)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($origin->stk_qty < $item->qty_pending) {
                    throw new Exception(
                        "Stok kurang untuk part {$item->dtl_dlv_part_number}"
                    );
                }

                $origin->decrement('stk_qty', $item->qty_pending);

                $item->update([
                    'qty_on_delivery' => $item->qty_pending,
                    'qty_pending'     => 0,
                ]);
            }
        });
    }

    public function receive(Request $request, $kode)
    {
        $delivery = DeliveryModel::with(['details', 'mr.details'])
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        if ($delivery->dlv_status !== 'on delivery') {
            throw new Exception('Delivery belum dalam proses pengiriman');
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.part_id' => 'required|integer',
            'items.*.qty_received' => 'required|integer|min:0',
            'items.*.receive_note' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $delivery) {

            foreach ($request->items as $input) {

                $detail = DeliveryDetailModel::where('dlv_id', $delivery->dlv_id)
                    ->where('part_id', $input['part_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($input['qty_received'] > $detail->qty_on_delivery) {
                    throw new Exception(
                        "Qty diterima melebihi qty dikirim ({$detail->dtl_dlv_part_number})"
                    );
                }

                if (
                    $input['qty_received'] < $detail->qty_on_delivery &&
                    empty($input['receive_note'])
                ) {
                    throw new Exception(
                        "Keterangan wajib diisi untuk penerimaan sebagian ({$detail->dtl_dlv_part_number})"
                    );
                }

                $detail->update([
                    'qty_delivered'   => $input['qty_received'],
                    'qty_pending'     => $detail->qty_on_delivery - $input['qty_received'],
                    'qty_on_delivery' => 0,
                    'receive_note'    => $input['receive_note'],
                ]);

                $stock = StockModel::firstOrCreate(
                    [
                        'part_id'      => $detail->part_id,
                        'stk_location' => $delivery->dlv_ke_gudang,
                    ],
                    ['stk_qty' => 0]
                );

                $stock->increment('stk_qty', $input['qty_received']);

                MaterialRequestItemModel::where('mr_id', $delivery->mr_id)
                    ->where('part_id', $detail->part_id)
                    ->increment('dtl_mr_qty_received', $input['qty_received']);
            }

            // ✅ UPDATE STATUS DELIVERY DI SINI
            $delivery->update([
                'dlv_status' => 'delivered',
            ]);
        });

        return response()->json([
            'message' => 'Barang diterima, silakan TTD untuk menyelesaikan delivery',
        ]);
    }

    public function signPenerima(Request $request, $kode)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        $delivery = DeliveryModel::with('details')
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        if ($delivery->dlv_status !== 'delivered') {
            throw new Exception('Belum bisa TTD penerima');
        }

        $path = $this->saveSignature($request->signature);

        $delivery->update([
            'signed_penerima_sign' => $path,
            'signed_penerima_at'   => now(),
            'delivered_at'         => now(),
        ]);

        return response()->json([
            'message' => 'Delivery selesai',
        ]);
    }

    private function saveSignature(string $base64): string
    {
        $clean = preg_replace('#^data:image/\w+;base64,#i', '', $base64);
        $clean = str_replace(' ', '+', $clean);

        $image = base64_decode($clean);

        if ($image === false) {
            throw new Exception('Signature tidak valid');
        }

        $filename = 'penerima_' . uniqid() . '.png';
        $path = 'signatures/' . $filename;

        Storage::disk('public')->put($path, $image);

        return $path;
    }

    public function exportPdf($kode)
    {
        $delivery = DeliveryModel::with(['details', 'mr.details'])
            ->where('dlv_kode', $kode)
            ->firstOrFail();

        $pdf = Pdf::loadView(
            'exports.delivery-pdf',
            compact('delivery')
        )->setPaper('A4', 'portrait');

        return $pdf->download(
            'DELIVERY_' . $delivery->dlv_kode . '.pdf'
        );
    }

    public function exportDeliveryHeader()
    {
        $deliveries = DeliveryModel::with('mr')->get();

        return Excel::download(
            new DeliveryListExport($deliveries),
            'DAFTAR_DELIVERY.xlsx'
        );
    }
}
