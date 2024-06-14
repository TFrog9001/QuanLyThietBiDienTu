<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agency';

    protected $primaryKey = 'agency_id';

    public $timestamps = false;

}
