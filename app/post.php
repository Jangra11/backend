<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class post extends Model
{
    protected $fillable = ['user_id', 'description','uploadfile','uploadvideo','likecount','dislikecount'];

public function comments()
    {                      
        return $this->hasMany(comment::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function postimages()
    {
        return $this->belongsToMany(postimages::class);
    }
    public function postvideos()
    {
        return $this->belongsToMany(postvideo::class);
    }

}
