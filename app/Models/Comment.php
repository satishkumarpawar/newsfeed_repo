<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    public $rules = [
        'comment.content' => 'required',
        'comment.article_id' => 'required',
        'comment.user_id' => 'required'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'article_id',
        'user_id',
        'parent_comment_id',
        'is_published',
        'published_at',
        'created_at',
        'updated_at',
        
    ];
}
