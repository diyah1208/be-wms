<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Delivery {{ $delivery->dlv_kode }}</title>

    <style>
        body {
            font-family: "calibri", Times, serif;
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
            <img
                src="{{ public_path('images/Logo Garuda Mart Indonesia.png') }}"
                style="height:50px; display:block;"
            >
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

{{-- ================= INFO DOKUMEN ================= --}}
<table width="100%" style="margin-bottom:18px;">
    <tr>
        <td width="50%" valign="top">
            <table width="100%" style="border-collapse:collapse;">
                <tr>
                    <td style="width:140px; font-weight:bold;">Lokasi Pengiriman</td>
                    <td style="width:10px;">:</td>
                    <td>{{ $delivery->dlv_dari_gudang }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Lokasi Penerima</td>
                    <td>:</td>
                    <td>{{ $delivery->dlv_ke_gudang }}</td>
                </tr>

                <tr>
                    <td style="font-weight:bold;">Nomor Resi</td>
                    <td>:</td>
                    <td>{{ $delivery->dlv_no_resi ?? '-' }}</td>
                </tr>
                <tr>
                    <td style="width:120px; font-weight:bold;">Tanggal Pengiriman</td>
                    <td style="width:8px;">:</td>
                    <td>
                        {{ \Carbon\Carbon::parse($delivery->created_at)
                            ->locale('id')
                            ->translatedFormat('l, d F Y') }}
                    </td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Jasa Pengiriman</td>
                    <td>:</td>
                    <td>{{ $delivery->dlv_ekspedisi }}</td>
                </tr>
                <tr>
                    <td style="font-weight:bold;">Penanggung Jawab</td>
                    <td>:</td>
                    <td>{{ $delivery->dlv_pic }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{{-- ================= JUDUL DELIVERY ================= --}}
<div style="text-align:right; margin: 18px 0 16px 0;">
    <<div style="font-size:13px; font-weight:bold;">
        DELIVERY {{ $delivery->dlv_kode }}/{{ strtoupper($delivery->dlv_dari_gudang) }}/{{ strtoupper($delivery->dlv_ke_gudang) }}
    </div>
</div>

{{-- ================= TABEL BARANG ================= --}}
<table class="data">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="18%">Part Number</th>
            <th>Part Name</th>
            <th width="8%">Qty Kirim</th>
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
            <td>{{ $item->qty_on_delivery + $item->qty_delivered }}</td>
            <td>{{ $item->dtl_dlv_satuan }}</td>
            <td>{{ $item->qty_delivered }}</td>
            <td class="left">{{ $item->receive_note ?? '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TANDA TANGAN (POJOK KANAN) ================= --}}
<table class="sign" style="margin-top:50px; width:100%;">
    <tr>
        <td width="60%"></td>
        <td width="40%" style="text-align:center; vertical-align:top;">
            <div style="font-weight:bold;">
                Warehouse Penerima
            </div>
            <div style="margin-bottom:8px;">
                {{ $delivery->dlv_ke_gudang }}
            </div>

            @if ($delivery->signed_penerima_sign)
                <div style="height:70px; margin:10px 0;">
                    <img
                        src="{{ storage_path('app/public/' . $delivery->signed_penerima_sign) }}"
                        style="max-width:180px; max-height:70px;"
                    >
                </div>

                    <strong style="font-size:11px;">
                        {{ $delivery->signed_penerima_name ?? $delivery->dlv_pic }}
                    </strong>

                <div style="font-size:9px; margin-top:2px;">
                    {{ \Carbon\Carbon::parse($delivery->signed_penerima_at)->format('d-m-Y H:i') }}
                </div>
            @else
            @endif
        </td>
    </tr>
</table>
</body>
</html>
