<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'trivia_id','identifier','ip','mac','selected_option','is_correct','mikrotik_username','mikrotik_password','granted_minutes','offline','connection_error','attempt_order'
    ];

    protected $casts = [
    'is_correct' => 'boolean',
    'offline' => 'boolean',
    'attempt_order' => 'integer',
    ];

    public function trivia()
    {
        return $this->belongsTo(Trivia::class);
    }
}
