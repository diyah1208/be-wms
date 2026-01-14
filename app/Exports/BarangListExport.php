<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BarangListExport implements FromCollection, WithHeadings
{
    protected $barang;

    public function __construct($barang)
    {
        $this->barang = $barang;
    }

    public function collection()
    {
        return $this->barang->map(function ($d, $i) {
            return [
                $i + 1,
                $d->part_number,
                $d->part_name,
                $d->part_satuan,
                $d->created_at,
                $d->updated_at,
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
            'Created At',
            'Updated At',
        ];
    }
}

