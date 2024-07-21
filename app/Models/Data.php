<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    use HasFactory;

    protected $fillable = [
        'male',
        'age',
        'currentSmoker',
        'cigsPerDay',
        'BPMeds',
        'prevalentStroke',
        'prevalentHyp',
        'diabetes',
        'totChol',
        'sysBP',
        'diaBP',
        'BMI',
        'heartRate',
        'glucose',
        'Risk',

    ];

    public $timestamps = true;

}
