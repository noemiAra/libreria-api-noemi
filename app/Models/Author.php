<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;
    protected $table = "authors";

    protected $fillable = [
        "id", "name",
        "first_surname", "second_surname"
    ];
    public $timestamp = false;


    public function books()
    {
        return $this->belongsToMany(
            Book::class, //destino
            "authors_books", //pivote/intersección
            "authors_id", //origen
            "books_id" //destino
        );
    }
}
