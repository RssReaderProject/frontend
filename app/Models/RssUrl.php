<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RssUrl extends Model
{
    use HasFactory;
    
    protected $fillable = ['url'];
}
