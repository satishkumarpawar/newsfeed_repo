<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Article extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $dates = ['published_at'];

    public $rules = [
        'article.heading' => 'required',
        'article.content' => 'required',
        'article.user_id' => 'required'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->with('user')->orderBy('id');
    }

  

    public function images()
    {
        return $this->belongsToMany(Image::class, 'article_image');
    }

    public function hastags()
    {
        return $this->belongsToMany(Hastag::class, 'keyword');
    }

    public function scopeNotDeleted(Builder $builder)
    {
        return $builder->where('is_deleted', 0);
    }
    

    public function scopePublished(Builder $builder)
    {
        return $builder->where('is_published', 1);
    }

   

    public function getContentAsHtmlAttribute()
    {
        $converter = new CommonMarkConverter();
        echo $converter->convertToHtml($this->content);
    }

    public function hasAuthorization(User $user)
    {
        return $this->user_id != $user->id;
    }

    public function scopeSearch(Builder $builder, $query = '')
    {
        if ($query) {
            return $builder->where(function (Builder $builder) use ($query) {
                return $builder->where('heading', 'like', "%{$query}%")
                    ->orWhere('content', 'content', "%{$query}%");
            });
        }
        return $builder;
    }

    
    public static function getPaginated(Request $request = null): LengthAwarePaginator
    {
        //$perPage = config('blog.item_per_page');
        $perPage = 10;
        if($request != null)  $perPage = $request;

        
            $articleQuery = Article::published()->notDeleted();
       
        $paginateUrl = '';
       
        return $articleQuery->with('user')
            ->latest()
            ->paginate($perPage)
            ->withPath($paginateUrl);
    }

    

}
