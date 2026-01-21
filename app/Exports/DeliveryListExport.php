<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DeliveryListExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    protected $deliveries;

    public function __construct($deliveries)
    {
        $this->deliveries = $deliveries;
    }

    public function collection()
    {
        return $this->deliveries->map(function ($d, $i) {
            return [
                'no'            => $i + 1,
                'dlv_kode'      => $d->dlv_kode,
                'mr_kode'       => $d->mr?->mr_kode ?? '-',
                'dari_gudang'   => $d->dlv_dari_gudang,
                'ke_gudang'     => $d->dlv_ke_gudang,
                'ekspedisi'     => $d->dlv_ekspedisi,
                'jumlah_koli'   => $d->dlv_jumlah_koli,
                'status'        => strtoupper($d->dlv_status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Delivery',
            'Kode MR',
            'Dari Gudang',
            'Ke Gudang',
            'Ekspedisi',
            'Jumlah Koli',
            'Status',
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

            // ANGKA & STATUS TENGAH
            "A2:A{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "G2:G{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            "H2:H{$lastRow}" => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
