<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wound extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'location',
        'x_coordinate',
        'y_coordinate',
        'description',
        'severity'
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}