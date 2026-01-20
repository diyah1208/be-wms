<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SpbListExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnFormatting,
    ShouldAutoSize,
    WithEvents
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->values()->map(function ($d, $i) {
            return [
                $i + 1,                                
                $d->created_at,                        
                $d->spb_no,                             
                $d->dtl_spb_part_number,                
                $d->dtl_spb_part_name,                  
                $d->dtl_spb_qty,                        
                $d->dtl_spb_part_satuan,                
                $d->spb_kode_unit,                      
                $d->spb_tipe_unit,                      
                $d->spb_brand,                          
                $d->spb_hm,                             
                $d->spb_problem_remark,                 
                $d->spb_section,                        
                $d->spb_pic_gmi,                        
                $d->spb_pic_ppa,                        
                $d->spb_no_wo,                          
                $d->created_at,                      
                $d->spb_status,                         
                $d->spb_tanggal,                        
                $d->po_no,                              
                $d->so_no,                              
                $d->so_date,                      
                $d->do_no,                             
                $d->do_date,                      
                $d->invoice_no,                         
                $d->invoice_date,                      
                $d->invoice_email_date,                
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'TGL SPB',
            'NO SPB',
            'PART NUMBER',
            'PART NAME',
            'QTY',
            'UOM',
            'KODE UNIT',
            'TYPE UNIT',
            'BRAND',
            'HM',
            'PROBLEM / REMARK',
            'SECTION',
            'PIC GMI',
            'PIC PPA',
            'NO WO',
            'DATE INPUT WO',
            'STATUS WO',
            'TANGGAL SPB to PO',
            'NO PO',
            'NO SO',
            'DATE INPUT PO',
            'NO DO',
            'DATE INPUT DO',
            'NO INVOICE',
            'TANGGAL INVOICE',
            'TANGGAL KIRIM EMAIL',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'], 
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '000000'], 
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G'  => NumberFormat::FORMAT_NUMBER,
            'K'  => NumberFormat::FORMAT_NUMBER,
            'Q'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'S'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'V'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'X'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Y'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'Z'  => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->freezePane('D2');

                $highestRow = $event->sheet->getHighestRow();
                $highestCol = $event->sheet->getHighestColumn();

                $event->sheet
                    ->getStyle("A1:{$highestCol}{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');
            },
        ];
    }
}
