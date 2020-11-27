<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    public function category() {
    	return $this->belongsTo('App\Category', 'category_id')->withDefault();
    }
    public function post() {
    	return $this->belongsTo('App\Post', 'post_id')->where('status', 1)->withDefault();
    }
}
