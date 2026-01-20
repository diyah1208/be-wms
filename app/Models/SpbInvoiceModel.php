<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpbInvoiceModel extends Model
{
    protected $table = 'tb_spb_invoice';
    protected $primaryKey = 'spb_invoice_id';

    protected $fillable = [
        'spb_id',
        'invoice_no',
        'invoice_date',
        'invoice_email_date',
        'po_pic',
    ];

    public function spb()
    {
        return $this->belongsTo(SpbModel::class, 'spb_id', 'spb_id');
    }
}
