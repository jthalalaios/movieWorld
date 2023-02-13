<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users_movies_likes()
    {
        return $this->belongsToMany(User::class, 'movie_user_likes', 'movie_id', 'user_id')->withTimestamps();
    }

    public function users_movies_hates()
    {
        return $this->belongsToMany(User::class, 'movie_user_hates', 'movie_id', 'user_id')->withTimestamps();
    }
}
