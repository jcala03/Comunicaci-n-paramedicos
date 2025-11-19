<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'emergency_code',
        'name',
        'age',
        'gender',
        'initial_assessment',
        'paramedic_id',
        'status'
    ];

    public function vitalSigns()
    {
        return $this->hasMany(VitalSign::class);
    }

    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    public function wounds()
    {
        return $this->hasMany(Wound::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function latestVitalSigns()
    {
        return $this->hasOne(VitalSign::class)->latest();
    }
    //
}
