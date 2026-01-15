<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrderModel;
use App\Models\PurchaseOrderDetailModel;
use App\Models\PurchaseRequestModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class PurchaseOrderController extends Controller
{
    public function getPrOpen(Request $request)
    {
        $data = PurchaseRequestModel::with([
                'details',
                'details.mr',
            ])
            ->where('pr_status', 'open')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function index()
    {
        $pos = PurchaseOrderModel::with('purchaseRequest')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(
            $pos->map(fn ($po) => [
                'id' => $po->po_id,
                'kode' => $po->po_kode,
                'kode_pr' => $po->purchaseRequest->pr_kode ?? null,
                'tanggal' => $po->po_tanggal,
                'tanggal_estimasi' => $po->po_estimasi,
                'status' => strtolower($po->po_status),
                'detail_status' => $po->po_detail_status, 
                'pic' => $po->po_pic,
                'keterangan' => $po->po_keterangan,
                'created_at' => $po->created_at?->toDateTimeString(),
                'updated_at' => $po->updated_at?->toDateTimeString(),
            ])
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'po_kode'        => 'required|unique:tb_purchase_order,po_kode',
            'pr_id'          => 'required|exists:tb_purchase_request,pr_id',
            'po_tanggal'     => 'required|date',
            'po_estimasi'    => 'nullable|date',
            'po_keterangan'  => 'nullable|string',
            'po_pic'         => 'required|string',
            'po_status'      => 'required|in:pending,purchased',
            'details'        => 'required|array',
            'details.*.part_id' => 'required',
            'details.*.dtl_po_qty' => 'required|numeric|min:1',
            'details.*.dtl_po_harga' => 'required|numeric|min:1',
'details.*.vendor_id'   => 'required|exists:vendors,id',

        ]);

        DB::transaction(function () use ($request) {

            $pr = PurchaseRequestModel::lockForUpdate()
                ->where('pr_id', $request->pr_id)
                ->firstOrFail();
foreach ($request->details as $item) {
    if ($item['dtl_po_qty'] <= 0) {
        abort(422, 'Qty PO harus lebih dari 0');
    }
}

            $po = PurchaseOrderModel::create([
                'po_kode' => $request->po_kode,
                'pr_id' => $request->pr_id,
                'po_tanggal' => $request->po_tanggal,
                'po_estimasi' => $request->po_estimasi,
                'po_status' => $request->po_status,
                'po_detail_status' =>  $request->po_detail_status,
                'po_keterangan' => $request->po_keterangan,
                'po_pic' => $request->po_pic,
                
            ]);

            foreach ($request->details as $item) {
                PurchaseOrderDetailModel::create([
                    'po_id'              => $po->po_id,
                    'part_id'            => $item['part_id'],
                    'dtl_po_part_number' => $item['dtl_po_part_number'],
                    'dtl_po_part_name'   => $item['dtl_po_part_name'],
                    'dtl_po_satuan'      => $item['dtl_po_satuan'],
                    'dtl_po_qty'         => $item['dtl_po_qty'],
                     'dtl_po_harga'       => $item['dtl_po_harga'] ?? null, // 🔥
    'vendor_id'          => $item['vendor_id'] ?? null,    // 🔥
                    'dtl_qty_received'   => 0,
                ]);
            }

            if ($request->po_status === 'purchased') {
                $pr->update(['pr_status' => 'closed']);
            }
        });

        return response()->json([
            'message' => 'Purchase Order berhasil dibuat'
        ], 201);
    }

 public function showKode($kode)
{
    $po = PurchaseOrderModel::with([
        'purchaseRequest',
        'details.vendor',
    ])
    ->where('po_kode', $kode)
    ->firstOrFail();

    return response()->json($po);
}


    public function show($id)
    {
        $po = PurchaseOrderModel::with(['purchaseRequest', 'details'])
            ->findOrFail($id);

        return response()->json([
            'id' => $po->po_id,
            'kode' => $po->po_kode,
            'kode_pr' => $po->purchaseRequest->pr_kode ?? null,
            'tanggal' => $po->po_tanggal,
            'tanggal_estimasi' => $po->po_estimasi,
            'status' => strtolower($po->po_status),
            'po_detail_status' => $po->po_detail_status, 
            'pic' => $po->po_pic,
            'keterangan' => $po->po_keterangan,
            'created_at' => $po->created_at?->toDateTimeString(),
            'updated_at' => $po->updated_at?->toDateTimeString(),
            'details' => $po->details->map(fn ($d) => [
                'po_detail_id' => $d->dtl_po_id,
                'part_id' => $d->part_id,
                'part_number' => $d->dtl_po_part_number,
                'part_name' => $d->dtl_po_part_name,
                'satuan' => $d->dtl_po_satuan,
                'qty_order' => $d->dtl_po_qty,
                'qty_received' => $d->dtl_qty_received,
            ]),
        ]);
    }

    public function update(Request $request, $id)
    {
        $po = PurchaseOrderModel::lockForUpdate()->findOrFail($id);

        if ($po->po_status !== 'pending') {
            return response()->json([
                'message' => 'PO tidak dapat diedit karena status sudah ' . $po->po_status
            ], 403);
        }

        $data = $request->validate([
            'po_detail_status' => 'required|string|max:50', 
            'po_keterangan' => 'nullable|string',
            'po_estimasi' => 'nullable|date',
        ]);

        DB::transaction(function () use ($po, $data) {
            $po->update([
                'po_detail_status' => $data['po_detail_status'],
                'po_keterangan' => $data['po_keterangan'] ?? $po->po_keterangan,
                'po_estimasi' => $data['po_estimasi'] ?? $po->po_estimasi,
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Sub status PO berhasil diupdate',
            'data' => [
                'id' => $po->po_id,
                'kode' => $po->po_kode,
                'status' => $po->po_status,
                'detail_status' => $po->po_detail_status,
            ]
        ]);
    }

    public function destroy($id)
    {
        $po = PurchaseOrderModel::findOrFail($id);
        $po->delete();

        return response()->json([
            'status' => true,
            'message' => 'Purchase Order berhasil dihapus'
        ]);
    }

        public function sign(Request $request): JsonResponse
{
    try {
        $request->validate([
            'kode' => 'required|string',
            'signature' => 'required|string',
        ]);

        $po = PurchaseOrderModel::where('po_kode', $request->kode)->first();

        if (!$po) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak ditemukan'
            ], 404);
        }

        if (!empty($po->signature_url)) {
            $oldPath = str_replace('/storage/', '', $po->signature_url);
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $signatureData = preg_replace(
            '#^data:image/\w+;base64,#i',
            '',
            $request->signature
        );
        $signatureData = str_replace(' ', '+', $signatureData);

        $decodedImage = base64_decode($signatureData);

        if ($decodedImage === false) {
            return response()->json([
                'success' => false,
                'message' => 'Format signature tidak valid'
            ], 422);
        }

        $safeKode = str_replace('/', '_', $po->po_kode);
        $filename = 'signature_' . $safeKode . '.png';
        $relativePath = 'signatures/' . $filename;

        Storage::disk('public')->put($relativePath, $decodedImage);

        $po->update([
            'signature_url' => $relativePath, 
            'sign_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tanda tangan berhasil disimpan'
        ]);

    } catch (\Throwable $e) {
        Log::error('SIGN ERROR', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan tanda tangan'
        ], 500);
    }
}

public function clearSignature(string $kode): JsonResponse
{
    try {
        $po = PurchaseOrderModel::where('po_kode', $kode)->first();

        if (!$po) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Order tidak ditemukan'
            ], 404);
        }

        if (!empty($po->signature_url)) {
            $path = $po->signature_url; 
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $po->update([
            'signature_url' => null,
            'sign_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signature berhasil direset'
        ]);
    } catch (\Throwable $e) {
        Log::error('CLEAR SIGNATURE ERROR', [
            'message' => $e->getMessage(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Gagal reset signature'
        ], 500);
    }
}
}
