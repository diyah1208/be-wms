<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockModel extends Model
{
    protected $table = 'tb_stock';
    protected $primaryKey = 'stk_id';

    protected $fillable = [
        'part_id',
        'stk_lokasi',
        'stk_qty',
        'stk_min',
        'stk_max',
    ];
}
