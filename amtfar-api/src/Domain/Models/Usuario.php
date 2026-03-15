<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    public $timestamps = false; // Manejamos created_at nativamente en SQL

    protected $fillable = [
        'farmacia_id',
        'rol_id',
        'username',
        'password',
        'email',
        'nombre_completo',
        'estado'
    ];

    /**
     * El usuario pertenece a una Farmacia (si es rol Farmacia)
     */
    public function farmacia()
    {
        return $this->belongsTo(Farmacia::class, 'farmacia_id', 'id');
    }

    /**
     * El usuario tiene un Rol
     */
    public function rol()
    {
        return $this->belongsTo(Rol::class, 'rol_id', 'id');
    }
}
