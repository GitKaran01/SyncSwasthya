<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_id',
        'name',
        'relation_with_head',

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

    public function family()
    {
        return $this->belongsTo(Family::class);
    }
}
