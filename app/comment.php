<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment extends Model
{
    protected $fillable = ['user_id','post_id','comment'];
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function posts()
    {
        return $this->belongsToMany(post::class);
    }

}
