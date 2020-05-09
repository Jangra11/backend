<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class postimages extends Model
{
    protected $fillable = [
        'post_id', 'uploadfile'
    ];
    public function posts()
    {                      
        return $this->hasMany(post::class);
    }

}
