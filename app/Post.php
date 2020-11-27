<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public function user() {
    	return $this->belongsTo('App\User', 'created_by')->withDefault();
    }

    public function categories() {
    	return $this->hasMany('App\PostCategory', 'post_id', 'id');
    }

    public function getViews() {
    	return $this->hasOne('App\PostView', 'post_id', 'id')->withDefault(['views' => 0]);
    }

    public function getImage($size)
    {
        return json_decode($this->featured_image)->$size ?? null;
    }
    public function getImage2($size)
    {
        return json_decode($this->reporter_image)->$size ?? null;
    }
}
