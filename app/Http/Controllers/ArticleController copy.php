<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\Image;
use Image as Media;

class ArticleController1 extends Controller
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
         try{
            $articles=Article::paginate($request->limit);
          } catch(Exception $e){
            return response()->json(['message' => 'Article not Found']);
          }
         

         foreach($articles as $k=>$article){
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
            /*
            if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img){
                    $image_ids[]=$img->image_id;
                    break;
                }
                $image_data = Image::get()->whereIn("id",$image_ids);
                $image_data_arr=Array();    
                if(count($image_data)>0){
                    foreach($image_data as $k=>$img){
                        $image_data_arr[]=$img;
                        
                    }
                    
                }
                
               $articles[$k]["images"]= Array($image_data_arr);
                
                
            }
             */

         }
        
         return response()->json($articles);
    }

    public function get_article(Request $request){
       try{
        $article=Article::get()->where("id",$request->articleId);
      } catch(Exception $e){
        return response()->json(['message' => 'Article not Found']);
      }
        if(count($article)>0){
            $article=current((Array)$article);
            $key_first=array_key_first($article);
            $article=$article[$key_first];
       
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
       /* return response()->json([
            '$_FILES' => $_FILES,
            'RequestAll' => $request->all(),
            'hasfile' => $request->hasFile('images'),
            'isValid' => $request->file('images')[0]->isValid(),
            'temp_name' =>  $request->file('images')[0]->getPathName(),
           'name' => $request->file('images')[0]->getClientOriginalName(),
           'ext' => $request->file('images')[0]->getClientOriginalExtension(),
           'size' => $request->file('images')[0]->getSize()
        ]);*/

        if($request->hasFile('images')){
            $reqdata= $request->file('images');
        }
        //if(isset($request->images) && count($request->images)>0)$images=$request->images;
        unset($request["images"]);

        $article = Article::create([
            'heading' => $request->heading,
            'content' => $request->content,
            'user_id' =>Auth::id(),
            'published_at'=>now(),
            
          ]);

        if($request->hasFile('images')){
            /*$img_urls=Array();
            foreach($images as $k=>$img_url){
                if(isset($img_url["image"])){
                    $img_urls[$k]["url"]=$img_url["image"];
                    $img_urls[$k]["caption"]=(isset($img_url["caption"])?$img_url["caption"]:$request->heading."_".$k)."_".$article["id"];
                }
            }*/
            $images = Array();
            $image_ids=Array();
            foreach($reqdata as $k=>$image){
                $id = uniqid(); 
                $images["images"][]=Array("image"=>$image,"caption"=>(isset($image->caption)?$imag->caption:$request->heading."_".$k)."_".$article["id"]);
            }
            $image_ids= ImageController::SaveImagePost($images,$path='images/article/',0,0);
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

        return response()->json(['message' => 'Article Saved Successfully']);
        // return response()->json($article);
    }

   

    public function update(Request $request){
        $reqdata = $request->all(); 
       // if(isset($request->images) && count($request->images)>0);$images=$request->images;
       if($request->hasFile('images')){
            $reqimgdata= $request->file('images');
        }
        unset($reqdata["images"]);
        //return response()->json($reqdata);
        $article= Article::where('id',$request->id)->where('user_id',Auth::id())->update($reqdata);
        //return response()->json($article);
        if($article){
            /*$image_data =  DB::table('article_image')->select()->where("article_id",$request->id)->get();
            response()->json(Array($image_data));
            if(is_array($image_data) && count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img) $image_ids[]=$img->image_id;
                if(is_array($image_ids) && count($image_ids)>0){
                   
                    DB::table('article_image')->where("article_id",$request->id)->delete();

                
                    $is_droped= ImageController::DeleteImage($image_ids);
                    
                }    
            }
            
            if(isset($images) && count($images)>0){
                $img_urls=Array();
                foreach($images as $k=>$img_url){
                    if(isset($img_url["image"])){
                        $img_urls[$k]["url"]=$img_url["image"];
                        $img_urls[$k]["caption"]=(isset($img_url["caption"])?$img_url["caption"]:$request->heading."_".$k)."_".$request->id;
                    }
                }

                $image_ids= ImageController::SaveImageUrl($img_urls,$path='images/article/',0,0);
                if(count($image_ids)>0){
                    $article_images= Array();
                    foreach($image_ids as $image_id){
                        $article_img = Array();
                        $article_img['article_id']=$request->id;
                        $article_img['image_id']=$image_id;
                        DB::table('article_image')->insert($article_img);
                    
                    }
                }
            
            }*/

            $images = Array();
            $image_ids=Array();
            foreach($reqimgdata as $k=>$image){
                $id = uniqid(); 
                $images["images"][]=Array("image"=>$image,"caption"=>(isset($image->caption)?$imag->caption:$request->heading."_".$k)."_".$request->id);
            }
            $image_ids= ImageController::SaveImagePost($images,$path='images/article/',0,0);
            if(count($image_ids)>0){
                $article_images= Array();
                foreach($image_ids as $image_id){
                    $article_img = Array();
                    $article_img['article_id']=$request->id;
                    $article_img['image_id']=$image_id;
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
    
    public function deleteImage(Request $request){
        $image_ids=$request->images;
        if(sizeof($image_ids)>0){
            $is_droped= ImageController::DeleteImage($image_ids);
            //response()->json($image_ids);
            if($is_droped && sizeof($image_ids)>0)$is_droped=  DB::table('article_images')->where("user_id",Auth::id())->whereIn('image_id',$image_ids)->delete();
           
        }
        return response()->json(['message' => 'Selected Article Images are Deleted Successfully']);
    }

    public function like(Request $request){
        $image_ids=$request->like;
        if(sizeof($image_ids)>0){
            $is_droped= ImageController::DeleteImage($image_ids);
            //response()->json($image_ids);
            if($is_droped && sizeof($image_ids)>0)$is_droped=  DB::table('article_images')->where("user_id",Auth::id())->whereIn('image_id',$image_ids)->delete();
           
        }
        return response()->json(['message' => 'Selected Article Images are Deleted Successfully']);
    }
}
