<?php

namespace App\Http\Controllers;
   
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Image;
use Image as Media;
class ImageController extends Controller
{
  
    public function SaveImagePost(Request $request,$path='',$width=0,$height=0)
    {
        if(empty($path))$path="images/profile/";
        $destinationPath="public/".$path;
        $publicPath = public_path("/".$path);
        $caption='';
        $ext='.jpg';
        
        $img_id='';
        
        if(isset($request->file)){
            $this->validate($request, [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $image = $request->file('image');
            $ext=$image->getClientOriginalExtension();
            $filename = $this->guard()->user()->name . '_' . $this->guard()->user()->id . '.' .$ext;
            $caption=$filename;
            if(isset($request->caption))$caption=$request->caption;
            if($width!=0 && $height!=0) $img = Media::make($image->getRealPath())->resize($width, $height)->save($destinationPath.'/'.$filename);
            else $img = Media::make($image->getRealPath())->save($destinationPath.'/'.$filename);
            
            $width=80;
            $height=60;
            $thumbfilename = $this->guard()->user()->name . '_' . $this->guard()->user()->id .'_'.$width.'x'.$height. '.' .$ext;
            $img = Media::make($url)->resize($width, $height)->save($publicPath.$thumbfilename);
      }
        if(isset($filename)){
            $image_data = Image::create([
                'src' => $path.$filename,
                'thumb' => $path.$thumbfilename,
                'caption' => $caption,
                'src_type' => 'internal',
            ]);
            
            $img_id=$image_data->id;
         }
   
        return $img_id;
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
            foreach($image_data as $img){    
                @unlink($publicPath.$img["src"]);
                @unlink($publicPath.$img["thumb"]);
                return response()->json($publicPath.$img["src"]);
            
               
            }
            $image_data = Image::delete()->whereIn("id",$image_ids);
        }
 
        return true;
    }

}
