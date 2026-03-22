<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class BoletaConcepto extends Model {
    protected $table = 'boletas_conceptos';
    public $timestamps = false;
    protected $guarded = [];
}
