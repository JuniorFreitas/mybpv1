<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VagaProjetoFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'feedback_id',
        'vaga_projeto_id'
    ];

    protected $casts = [
        'feedback_id' => 'int',
        'vaga_projeto_id' => 'int'
    ];

    public function VagaProjeto()
    {
        return $this->hasOne(VagaProjeto::class, 'id', 'vaga_projeto_id');
    }

    public function Feedback()
    {
        return $this->hasOne(FeedbackCurriculo::class, 'id', 'feedback_id');
    }
}
