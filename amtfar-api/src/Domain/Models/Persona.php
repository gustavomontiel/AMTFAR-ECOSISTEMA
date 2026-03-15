<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model {
    protected $table = 'personas';
    public $timestamps = false;
    protected $guarded = [];
}
