<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'devices';

    protected $primaryKey = 'device_id';

    protected $fillable = [
        'serial_number',
        'device_name',
        'device_type_id',
        'warranty_expiry',
    ];

    public $timestamps = false;

}
