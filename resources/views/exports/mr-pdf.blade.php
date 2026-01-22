<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Material Request {{ $mr->mr_kode }}</title>

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
        MATERIAL REQUEST
    </div>
</div>

{{-- ================= INFO MR ================= --}}
<table width="100%" style="margin-bottom:18px;">
    <tr>
        <td width="50%" valign="top">
            <table width="100%">
                <tr>
                    <td class="label" style="width:140px;">Kode MR</td>
                    <td style="width:10px;">:</td>
                    <td>{{ $mr->mr_kode }}</td>
                </tr>

                <tr>
                    <td class="label">PIC</td>
                    <td>:</td>
                    <td>{{ $mr->mr_pic }}</td>
                </tr>

                <tr>
                    <td class="label">Lokasi</td>
                    <td>:</td>
                    <td>{{ $mr->mr_lokasi }}</td>
                </tr>

                <tr>
                    <td class="label">Status</td>
                    <td>:</td>
                    <td style="text-transform:capitalize;">
                        {{ $mr->mr_status }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Tanggal MR</td>
                    <td>:</td>
                    <td>
                        {{ \Carbon\Carbon::parse($mr->mr_tanggal)
                            ->locale('id')
                            ->translatedFormat('l, d F Y') }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Due Date</td>
                    <td>:</td>
                    <td>
                        {{ \Carbon\Carbon::parse($mr->mr_due_date)
                            ->locale('id')
                            ->translatedFormat('l, d F Y') }}
                    </td>
                </tr>

                <tr>
                    <td class="label">Terakhir Edit</td>
                    <td>:</td>
                    <td>
                        {{ $mr->mr_last_edit_by ?? '-' }}
                        @if($mr->mr_last_edit_at)
                            ({{ \Carbon\Carbon::parse($mr->mr_last_edit_at)->format('d-m-Y H:i') }})
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ================= JUDUL MR ================= --}}
<div style="text-align:right; margin: 18px 0 16px 0;">
    <div style="font-size:13px; font-weight:bold;">
        MR {{ $mr->mr_kode }}/{{ strtoupper($mr->mr_lokasi) }}
    </div>
</div>

{{-- ================= TABEL BARANG ================= --}}
<table class="data">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="18%">Part Number</th>
            <th>Nama Part</th>
            <th width="8%">Satuan</th>
            <th width="10%">Prioritas</th>
            <th width="12%">Qty Request</th>
            <th width="12%">Qty Diterima</th>
            <th width="15%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($mr->details as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="left">{{ $item->dtl_mr_part_number }}</td>
            <td class="left">{{ $item->dtl_mr_part_name }}</td>
            <td>{{ $item->dtl_mr_satuan }}</td>
            <td>{{ $item->dtl_mr_prioritas }}</td>
            <td>{{ $item->dtl_mr_qty_request }}</td>
            <td>{{ $item->dtl_mr_qty_received }}</td>
            <td class="left">
                {{ $item->dtl_mr_note ?? '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- ================= TANDA TANGAN ================= --}}
<table class="sign" style="margin-top:50px;">
    <tr>
        <td width="60%"></td>
        <td width="40%">
            <div style="font-weight:bold;">Disetujui Oleh</div>

            @if ($mr->signature_url)
                <div style="height:70px; margin:10px 0;">
                    <img
                        src="{{ storage_path('app/public/' . $mr->signature_url) }}"
                        style="max-width:180px; max-height:70px;"
                    >
                </div>

                <strong style="font-size:11px;">
                    {{ $mr->mr_pic }}
                </strong>

                <div style="font-size:9px; margin-top:2px;">
                    {{ \Carbon\Carbon::parse($mr->sign_at)->format('d-m-Y H:i') }}
                </div>
            @endif
        </td>
    </tr>
</table>

</body>
</html>