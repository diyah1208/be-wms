<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorModel extends Model
{
    protected $table = 'vendors';   // 🔥 WAJIB
    protected $primaryKey = 'id';   // 🔥 WAJIB (karena PK = id)

    protected $fillable = [
        'vendor_no',
        'vendor_name',
        'telephone',
        'contact_name',
        'is_active'
    ];
}
