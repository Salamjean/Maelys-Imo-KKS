<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetCodePasswordLocataire extends Model
{
    protected $fillable = ['code', 'email'];
}
