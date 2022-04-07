<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Article_who_like;
use Image as Media;

class ArticleController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    //
    public function get_articles(Request $request){
        
         try{
            $articles=Article::paginate($request->limit);
          } catch(Exception $e){
            return response()->json(['message' => 'Article not Found']);
          }
         

         foreach($articles as $k=>$article){
            $liked=Article_who_like::where("user_id",Auth::id())->where("article_id",$article["id"])->get();
            $article['user_liked']=(count($liked)>0?1:0);

            $image_data =  DB::table('article_image')->select()->where("article_id",$article['id'])->get();
            $articles[$k]["images"]= Array();
            if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img){
                    $image_ids[]=$img->image_id;
                    break;
                }
                $image_data = Image::get()->whereIn("id",$image_ids);
                //$publicPath="/public/";
                $publicPath="/";
                if(count($image_data)>0){
                    $image_data_arr=Array();
                    foreach($image_data as $img){
                        $img["src"]=  url('/').$publicPath.$img["src"];
                        $img["thumb"]=  url('/').$publicPath.$img["thumb"];
                        $image_ids[]=$img->image_id;
                        break;
                    }
                    $image_data=current((Array)$image_data);
                    $key_first=array_key_first($image_data);
                    $image_data=$image_data[$key_first];
                }
                $articles[$k]["images"]=Array($image_data);

                
                
            }
          

         }
        
         return response()->json($articles);
    }

    public function get_article(Request $request){
       try{
        $article=Article::get()->where("id",$request->articleId)->where("is_deleted",0);
      } catch(Exception $e){
        return response()->json(['message' => 'Article not Found']);
      }
        if(count($article)>0){
            $article=current((Array)$article);
            $key_first=array_key_first($article);
            $article=$article[$key_first];

            $liked=Article_who_like::where("user_id",Auth::id())->where("article_id",$request->articleId)->get();
            $article['user_liked']=(count($liked)>0?1:0);

            $image_data =  DB::table('article_image')->select()->where("article_id",$article['id'])->get();
            $article["images"]=Array();

            if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img)$image_ids[]=$img->image_id;
                $image_data = Image::get()->whereIn("id",$image_ids);
                //$publicPath="/public/";
                $publicPath="/";
                if(count($image_data)>0){
                    $image_data_arr=Array();
                    foreach($image_data as $k=>$img){
                        $img["src"]=  url('/').$publicPath.$img["src"];
                        $img["thumb"]=  url('/').$publicPath.$img["thumb"];
                        $image_data_arr[]=$img;
                    
                    }
                
                }
                $article["images"]=$image_data_arr;
            }

            return response()->json($article);
        } else return response()->json(['message' => 'Article not Found']);

           
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

        if(count($images)>0){
          
             foreach($images as $k=>$image_id){
               
               $article_img = Array("article_id"=>$article["id"],"image_id"=>$image_id);
               DB::table('article_image')->insert($article_img);
                   
            }
                //$image_data = Image::get()->whereIn("id",$image_ids);
                //$article["images"]=$image_data;
                     
        }

        return response()->json(['message' => 'Article Saved Successfully']);
        // return response()->json($article);
    }

   

    public function update(Request $request){
        $reqdata = $request->all(); 
       
        if(isset($request->images) && count($request->images)>0);$images=$request->images;
       
        unset($reqdata["images"]);
        //return response()->json($reqdata);
        $article= Article::where('id',$request->id)->where('user_id',Auth::id())->update($reqdata);
        //return response()->json($article);
        if($article){
           

            if(count($images)>0){
                $image_ids=$images;
                //$is_droped=  DB::table('article_image')->where("article_id",$request->id)->whereIn('image_id',$image_ids)->delete();
                $is_droped=  DB::table('article_image')->where("article_id",$request->id)->delete();
                foreach($images as $k=>$image_id){
                  $article_img = Array("article_id"=>$request->id,"image_id"=>$image_id);
                  DB::table('article_image')->insert($article_img);
                      
               }
                   //$image_data = Image::get()->whereIn("id",$image_ids);
                   //$article["images"]=$image_data;
                        
           }
                 
            return response()->json(['message' => 'Article Updated Successfully']);
        } else return response()->json(['message' => 'Article not Updated']);
    }

    public function delete(Request $request){
        $article= Article::where('id',$request->articleId)->where('user_id',Auth::id())->update(Array('is_deleted'=>1));
        
        // $is_droped=  DB::table('article_image')->where("article_id",$request->articleId)->whereIn('image_id',$image_ids)->delete();
       
        return response()->json(['message' => 'Article Deleted Successfully']);
    }
    
    public function deleteImage(Request $request){
        $image_ids=$request->images;
        if(sizeof($image_ids)>0){
            //response()->json($image_ids);
            $is_droped=  DB::table('article_image')->where("article_id",$request->id)->whereIn('image_id',$image_ids)->delete();
           
        }
        return response()->json(['message' => 'Selected Article Images are Deleted Successfully']);
    }

    public function likes(Request $request){
        $request->like;
        if($request->like){
            $wholiked = Array("user_id"=>Auth::id(),"article_id"=>$request->id,"status"=>1);
            Article_who_like::insert($wholiked);
            //DB::table('article_who_likes')->insert($wholiked);
            $article= Article::where('id',$request->id)->update(Array('like_count'=>DB::raw('like_count+1')));
            return response()->json(['message' => 'Article Greated Successfully']);
        } else {
            Article_who_like::where("user_id",Auth::id())->where("article_id",$request->id)->delete();
            //DB::table('article_who_likes')->where("user_id",Auth::id())->where("article_id",$request->id)->delete();
            $article= Article::where('id',$request->id)->update(Array('like_count'=>DB::raw('like_count-1')));

            return response()->json(['message' => 'Article unGreated Successfully']);
        }
       
    }
}
