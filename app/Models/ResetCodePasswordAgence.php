<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetCodePasswordAgence extends Model
{
    protected $fillable = ['code', 'email'];
}
