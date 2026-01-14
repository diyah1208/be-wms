<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeliveryListExport implements FromCollection, WithHeadings
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
                $i + 1,
                $d->dlv_kode,
                $d->mr?->mr_kode,
                $d->dlv_dari_gudang,
                $d->dlv_ke_gudang,
                $d->dlv_ekspedisi,
                $d->dlv_jumlah_koli,
                $d->dlv_status,
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
}

