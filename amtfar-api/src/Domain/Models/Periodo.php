<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model {
    protected $table = 'periodos';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
