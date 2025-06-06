<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Terms extends Model
{
    protected $guarded = [];
    protected $fillable = ['name', 'message'];
}
