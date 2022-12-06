<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Editorial extends Model
{
    use HasFactory;

    protected $table = "editorials";

    protected $fillable = ["id", "name"];
    public $timestamp = false;

    public function books(){
        return $this->hasMany(Book::class, 'id');
    }
}
