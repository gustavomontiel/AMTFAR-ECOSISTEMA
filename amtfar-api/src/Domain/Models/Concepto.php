<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Concepto extends Model {
    protected $table = 'conceptos';
    public $timestamps = false;
    protected $guarded = [];
}
