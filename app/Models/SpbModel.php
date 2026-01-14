<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbModel extends Model
{
    protected $table = 'tb_spb';
    protected $primaryKey = 'spb_id';

    protected $fillable = [
        'spb_tanggal',
        'spb_no',
        'spb_no_wo',
        'spb_section',
        'spb_pic_gmi',
        'spb_pic_ppa',
        'part_id',
        'spb_part_number',
        'spb_part_name',
        'spb_qty',
        'spb_uom',
        'spb_kode_unit',
        'spb_tipe_unit',
        'spb_brand',
        'spb_hm',
        'spb_problem_remark',
        'spb_status',
    ];

    public function part()
    {
        return $this->belongsTo(Barang::class, 'part_id', 'part_id');
    }

    public function po()
    {
        return $this->hasOne(SpbPoModel::class, 'spb_id', 'spb_id');
    }

    public function do()
    {
        return $this->hasOne(SpbDo::class, 'spb_id', 'spb_id');
    }

    public function invoice()
    {
        return $this->hasOne(SpbInvoice::class, 'spb_id', 'spb_id');
    }
}
