<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'rental_id',
        'rating',
        'comment',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }
}
