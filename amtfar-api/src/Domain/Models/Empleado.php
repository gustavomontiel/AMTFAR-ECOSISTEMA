<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model {
    protected $table = 'empleados';
    public $timestamps = false;
    protected $guarded = [];

    public function persona() {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function farmacia() {
        return $this->belongsTo(Farmacia::class, 'farmacia_id');
    }

    public function categoria() {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
