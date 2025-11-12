<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'product_id',
        'questionnaire_instance_id',
        'responses',
    ];

    protected $casts = [
        'responses' => 'array',
    ];
}
