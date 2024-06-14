<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceType extends Model
{
    protected $table = 'device_types';

    protected $primaryKey = 'device_type_id';

    public $timestamps = false;

}
