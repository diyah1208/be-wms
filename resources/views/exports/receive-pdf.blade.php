<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Receive {{ $receive->ri_kode }}</title>

    <style>
        body {
            font-family: Calibri, sans-serif;
            font-size: 12px;
            margin: 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .data th, .data td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .data th {
            background: #f2f2f2;
            font-weight: bold;
        }

        .left {
            text-align: left;
        }

        .sign td {
            padding-top: 60px;
            text-align: center;
        }
    </style>
</head>
<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td>
            <img src="{{ public_path('images/Logo Garuda Mart Indonesia.png') }}" style="height:50px;">
            <div style="font-size:12px; margin-top:4px; line-height:1.4;">
                <strong>PT. Garuda Mart Indonesia</strong><br>
                RT.002/RW.012, Jatiasih, Kec. Jatiasih<br>
                Kota Bekasi, Jawa Barat 17423<br>
                Telp: (021) 82407309
            </div>
        </td>
        <td width="30%" align="right" valign="top">
            <img
                src="{{ public_path('images/Logo-Lourdes.png') }}"
                style="height:45px;"
            >
        </td>
    </tr>
</table>

<hr style="margin:14px 0;">

<div style="text-align:center; font-size:15px; font-weight:bold;">
    SURAT PENERIMAAN BARANG
</div>

{{-- ================= INFO ================= --}}
<table style="margin:18px 0;">
    <tr>
        <td width="30%"><strong>Lokasi Penerima</strong></td>
        <td width="2%">:</td>
        <td>{{ $receive->ri_lokasi }}</td>
    </tr>
    <tr>
        <td><strong>Purchase Order</strong></td>
        <td>:</td>
        <td>{{ optional($receive->purchaseOrder)->po_kode ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Tanggal Penerimaan</strong></td>
        <td>:</td>
        <td>{{ \Carbon\Carbon::parse($receive->ri_tanggal)->format('d-m-Y') }}</td>
    </tr>
    <tr>
        <td><strong>Keterangan</strong></td>
        <td>:</td>
        <td>{{ $receive->ri_keterangan ?? '-' }}</td>
    </tr>
    <tr>
        <td><strong>Penanggung Jawab</strong></td>
        <td>:</td>
        <td>{{ $receive->ri_pic }}</td>
    </tr>
</table>

<div style="text-align:right; font-weight:bold; margin-bottom:10px;">
    RECEIVE {{ $receive->ri_kode }} / {{ strtoupper($receive->ri_lokasi) }}
</div>

{{-- ================= TABEL BARANG ================= --}}
<table class="data">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="18%">Part Number</th>
            <th>Part Name</th>
            <th width="10%">Satuan</th>
            <th width="10%">Qty</th>
            <th width="17%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($receive->details as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $item->dtl_ri_part_number }}</td>
            <td class="left">{{ $item->dtl_ri_part_name }}</td>
            <td>{{ $item->dtl_ri_satuan }}</td>
            <td>{{ $item->dtl_ri_qty }}</td>
            <td></td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TANDA TANGAN ================= --}}
<table class="sign">
    <tr>
        <td width="60%"></td>
        <td width="40%">
            <strong>Warehouse Penerima</strong><br>
            {{ $receive->ri_lokasi }}

            @if ($receive->signed_penerima_sign)
                <div style="margin:10px 0;">
                    <img
                        src="{{ storage_path('app/public/'.$receive->signed_penerima_sign) }}"
                        style="max-width:180px; max-height:70px;"
                    >
                </div>
                <strong>{{ $receive->signed_penerima_name ?? $receive->ri_pic }}</strong>
                <div style="font-size:10px;">
                    {{ \Carbon\Carbon::parse($receive->signed_penerima_at)->format('d-m-Y H:i') }}
                </div>
            @endif
        </td>
    </tr>
</table>
</body>
</html>
