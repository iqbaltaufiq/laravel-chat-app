<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\PersonalChatroom;

class Chat extends Model
{
    protected $guarded = [];

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    // public function personalChatroom ()
    // {
    //     return $this->belongsTo(PersonalChatroom::class);
    // }
}
