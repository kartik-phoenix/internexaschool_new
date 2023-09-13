<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Slider extends Model
{
    use HasFactory;

    //Getter Attributes
    public function getImageAttribute($value){
        return url('public'. Storage::url($value));
    }
}
