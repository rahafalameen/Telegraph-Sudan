<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($tag) => $tag->slug ??= Str::slug($tag->name));
    }

    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
