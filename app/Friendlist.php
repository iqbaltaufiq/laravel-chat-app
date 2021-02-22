<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friendlist extends Model
{
    protected $fillable = ['first_user', 'second_user'];
}
