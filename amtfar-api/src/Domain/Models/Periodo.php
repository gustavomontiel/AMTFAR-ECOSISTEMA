<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model {
    protected $table = 'periodos';
    public $timestamps = false;
    protected $guarded = [];
}
