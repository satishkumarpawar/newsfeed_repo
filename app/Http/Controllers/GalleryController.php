<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Database\Eloquent\Builder;
use App\Models\GalleryImage;
use Illuminate\Http\Request;
use App\Models\Image;
use Image as Media;

class GalleryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['get_image']]);
    }

    public function get_image(){
        $img_url=$_GET['img_url'];
        $path=public_path()."\\images\\".str_replace("/","\\",$img_url);
        //return response()->json($path);
        return Response::download($path);
        if(file_exist($path)){
            return Response::download($path);
        } else {
            return Response::download(public_path()."images/No_image_available.jpeg");
        }

    }

    //
    public function get_gallery_images(Request $request){
        $gallery_image=Array();
         try{
            $gallery_images=GalleryImage::paginate($request->limit);
          } catch(Exception $e){
            return response()->json(['message' => 'Gallery not Found']);
          }
          $image_data =$gallery_images;
         
          
          if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $k=>$img){
                    $image_ids[]=$img->image_id;
                    unset($gallery_images[$k]);
                }
                 $image_data = Image::get()->whereIn("id",$image_ids);
                //$publicPath="/public/";
                $image_data_arr=Array();
                $publicPath="/";
                if(count($image_data)>0){
                    $image_data_arr=Array();
                    foreach($image_data as $img){
                        $img["src"]=  url('/').$publicPath.$img["src"];
                        $img["thumb"]=  url('/').$publicPath.$img["thumb"];
                        $image_data_arr[]=$img;
                    }
                  
                }

                $gallery_images["images"]=$image_data_arr;
           
                
            }
                   
         return response()->json($gallery_images);
    }

   
    public function upload(Request $request){
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

       
        //if(isset($request->images) && count($request->images)>0)$images=$request->images;
        //unset($request["images"]);
       

        if($request->hasFile('images')){
            $reqdata= $request->file('images');
            $images = Array();
            $image_ids=Array();
            foreach($reqdata as $k=>$image){
                $id = uniqid(); 
                $images["images"][]=Array("image"=>$image,"caption"=>(isset($request->caption)?$request->caption:$k)."_".$id);
                

                /*if(isset($img_url["image"])){
                    $img_urls[$k]["url"]=$img_url["image"];
                    $id = uniqid(); 
                    $img_urls[$k]["caption"]=(isset($img_url["caption"])?$img_url["caption"]:$request->heading."_".$k)."_".$id;
                }*/
            }
            $image_ids= ImageController::SaveImagePost($images,$path='images/user-gallery/'.Auth::id()."/",0,0);
               
            //$image_ids= ImageController::SaveImageUrl($img_urls,$path='images/user-gallery/'.Auth::id()."/",0,0);
            if(count($image_ids)>0){
                $gallery_image_images= Array();
                foreach($image_ids as $image_id){
                    $gallery_image_img = Array();
                    $gallery_image_img['user_id']=Auth::id();
                    $gallery_image_img['image_id']=$image_id;
                    DB::table('gallery_images')->insert($gallery_image_img);
                    /*$gallery_image = GalleryImage::create([
                        'user_id' =>Auth::id(),
                        'image_id'=>$image_id,   
                      ]);*/
                   
                }
              
                $image_data = Image::get()->whereIn("id",$image_ids);
                $gallery_image["images"]=$image_data;
              

            }
           
        }

        return response()->json(['message' => 'Gallery Images are Saved Successfully']);
        //return response()->json($gallery_image);
    }

  
    public function delete(Request $request){
        $image_ids=$request->images;
        if(sizeof($image_ids)>0){
            //$image_data= GalleryImage::whereIn('image_id',$image_ids)->where('user_id',Auth::id());
          /* $image_data =  DB::table('gallery_images')->select()->whereIn('image_id',$image_ids)->where("user_id",Auth::id())->get();
            if(count($image_data)>0){
                $image_ids=Array();
                foreach($image_data as $img)$image_ids[]=$img->image_id;
                if(count($image_ids)>0){
                   $is_droped= ImageController::DeleteImage($image_ids);
                    //if($is_droped)$is_droped= GalleryImage::delete()->whereIn('image_id',$image_ids)->where('user_id',Auth::id());
                    if($is_droped)$is_droped=  DB::table('gallery_images')->delete()->whereIn('image_id',$image_ids)->where("user_id",Auth::id());
           
                }    
            }*/
            $is_droped= ImageController::DeleteImage($image_ids);
            //response()->json($image_ids);
            //if($is_droped)$is_droped= GalleryImage::delete()->whereIn('image_id',$image_ids)->where('user_id',Auth::id());
            if($is_droped && sizeof($image_ids)>0)$is_droped=  DB::table('gallery_images')->where("user_id",Auth::id())->whereIn('image_id',$image_ids)->delete();
           
        }
        return response()->json(['message' => 'Selected Gallery Images are Deleted Successfully']);
    }
}
