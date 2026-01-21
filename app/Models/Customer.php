<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_no',
        'customer_name',
        'telephone',
        'contact_name',
        'is_active'
    ];
}
