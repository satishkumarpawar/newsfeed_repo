<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Image;
use Image as Media;
use File;
use Validate;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class ImageController extends Controller
{
  
    public function clean($string) {
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
     
        return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
     }

    public function SaveImagePost($images_arr,$path='/',$width=0,$height=0)
    {
        if(empty($path))$path="images/profile/";
        $destinationPath="public/".$path;
        $publicPath = public_path($path);
        $publicThumbPath = public_path($path."thumb/");
        if (!file_exists($publicPath)) {
            mkdir("$publicPath");
            chmod("$publicPath", 0755);
            //mkdir($publicPath, 0775, true);
        }
        if (!file_exists($publicThumbPath)) {
            mkdir("$publicThumbPath");
            chmod("$publicThumbPath", 0755);
        }

          
        $img_ids=Array();
        if(is_array($images_arr)){
            foreach($images_arr['images'] as $imgarr){
                $caption='';
                $ext='.jpg';
                if($imgarr["image"]->isValid()){
                    $rules = Array(
                        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
                    );
                     /* $validator = Validator::make($imgarr, $rules);
                  
                    // Check to see if validation fails or passes
                    if ($validator->fails()){
                            return response()->json(['error' => $validator->errors()->getMessages()], 400);
                    } else {
                       */
                                    
                        $image = $imgarr["image"];
                        $ext=$image->getClientOriginalExtension();
                        if(isset($imgarr->caption)){
                            $caption=$imgarr->caption;
                               
                        } else {
                            $filename =  $image->getClientOriginalName();
                            $filenamearr=explode(".", $filename);
                            $caption=$filenamearr[0];
                            $ext=$filenamearr[1];
                        }
                    
                        $filename=$this->clean(strtolower($caption));
                        $filename = uniqid() .  $filename . '.' .$ext;

                        if($width!=0 && $height!=0) $img = Media::make($image->getRealPath())->resize($width, $height)->save($publicPath.$filename);
                        else $img = Media::make($image->getRealPath())->save($publicPath.$filename);
                        
                        $width=80;
                        $height=60;
                        $thumbfilename = $caption .$width.'x'.$height. '.' .$ext;
                        $img = Media::make($image->getRealPath())->resize($width, $height)->save($publicThumbPath.$thumbfilename);
                
                        if(isset($filename)){
                            $image_data = Image::create([
                                'src' => $path.$filename,
                                'thumb' => $path."thumb/".$thumbfilename,
                                'caption' => $caption,
                                'src_type' => 'internal',
                            ]);
                            
                            $img_ids[]=$image_data->id;
                        }
                   /* }*/
                }
            }
        }
        return $img_ids;
    }

    
    public function SaveImageUrl($urls=Array(),$path='',$width=0,$height=0)
    {

        //return 'true';
            // store the profile image
            /*$file = $request->file('image');
            $destinationPath = "public/images/profile";
            $filename = $user->user_name . '_' . $user->id . '.' . $file->extension();

            Storage::putFileAs($destinationPath, $file, $filename);*/
        if(empty($path))$path="images/profile/";
        $destinationPath="public/".$path;
        $publicPath = public_path("/".$path);

        if (!file_exists($publicPath)) {
            mkdir($publicPath, 0777, true);
        }

        $ext='.jpg';
        
        $img_ids=array();
        
        if(count($urls)>0){
            foreach($urls as $imgurl){    
                $name = substr($imgurl["url"], strrpos($imgurl["url"], '/') + 1);
                $namearr=explode('.',$name);
                $caption=$namearr[0];
                $ext=$namearr[1];
                $imgurl["caption"]=str_replace(" ","_",strtolower($imgurl["caption"]));
                $filename = $imgurl["caption"] . '.' .$ext;
                
                $img = Media::make($imgurl["url"])->save($publicPath.$filename);
                $width=80;
                $height=60;
                $thumbfilename = $imgurl["caption"] .'_'.$width.'x'.$height. '.' .$ext;
                $img = Media::make($imgurl["url"])->resize($width, $height)->save($publicPath.$thumbfilename);
    
                if(isset($filename)){
                    $image_data = Image::create([
                        'src' => $path.$filename,
                        'thumb' => $path.$thumbfilename,
                        'caption' => $caption,
                        'src_type' => 'internal',
                    ]);
                    $img_ids[]=$image_data->id;
                   
                 } 
            }
        }
 
        return $img_ids;
    }

    public function DeleteImage($image_ids=Array())
    {
        //if(empty($path))$path="images/profile/";
        $path="";
        $destinationPath="public/".$path;
        $publicPath = public_path("/");
        if(count($image_ids)>0){
            $image_data = Image::get()->whereIn("id",$image_ids);
            //return response()->json($image_data);
            
            foreach($image_data as $img){    
                @unlink($publicPath.$img["src"]);
                @unlink($publicPath.$img["thumb"]);     
            }
            
            try{
                //$is_drop = Image::delete()->whereIn("id",$image_ids);
                DB::table('images')->whereIn("id",$image_ids)->delete();
              } catch(Exception $e){
                return response()->json($e);
              }
          
           //Image::destroy( $image_ids );
        }
 
        return true;
    }

}
