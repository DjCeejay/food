<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'price',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }
}
