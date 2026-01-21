<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ReceiveListExport implements 
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    protected $receives;

    public function __construct($receive)
    {
        $this->receives = $receive;
    }

    public function collection()
    {
        return $this->receives->map(function ($d, $i) {
            return [
                'no'         => $i + 1,
                'ri_kode'    => $d->ri_kode,
                'po_kode'    => $d->purchaseOrder?->po_kode ?? '-',
                'tanggal'    => $d->ri_tanggal,
                'lokasi'     => $d->ri_lokasi,
                'pic'        => $d->ri_pic,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Receive Item',
            'Kode Purchase Order',
            'Tanggal Receive',
            'Gudang Penerima',
            'PIC',
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

            // TANGGAL RATA TENGAH
            "D2:D{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
