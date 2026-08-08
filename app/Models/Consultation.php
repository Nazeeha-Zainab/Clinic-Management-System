<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    protected $fillable = [
        'appointment_id',
        'blood_pressure',
        'heart_rate',
        'temperature',
        'weight',
        'presenting_symptoms',
        'clinical_diagnosis',
        'treatment_plan',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}
