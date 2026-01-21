<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockListExport implements 
    FromCollection, 
    WithHeadings, 
    WithStyles, 
    ShouldAutoSize
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

                $get = fn ($lokasi) =>
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

                $sum = $balikpapan + $jakarta + $ami + $ba + $bib
                     + $mifa + $mip + $tabang + $tal + $tanjung;

                return [
                    'no'          => $i + 1,
                    'part_number' => $barang->part_number,
                    'part_name'   => $barang->part_name,
                    'satuan'      => $barang->part_satuan,
                    'balikpapan'  => $balikpapan,
                    'jakarta'     => $jakarta,
                    'ami'         => $ami,
                    'ba'          => $ba,
                    'bib'         => $bib,
                    'mifa'        => $mifa,
                    'mip'         => $mip,
                    'tabang'      => $tabang,
                    'tal'         => $tal,
                    'tanjung'     => $tanjung,
                    'sum'         => $sum,
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
            'TOTAL',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow    = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        return [
            // HEADER
            1 => [
                'font' => [
                    'bold' => true,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],

            // BODY
            "A2:{$lastColumn}{$lastRow}" => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ],

            // ANGKA (kolom stok & total rata kanan)
            "E2:{$lastColumn}{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];
    }
}
