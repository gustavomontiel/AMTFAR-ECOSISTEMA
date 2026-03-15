<?php
namespace App\Domain\Models;
use Illuminate\Database\Eloquent\Model;

class Farmacia extends Model {
    protected $table = 'farmacias';
    public $timestamps = false;
    protected $guarded = [];
}
