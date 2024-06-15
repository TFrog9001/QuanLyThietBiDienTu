<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceReceipt extends Model
{
    protected $table = 'device_receipts';
    protected $primaryKey = 'receipt_id';

    protected $fillable = [
        'agency_id',
        'user_id',
        'receipt_date',
        'total_amount',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->user_id = Auth::id();
        });

        static::saving(function ($model) {
            $total = 0;
            foreach ($model->details as $detail) {
                $total += $detail->price;
            }
            $model->total_amount = $total;
        });
    }

    public function devices(): HasMany
    {
        return $this->hasMany(DeviceReceiptDetail::class, 'receipt_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DeviceReceiptDetail::class, 'receipt_id');
    }
}
