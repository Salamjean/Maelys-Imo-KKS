<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetCodePasswordCommercial extends Model
{
    protected $fillable = ['code', 'email'];
}
