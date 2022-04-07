<?php

namespace App\Models;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use HasFactory;

    public static function getPaginated(Request $request = null): LengthAwarePaginator
    {
        $perPage = 10;
        if($request != null)  $perPage = $request;

        
            $articleQuery = Gallery_Image::where("user_id",Auth::id())->get();
       
        $paginateUrl = '';
       
        return $articleQuery->latest()
            ->paginate($perPage)
            ->withPath($paginateUrl);
    }
}
