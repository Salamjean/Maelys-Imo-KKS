<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetCodePasswordProprietaire extends Model
{
    protected $fillable = [
        'code',
        'email'
    ];
}
