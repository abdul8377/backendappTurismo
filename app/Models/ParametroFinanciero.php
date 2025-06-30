<?php
// app/Models/ParametroFinanciero.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParametroFinanciero extends Model
{
    use HasFactory;

    protected $table      = 'parametros_financieros';
    protected $primaryKey = 'clave';   // la PK es la propia clave
    public    $incrementing = false;
    protected $keyType    = 'string';
    public    $timestamps = false;     // normalmente son datos “de catálogo”

    protected $fillable = ['clave', 'valor'];

    /* ─────────────── Helpers ─────────────── */

    /** Obtiene el valor (string) o devuelve default */
    public static function valor(string $clave, $default = null): ?string
    {
        return optional(static::find($clave))->valor ?? $default;
    }

    /** Devuelve el valor como float (útil para porcentajes) */
    public static function float(string $clave, float $default = 0): float
    {
        return (float) static::valor($clave, $default);
    }
}
