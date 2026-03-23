<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'id';
    public $timestamps = false;
    
    protected $fillable = ['nombre', 'descripcion'];

    /**
     * Users/Roles relationships
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'rol_permisos', 'permiso_id', 'rol_id');
    }
}
