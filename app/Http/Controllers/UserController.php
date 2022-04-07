<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ImageController;
use App\Models\User;
use App\Models\Image;
use Image as Media;
use Storage;
//use Image;
use File;
use Validate;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserController extends Controller
{
    public $loginAfterSignUp = true;
    
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => ['login','register']]);
       
       
    }

    public function register(Request $request)
    {
      $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
      ]);
      

      $token = auth()->login($user);
      return $this->respondWithToken($token);
    }
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if ($token = $this->guard()->attempt($credentials)) {
                $this->last_active();
                return $this->respondWithToken($token);
            } else {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        
        $user=$this->guard()->user();
        $this->last_active();
        if(!empty($user['image_id'])){
            $image_data = Image::get()->where("id",$user['image_id']);
            $image_data=current((Array)$image_data);
            $key_first=array_key_first($image_data);
            $image_data=$image_data[$key_first];
            $publicPath="/";
            $image_data["src"]=  url('/').$publicPath.$image_data["src"];
            $image_data["thumb"]=  url('/').$publicPath.$image_data["thumb"];
            $user["image"]=$image_data;
        }
            return response()->json($user);
    }

    public function profile(Request $request)
    {
        $user=User::get()->where("id",$request->userId);
       if(count($user)>0){
            $user=current((Array)$user);
            $key_first=array_key_first($user);
            $user=$user[$key_first];
            if(!empty($user['image_id'])){
                $image_data = Image::get()->where("id",$user['image_id']);
                $image_data=current((Array)$image_data);
                $key_first=array_key_first($image_data);
                $image_data=$image_data[$key_first];
                $publicPath="/";
                $image_data["src"]=  url('/').$publicPath.$image_data["src"];
                $image_data["thumb"]=  url('/').$publicPath.$image_data["thumb"];
                $user["image"]=$image_data;
            }
        }
            return response()->json($user);
    }
    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->last_active();
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    public function update(Request $request){
        /*return response()->json([
            '$_FILES' => $_FILES,
            'RequestAll' => $request->all(),
            'hasfile' => $request->hasFile('image'),
            'isValid' => $request->file('image')->isValid(),
            'temp_name' =>  $request->file('image')->getPathName(),
           'name' => $request->file('image')->getClientOriginalName(),
           'ext' => $request->file('image')->getClientOriginalExtension(),
           'size' => $request->file('image')->getSize()
        ]);*/
        $reqdata = $request->all(); 
        unset($reqdata['password']);
        if($request->hasFile('image'))  $image=$request->file('image');
        unset($reqdata['image']);
        //$reqdata['last_ip']=
        $user= User::where('id',$request->loginId)->update($reqdata);
        if ($user) {
            $user=User::get()->where("id",$request->loginId);
            if(count($user)>0){
                $user=current((Array)$user);
                $key_first=array_key_first($user);
                $user=$user[$key_first];
                if(isset($user["image_id"])){
                        $is_droped= ImageController::DeleteImage(Array($user["image_id"]));
                }
            }
           
            
           // $urls=Array(Array("url"=>$url,"caption"=>$this->guard()->user()->name . '_' . $request->loginId));
           // $image_ids= ImageController::SaveImageUrl($urls,$path='images/profile/',0,0);
          if($image->isValid()){
            $images["images"][]=Array("image"=>$image,"caption"=>$this->guard()->user()->name . '_' . $request->loginId);
            $image_ids= ImageController::SaveImagePost($images,$path='images/profile/',0,0);
            
            $reqdata['image_id']=  $image_ids[0];
        }
           
            if(!empty($reqdata['image_id'])){
                $reqdata['image_id']=$reqdata['image_id'];    
             }
             $user= User::where('id',$request->loginId)->update($reqdata);
             return response()->json(['message' => 'User Updated Successfully']);
         }else{
 
             return 'false';
 
         }
 
     }

     
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $this->save_token($token);
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ]);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
    function save_token($token){
        /*$data['token']=$token;
        User::where('id',$request->loginId)->update($data);*/
    }
    function last_active(){
        $data['last_ip']=$this->get_client_ip();
        $data['last_active']=date('Y-m-d-H:i:s');
        User::where('id',$request->loginId)->update($data);
    }
    function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }
}