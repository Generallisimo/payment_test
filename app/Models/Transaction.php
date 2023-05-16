<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    // перчесляем переменные которые могут массово заполнятся 
    protected $fillable = ['amount', 'desc', 'status'];
}
