<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class RouterDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name','host','port','ssl','timeout','username','password','enabled','is_default','notes'
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'is_default' => 'boolean',
    'port' => 'integer',
    'ssl' => 'boolean',
    'timeout' => 'integer',
    ];

    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Crypt::encryptString($value);
    }

    public function getPasswordAttribute($value): string
    {
        try {
            $dec = Crypt::decryptString($value);
            return trim($dec);
        } catch(\Throwable $e) {
            \Log::warning('No se pudo descifrar password de RouterDevice ID '.$this->id.' (posible cambio de APP_KEY)');
            return '';
        }
    }

    public function scopeEnabled($q) { return $q->where('enabled', true); }
}
