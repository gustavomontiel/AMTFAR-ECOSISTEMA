<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Boleta extends Model {
    protected $table = 'boletas';
    public $timestamps = false;
    protected $guarded = [];

    public function farmacia() {
        return $this->belongsTo(Farmacia::class);
    }
    public function periodo() {
        return $this->belongsTo(Periodo::class);
    }
    public function remuneraciones() {
        return $this->hasMany(BoletaRemuneracion::class, 'boleta_id');
    }
    public function conceptos() {
        return $this->hasMany(BoletaConcepto::class, 'boleta_id');
    }
}
