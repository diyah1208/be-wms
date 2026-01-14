<?php

// app/Models/SpbPo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbPoModel extends Model
{
    protected $table = 'tb_spb_po';
    protected $primaryKey = 'spb_po_id';

    protected $fillable = [
        'spb_id',
        'po_no',
        'so_no',
        'so_date',
    ];

    public function spb()
    {
        return $this->belongsTo(SpbModel::class, 'spb_id', 'spb_id');
    }
}
