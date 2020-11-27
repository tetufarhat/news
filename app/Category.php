<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
	/**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'slug', 'description', 'parent_id', 'status', 
    ];

    public function parent(){
        return $this->hasOne($this, 'id', 'parent_id')->withDefault();
    }

    public function totalPost()
    {
        return $this->hasMany('App\PostCategory', 'category_id', 'id')->count();
    }

    public function posts()
    {
        return $this->hasMany('App\PostCategory', 'category_id', 'id')->orderBy('post_id', 'DESC');
    }
}
