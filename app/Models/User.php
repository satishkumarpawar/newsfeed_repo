<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject; #SKP
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Image;  #SKP

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    public $rules = [
        'user.name' => 'required',
        'user.email' => 'required',
        'user.password' => 'required'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'title',
        'username',
        'email_verified_at',
        'date_of_birth',
        'phone',
        'website',
        'note',
        'image_id',
        'is_active',
        'last_active',
        'token',
        'subscribed_to_news_letter',
        'last_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

   


    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }


    public function scopeActive(Builder $builder)
    {
        return $builder->where('is_active', 1);
    }

    /*public static function getSubscribedUsers()
    {
        $subscribedReadersIds = Reader::subscribed()
            ->verified()
            ->pluck('user_id');
        return self::whereIn('id', $subscribedReadersIds)->get();
    }*/


}
