<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbDetailModel extends Model
{
    protected $table = 'tb_spb_detail';
    protected $primaryKey = 'spb_dtl_id';

    protected $fillable = [
        'spb_id',
        'part_id',
        'dtl_spb_part_number',
        'dtl_spb_part_name',
        'dtl_spb_part_satuan',
        'dtl_spb_qty',
    ];

    public function part()
    {
        return $this->belongsTo(Barang::class, 'part_id', 'part_id');
    }

    public function spb()
    {
        return $this->belongsTo(SpbModel::class, 'spb_id', 'spb_id');
    }
}
