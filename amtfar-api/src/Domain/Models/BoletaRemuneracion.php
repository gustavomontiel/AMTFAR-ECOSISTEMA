<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class BoletaRemuneracion extends Model {
    protected $table = 'boletas_remuneraciones';
    public $timestamps = false;
    protected $guarded = [];

    public function empleado() {
        return $this->belongsTo(Empleado::class);
    }
    public function categoria() {
        return $this->belongsTo(Categoria::class);
    }
}
