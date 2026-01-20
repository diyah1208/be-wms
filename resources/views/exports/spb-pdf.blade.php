<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px; }
        .no-border td { border: none; }
        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>

{{-- HEADER --}}
<table class="no-border">
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
    </tr>
</table>

<hr>

<h3 class="center">SURAT PENGELUARAN BARANG</h3>

{{-- INFO HEADER --}}
<table class="no-border">
    <tr>
        <td>No. SPB</td><td>: {{ $spb->spb_no }}</td>
        <td>No. WO</td><td>: {{ $spb->spb_no_wo }}</td>
        <td>HM</td><td>: {{ $spb->spb_hm }}</td>
    </tr>
    <tr>
        <td>Lokasi</td><td>: {{ $spb->spb_gudang }}</td>
        <td>No. PO</td><td>: {{ optional($spb->po)->po_no ?? '-' }}</td>
        <td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($spb->spb_tanggal)->format('d/m/Y') }}</td>
    </tr>
</table>

<br>

{{-- DETAIL TABLE --}}
<table>
    <thead>
        <tr class="center bold">
            <th>No</th>
            <th>Part No</th>
            <th>Description</th>
            <th>Qty</th>
            <th>Satuan</th>
            <th>No Unit AMM</th>
            <th>Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($spb->details as $i => $d)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $d->dtl_spb_part_number }}</td>
            <td>{{ $d->dtl_spb_part_name }}</td>
            <td class="center">{{ $d->dtl_spb_qty }}</td>
            <td class="center">{{ $d->dtl_spb_part_satuan }}</td>
            <td class="center">{{ $spb->spb_kode_unit }}</td>
            <td>{{ $spb->spb_problem_remark }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<br><br>

{{-- SIGNATURE --}}
<table class="no-border center">
    <tr>
        <td>Yang Menyerahkan<br><br><br>{{ $spb->spb_pic_gmi }}</td>
        <td>Mengetahui<br><br><br>GL Plant</td>
        <td>Mengetahui<br><br><br>Planner</td>
    </tr>
</table>

<br>

</body>
</html>
