<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReceiveListExport implements FromCollection, WithHeadings
{
    protected $receive;

    public function __construct($receive)
    {
        $this->receives = $receive;
    }

    public function collection()
    {
        return $this->receives->map(function ($d, $i) {
            return [
                $i + 1,
                $d->ri_kode,
                $d->purchase_order?->po_kode,
                $d->ri_tanggal,
                $d->ri_lokasi,
                $d->ri_pic,
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
}

