<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetCodePasswordComptable extends Model
{
    protected $fillable = ['code', 'email'];
}
