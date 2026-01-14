<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockListExport implements FromCollection, WithHeadings
{
    protected $stocks;

    public function __construct($stocks)
    {
        $this->stocks = $stocks;
    }

    public function collection()
    {
        return $this->stocks
            ->groupBy('part_id')
            ->values()
            ->map(function ($rows, $i) {

                $barang = $rows->first()->barang;

                $get = fn($lokasi) =>
                    (int) $rows->where('stk_location', $lokasi)->sum('stk_qty');

                $balikpapan = $get('BALIKPAPAN');
                $jakarta    = $get('JAKARTA');
                $ami        = $get('SITE AMI');
                $ba         = $get('SITE BA');
                $bib        = $get('SITE BIB');
                $mifa       = $get('SITE MIFA');
                $mip        = $get('SITE MIP');
                $tabang     = $get('SITE TABANG');
                $tal        = $get('SITE TAL');
                $tanjung    = $get('MUARA ENIM');

                $sum = $balikpapan + $jakarta + $ami + $ba + $bib + $mifa + $mip + $tabang + $tal + $tanjung;

                return [
                    $i + 1,
                    $barang->part_number,
                    $barang->part_name,
                    $barang->part_satuan,
                    $balikpapan,
                    $jakarta,
                    $ami,
                    $ba,
                    $bib,
                    $mifa,
                    $mip,
                    $tabang,
                    $tal,
                    $tanjung,
                    $sum,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
            'Part Number',
            'Part Name',
            'Satuan',
            'BALIKPAPAN',
            'JAKARTA',
            'SITE AMI',
            'SITE BA',
            'SITE BIB',
            'SITE MIFA',
            'SITE MIP',
            'SITE TABANG',
            'SITE TAL',
            'MUARA ENIM',
            'SUM',
        ];
    }
}
