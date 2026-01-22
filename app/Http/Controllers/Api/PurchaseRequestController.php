<?php

namespace App\Http\Controllers\Api;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseRequestModel;
use App\Models\BarangModel;
use App\Models\PurchaseRequestItemModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class PurchaseRequestController extends Controller
{
    public function index()
    {
        $data = PurchaseRequestModel::with([
            'details',
            'details.mr', 
        ]); 
        return response()->json($data->get());
    }

    public function showKode($kode)
    {
        $kode = urldecode($kode);
        $pr = PurchaseRequestModel::with([
            'details',
            'details.mr',
        ])
        ->where('pr_kode', $kode)
        ->firstOrFail();

        return response()->json($pr);
    }


    public function show($id)
    {
        $pr = PurchaseRequestModel::with([
            'details',
            'details.mr',
        ])->findOrFail($id);

        return response()->json($pr);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pr_kode'        => 'required|unique:tb_purchase_request,pr_kode',
            'pr_lokasi'      => 'required',
            'pr_tanggal'     => 'required',
            'pr_pic'         => 'required',
            'details.*.dtl_pr_qty' => 'required|numeric|min:1',
            'details'        => 'required|array',
        ]);

        DB::transaction(function () use ($request) {

            $delivery = PurchaseRequestModel::create([
                'pr_kode'     => $request->pr_kode,
                'pr_lokasi'   => $request->pr_lokasi,
                'pr_tanggal'  => $request->pr_tanggal,
                'pr_status'   => 'open',
                'pr_pic'      => $request->pr_pic,
            ]);

            foreach ($request->details as $item) {
                PurchaseRequestItemModel::create([
                    'pr_id'                 => $delivery->pr_id,
                    'mr_id'                 => $item['mr_id'],
                    'part_id'               => $item['part_id'],
                    'dtl_pr_part_number'    => $item['dtl_pr_part_number'],
                    'dtl_pr_part_name'      => $item['dtl_pr_part_name'],
                    'dtl_pr_satuan'         => $item['dtl_pr_satuan'],
                    'dtl_pr_qty'            => $item['dtl_pr_qty'] ?? 0,
                ]);
            }
        });

        return response()->json(['message' => 'Purchase Request created']);
    }

    public function sign(Request $request): JsonResponse
{
    try {
        $request->validate([
            'kode' => 'required|string',
            'signature' => 'required|string',
        ]);

        $pr = PurchaseRequestModel::where('pr_kode', $request->kode)->first();

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Request tidak ditemukan'
            ], 404);
        }

        if (!empty($pr->signature_url)) {
            $oldPath = str_replace('/storage/', '', $pr->signature_url);
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

        $safeKode = str_replace('/', '_', $pr->pr_kode);
        $filename = 'signature_' . $safeKode . '.png';
        $relativePath = 'signatures/' . $filename;

        Storage::disk('public')->put($relativePath, $decodedImage);

        $pr->update([
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
        $pr = PurchaseRequestModel::where('pr_kode', $kode)->first();

        if (!$pr) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase Request tidak ditemukan'
            ], 404);
        }

        if (!empty($pr->signature_url)) {
            $path = $pr->signature_url; 
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $pr->update([
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

public function exportPdf(string $kode)
{
    $pr = PurchaseRequestModel::with(['details'])
        ->where('pr_kode', $kode)
        ->firstOrFail();

    $pdf = Pdf::loadView(
        'exports.pr-pdf',
        compact('pr')
    )->setPaper('A4', 'portrait');

    return $pdf->download(
        'PR_' . str_replace('/', '_', $pr->pr_kode) . '.pdf'
    );
}
}