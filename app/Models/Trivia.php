<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trivia extends Model
{
    use HasFactory;

    // El plural de "Trivia" es irregular (incontable) y Eloquent intenta usar 'trivia'.
    // Nuestra migración creó la tabla 'trivias', así que lo forzamos explícitamente.
    protected $table = 'trivias';

    protected $fillable = [
        'question','option_a','option_b','option_c','option_d','correct_option','active','valid_on'
    ];

    protected $casts = [
        'active' => 'boolean',
        'valid_on' => 'date',
    ];

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public static function getToday(): ?self
    {
        return static::query()
            ->where('active', true)
            ->when(now()->toDateString(), function($q){
                $q->where(function($q2){
                    $q2->whereNull('valid_on')->orWhereDate('valid_on', now()->toDateString());
                });
            })
            ->inRandomOrder()
            ->first();
    }
}
