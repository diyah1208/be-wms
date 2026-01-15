<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MaterialRequestModel;
use App\Models\MaterialRequestItemModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\MaterialRequestModel as MaterialRequest;
class MaterialRequestController extends Controller
{
    private array $lokasiKodeMap = [
        'JAKARTA'        => 'JKT',
        'TANJUNG ENIM'   => 'ENIM',
        'BALIKPAPAN'     => 'BPN',
        'SITE BA'        => 'BA',
        'SITE TAL'       => 'TAL',
        'SITE MIP'       => 'MIP',
        'SITE MIFA'      => 'MIFA',
        'SITE BIB'       => 'BIB',
        'SITE AMI'       => 'AMI',
        'SITE TABANG'    => 'TAB',
    ];

    private function getLokasiKode(string $lokasi): string
    {
        return $this->lokasiKodeMap[strtoupper($lokasi)] ?? 'UNK';
    }

    public function index()
    {
        return response()->json(
            MaterialRequestModel::with('details')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'mr_tanggal'  => 'required|date',
            'mr_lokasi'   => 'required|string',
            'mr_pic'      => 'required',
            'mr_due_date' => 'required|date',
            'details'     => 'required|array|min:1'
        ]);

        return DB::transaction(function () use ($request) {

            $lokasiNama = strtoupper($request->mr_lokasi);
            $lokasiKode = $this->getLokasiKode($lokasiNama);
            $now = now();

            $lastMr = MaterialRequestModel::where('mr_lokasi', $lokasiNama)
                ->whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->lockForUpdate()
                ->orderBy('mr_id', 'desc')
                ->first();

            $urutan = $lastMr
                ? ((int) substr($lastMr->mr_kode, -5)) + 1
                : 1;

            $mrKode = sprintf(
                "GMI/%s/%s/%05d",
                $lokasiKode,
                $now->format('y/m'),
                $urutan
            );

            $mr = MaterialRequestModel::create([
                'mr_kode'     => $mrKode,
                'mr_tanggal'  => $request->mr_tanggal,
                'mr_lokasi'   => $lokasiNama,
                'mr_pic'      => $request->mr_pic,
                'mr_due_date' => $request->mr_due_date,
                'mr_status'   => 'open',
                'mr_last_edit_by' => $request->mr_last_edit_by,
                'mr_last_edit_at' => now(),
            ]);

            foreach ($request->details as $item) {
                MaterialRequestItemModel::create([
                    'mr_id'               => $mr->mr_id,
                    'part_id'             => $item['part_id'],
                    'dtl_mr_part_number'  => $item['dtl_mr_part_number'],
                    'dtl_mr_part_name'    => $item['dtl_mr_part_name'],
                    'dtl_mr_satuan'       => $item['dtl_mr_satuan'],
                    'dtl_mr_prioritas'    => $item['dtl_mr_prioritas'],
                    'dtl_mr_qty_request'  => (int) $item['dtl_mr_qty_request'],
                    'dtl_mr_qty_received' => 0
                ]);
            }

            return response()->json($mr, 201);
        });
    }

    public function show($id)
    {
        return response()->json(
            MaterialRequestModel::with(['details'])->findOrFail($id)
        );
    }

    public function showKode($kode)
    {
        $mr = MaterialRequestModel::with(['details'])
            ->where('mr_kode', $kode)
            ->firstOrFail();

        return response()->json($mr);
    }

    public function getOpenMR()
    {
        return response()->json(
            MaterialRequestModel::where('mr_status', 'open')
                ->orderBy('created_at', 'desc')
                ->get(['mr_id', 'mr_kode'])
        );
    }

    public function update(Request $request, $id)
    {
        $mr = MaterialRequestModel::with('details')->findOrFail($id);

        // 🔒 hanya boleh edit saat OPEN
        if ($mr->mr_status !== 'open') {
            return response()->json([
                'message' => 'MR tidak bisa diedit karena status bukan OPEN'
            ], 403);
        }

        if ($request->has('mr_status')) {

            $status = $request->mr_status === 'close'
                ? 'closed'
                : $request->mr_status;

            $mr->update([
                'mr_status' => $status,
                'mr_last_edit_at' => now(),
                'mr_last_edit_by' => $request->mr_last_edit_by,
            ]);

            return response()->json([
                'message' => 'Status MR berhasil diupdate'
            ]);
        }

        $request->validate([
            'dtl_mr_id' => 'required',
            'part_id' => 'required',
            'dtl_mr_part_number' => 'required|string',
            'dtl_mr_part_name' => 'required|string',
            'dtl_mr_satuan' => 'required|string',
            'dtl_mr_prioritas' => 'required|string',
            'dtl_mr_qty_request' => 'required|integer|min:1'
        ]);

        $item = MaterialRequestItemModel::findOrFail($request->dtl_mr_id);

        // pastikan item milik MR ini
        if ($item->mr_id !== $mr->mr_id) {
            return response()->json([
                'message' => 'Item bukan milik MR ini'
            ], 403);
        }

        $item->update([
            'part_id' => $request->part_id,
            'dtl_mr_part_number' => $request->dtl_mr_part_number,
            'dtl_mr_part_name' => $request->dtl_mr_part_name,
            'dtl_mr_satuan' => $request->dtl_mr_satuan,
            'dtl_mr_prioritas' => $request->dtl_mr_prioritas,
            'dtl_mr_qty_request' => $request->dtl_mr_qty_request
        ]);

        $mr->update([
            'mr_last_edit_at' => now(),
            'mr_last_edit_by' => $request->mr_last_edit_by
        ]);

        return response()->json([
            'message' => 'Item MR berhasil diupdate'
        ]);
    }
public function deleteDetail(string $detailId): JsonResponse
{
    return DB::transaction(function () use ($detailId) {

        $detail = MaterialRequestItemModel::with('materialRequest')
            ->findOrFail($detailId);

        $mr = $detail->materialRequest;

        if ($mr->mr_status !== 'open') {
            return response()->json([
                'message' => 'Detail tidak bisa dihapus karena MR bukan OPEN'
            ], 403);
        }

        if ((int) $detail->dtl_mr_qty_received > 0) {
            return response()->json([
                'message' => 'Detail tidak bisa dihapus karena sudah ada barang diterima'
            ], 403);
        }

        $detail->delete();

        $mr->update([
            'mr_last_edit_at' => now(),
            'mr_last_edit_by' => auth()->user()->name,
        ]);

        return response()->json([
            'message' => 'Detail MR berhasil dihapus'
        ]);
    });
}


public function sign(Request $request): JsonResponse
{
    try {
        $request->validate([
            'kode' => 'required|string',
            'signature' => 'required|string',
        ]);

        $mr = MaterialRequest::where('mr_kode', $request->kode)->first();

        if (!$mr) {
            return response()->json([
                'success' => false,
                'message' => 'Material Request tidak ditemukan'
            ], 404);
        }

        if (!empty($mr->signature_url)) {
            $oldPath = str_replace('/storage/', '', $mr->signature_url);
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

        $safeKode = str_replace('/', '_', $mr->mr_kode);
        $filename = 'signature_' . $safeKode . '.png';
        $relativePath = 'signatures/' . $filename;

        Storage::disk('public')->put($relativePath, $decodedImage);

        $mr->update([
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
        $mr = MaterialRequest::where('mr_kode', $kode)->first();

        if (!$mr) {
            return response()->json([
                'success' => false,
                'message' => 'Material Request tidak ditemukan'
            ], 404);
        }

        if (!empty($mr->signature_url)) {
            $path = $mr->signature_url; 
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $mr->update([
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

    public function generateKode(Request $request)
    {
        $lokasiNama = strtoupper(trim($request->lokasi));
        $lokasiKode = $this->getLokasiKode($lokasiNama);
        $tahunBulan = now()->format('y/m');

        $last = MaterialRequestModel::where('mr_lokasi', $lokasiNama)
            ->where('mr_kode', 'like', "%/$tahunBulan/%")
            ->orderBy('mr_id', 'desc')
            ->first();

        $nextNumber = $last
            ? ((int) substr($last->mr_kode, -5)) + 1
            : 1;

        return response()->json(sprintf(
            "GMI/%s/%s/%05d",
            $lokasiKode,
            $tahunBulan,
            $nextNumber
        ));
    }
    
}
