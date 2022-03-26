<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\Image;
use Image as Media;

class ArticleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    //
    public function get_articles(Request $request){
        //$articles=Article::get();//->where("id",$request->articleId);
        //$article=$article[0];
       /* if(!empty($article['image_id'])){
            $image_data = Image::get()->where("id",$article['image_id']);
            $article["image"]=$image_data;
        }*/

         //$articles->paginate(config('blog.item_per_page'));
         $articles=Article::paginate($request->limit);

         foreach($articles as $k=>$article){
            $image_data =  DB::table('article_image')->select()->where("article_id",$article['id'])->get();
        
            if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img){
                    $image_ids[]=$img->image_id;
                    break;
                }
                $image_data = Image::get()->whereIn("id",$image_ids);
                $articles[$k]["images"]=$image_data;
            }

         }
        
         return response()->json($articles);
    }

    public function get_article(Request $request){
        $article=Article::get()->where("id",$request->articleId);
        if(count($article)>0){
            $article=current((Array)$article);
            $key_first=array_key_first($article);
            $article=$article[$key_first];
        }
        $image_data =  DB::table('article_image')->select()->where("article_id",$article['id'])->get();
        
        if(count($image_data)>0){
            $image_ids=Array();
            foreach($image_data as $img)$image_ids[]=$img->image_id;
            $image_data = Image::get()->whereIn("id",$image_ids);
            $article["images"]=$image_data;
        }

       /* if(!empty($article['image_id'])){
            $image_data = Image::get()->where("id",$article['image_id']);
            $article["image"]=$image_data;
        }*/
            return response()->json($article);
    }

    public function create(Request $request){
        if(isset($request->images) && count($request->images)>0)$images=$request->images;
        unset($request["images"]);

        $article = Article::create([
            'heading' => $request->heading,
            'content' => $request->content,
            'user_id' =>Auth::id(),
            'published_at'=>now(),
            
          ]);

        if(isset($images) && count($images)>0){
            $img_urls=Array();
            foreach($images as $k=>$img_url){
                if(isset($img_url["image"])){
                    $img_urls[$k]["url"]=$img_url["image"];
                    $img_urls[$k]["caption"]=(isset($img_url["caption"])?$img_url["caption"]:$request->heading."_".$k)."_".$article["id"];
                }
            }

            $image_ids= ImageController::SaveImageUrl($img_urls,$path='images/article/',0,0);
            if(count($image_ids)>0){
                $article_images= Array();
                foreach($image_ids as $image_id){
                    $article_img = Array();
                    $article_img['article_id']=$article["id"];
                    $article_img['image_id']=$image_id;
                    DB::table('article_image')->insert($article_img);
                   
                }
              
                $image_data = Image::get()->whereIn("id",$image_ids);
                $article["images"]=$image_data;
              

            }
           
        }

        //return response()->json(['message' => 'Article Saved Successfully']);
         return response()->json($article);
    }

   

    public function update(Request $request){
       
        $reqdata = $request->all(); 
        if(isset($reqdata->images) && count($reqdata->images)>0)$images=$reqdata->images;
        unset($reqdata["images"]);
        $article= Article::where('id',$request->id)->where('user_id',Auth::id())->update($reqdata);
       
        $image_data =  DB::table('article_image')->select()->where("article_id",$request->id)->get();
        
        if(count($image_data)>0){
            $image_ids=Array();
            foreach($image_data as $img)$image_ids[]=$img->image_id;
            if(count($image_ids)>0){
                $is_droped= ImageController::DeleteImage($image_ids);
            }    
        }

        if(isset($images) && count($images)>0){
            $img_urls=Array();
            foreach($images as $k=>$img_url){
                if(isset($img_url["image"])){
                    $img_urls[$k]["url"]=$img_url["image"];
                    $img_urls[$k]["caption"]=(isset($img_url["caption"])?$img_url["caption"]:$request->heading."_".$k)."_".$article["id"];
                }
            }

            $image_ids= ImageController::SaveImageUrl($img_urls,$path='images/article/',0,0);
            if(count($image_ids)>0){
                $article_images= Array();
                foreach($image_ids as $image_id){
                    $article_img = Array();
                    $article_img['article_id']=$article["id"];
                    $article_img['image_id']=$image_id;
                    DB::table('article_image')->insert($article_img);
                   
                }
             }
           
        }
       
        return response()->json(['message' => 'Article Updated Successfully']);
    }

    public function delete(Request $request){
        $article= Article::where('id',$request->articleId)->where('user_id',Auth::id())->update(Array('is_deleted'=>1));
        
        /*$image_data =  DB::table('article_image')->select()->where("article_id",$article['id'])->get();
        
        if(count($image_data)>0){
            $image_ids=Array();
            foreach($image_data as $img)$image_ids[]=$img->image_id;
            if(count($image_ids)>0){
                $is_droped= ImageController::DeleteImage($image_ids);
            }    
        }*/
        return response()->json(['message' => 'Article Deleted Successfully']);
    }
}
