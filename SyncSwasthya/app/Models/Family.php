<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Family extends Model
{
    use HasFactory;

    protected $fillable = [
        'head_name',
        'village',

        'demographics',
        'lifestyle',
        'vitals',
        'medical_history',
        'addictions',
        'mental_health',
        'vaccination',
    ];

    protected $casts = [
        'demographics' => 'array',
        'lifestyle' => 'array',
        'vitals' => 'array',
        'medical_history' => 'array',
        'addictions' => 'array',
        'mental_health' => 'array',
        'vaccination' => 'array',
    ];

    public function members()
    {
        return $this->hasMany(FamilyMember::class);
    }
}
