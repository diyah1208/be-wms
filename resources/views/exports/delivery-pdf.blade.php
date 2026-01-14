<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Delivery {{ $delivery->dlv_kode }}</title>

    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 12px;
            margin: 40px 40px 30px 40px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .label {
            font-weight: bold;
            white-space: nowrap;
        }

        .data th, .data td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        .data th {
            background: #f2f2f2;
        }

        .left {
            text-align: left;
        }

        .sign td {
            padding-top: 55px;
            text-align: center;
        }
    </style>
</head>

<body>

{{-- ================= HEADER ================= --}}
<table>
    <tr>
        <td>
            <img
                src="{{ public_path('images/Logo Garuda Mart Indonesia.png') }}"
                style="height:50px; display:block;"
            >
            <div style="font-size:9px; margin-top:4px; line-height:1.4;">
                Sentra Niaga Kalimalang Blok J5-8A<br>
                Jakarta Timur, Indonesia<br>
                Telp: (021) 847 7309
            </div>
        </td>
    </tr>
</table>

<table style="margin-top:10px; margin-bottom:18px;">
    <tr>
        <td style="border-bottom:1px solid #000;"></td>
    </tr>
</table>

<div style="text-align:center; margin: 10px 0 18px 0;">
    <div style="font-size:15px; font-weight:bold; letter-spacing:0.5px;">
        SURAT PENERIMAAN BARANG
    </div>
</div>


{{-- ================= INFO DOKUMEN (2 KOLOM RAPI) ================= --}}
<table width="100%" style="margin-bottom:18px;">
    <tr>
        <!-- KOLOM KIRI -->
        <td width="50%" valign="top">
            <table width="100%">
                <tr>
                    <td class="label" width="35%">Dari Site</td>
                    <td width="65%">: {{ $delivery->dlv_dari_gudang }}</td>
                </tr>
                <tr>
                    <td class="label">Ke Site</td>
                    <td>: {{ $delivery->dlv_ke_gudang }}</td>
                </tr>
                <tr>
                    <td class="label">No Resi</td>
                    <td>: {{ $delivery->dlv_no_resi ?? '-' }}</td>
                </tr>
                 <tr>
                    <td class="label" width="35%">Tanggal</td>
                    <td width="65%">: {{ $delivery->created_at->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <td class="label">Ekspedisi</td>
                    <td>: {{ $delivery->dlv_ekspedisi }}</td>
                </tr>
                <tr>
                    <td class="label">PIC Dokumen</td>
                    <td>: {{ $delivery->dlv_pic }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ================= JUDUL DELIVERY (TENGAH, SEBELUM TABEL) ================= --}}
<div style="text-align:right; margin: 18px 0 16px 0;">
    <div style="font-size:13px; font-weight:bold;">
        DELIVERY {{ $delivery->dlv_kode }}
    </div>
</div>

{{-- ================= TABEL BARANG ================= --}}
<table class="data">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="18%">Part No</th>
            <th>Deskripsi</th>
            <th width="8%">Qty</th>
            <th width="10%">Satuan</th>
            <th width="12%">Qty Diterima</th>
            <th width="17%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($delivery->details as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $item->dtl_dlv_part_number }}</td>
            <td class="left">{{ $item->dtl_dlv_part_name }}</td>
            <td>{{ $item->qty_dikirim }}</td>
            <td>{{ $item->dtl_dlv_satuan }}</td>
            <td>{{ $item->qty_delivered }}</td>
            <td class="left">{{ $item->receive_note ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TANDA TANGAN ================= --}}
<table class="sign" style="margin-top:40px;">
    <tr>
        {{-- WAREHOUSE PENGIRIM --}}
        <td width="{{ $delivery->dlv_ekspedisi === 'Hand Carry' ? '50%' : '33%' }}">
            Warehouse {{ $delivery->dlv_dari_gudang }}<br><br>

            @if ($delivery->signed_pengirim_sign)
                <img src="{{ public_path($delivery->signed_pengirim_sign) }}" height="45"><br>
                <strong>{{ $delivery->signed_pengirim_name }}</strong><br>
                <span style="font-size:10px;">
                    {{ \Carbon\Carbon::parse($delivery->signed_pengirim_at)->format('d-m-Y H:i') }}
                </span>
            @else
                ( __________________________ )
            @endif
        </td>

        {{-- LOGISTIK (NON HAND CARRY) --}}
        @if ($delivery->dlv_ekspedisi !== 'Hand Carry')
        <td width="33%">
            Logistik<br><br>

            @if ($delivery->signed_logistik_sign)
                <img src="{{ public_path($delivery->signed_logistik_sign) }}" height="45"><br>
                <strong>{{ $delivery->signed_logistik_name }}</strong><br>
                <span style="font-size:10px;">
                    {{ \Carbon\Carbon::parse($delivery->signed_logistik_at)->format('d-m-Y H:i') }}
                </span>
            @else
                ( __________________________ )
            @endif
        </td>
        @endif

        {{-- WAREHOUSE PENERIMA --}}
        <td width="{{ $delivery->dlv_ekspedisi === 'Hand Carry' ? '50%' : '33%' }}">
            Warehouse {{ $delivery->dlv_ke_gudang }}<br><br>

            @if ($delivery->signed_penerima_sign)
                <img src="{{ public_path($delivery->signed_penerima_sign) }}" height="45"><br>
                <strong>{{ $delivery->signed_penerima_name }}</strong><br>
                <span style="font-size:10px;">
                    {{ \Carbon\Carbon::parse($delivery->signed_penerima_at)->format('d-m-Y H:i') }}
                </span>
            @else
                ( __________________________ )
            @endif
        </td>
    </tr>
</table>

</body>
</html>
