<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbDoModel extends Model
{
    protected $table = 'tb_spb_do';
    protected $primaryKey = 'spb_do_id';

    protected $fillable = [
        'spb_id',
        'do_no',
        'do_date',
        'do_status_part',
    ];

    public function spb()
    {
        return $this->belongsTo(SpbModel::class, 'spb_id', 'spb_id');
    }
}
