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
        $this->middleware('auth:api', ['except' => ['login','register']]);
       
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
        $reqdata = $request->all(); 
        unset($reqdata['password']);
        $url=$reqdata['image'];
        unset($reqdata['image']);
        //$reqdata['last_ip']=
        $user= User::where('id',$this->guard()->user()->id)->update($reqdata);
        if ($user) {
            $user=User::get()->where("id",$this->guard()->user()->id);
            if(count($user)>0){
                $user=current((Array)$user);
                $key_first=array_key_first($user);
                $user=$user[$key_first];
                if(isset($user["image_id"])){
                        $is_droped= ImageController::DeleteImage(Array($user["image_id"]));
                }
            }
           
            
            $urls=Array(Array("url"=>$url,"caption"=>$this->guard()->user()->name . '_' . $this->guard()->user()->id));
            $image_ids= ImageController::SaveImageUrl($urls,$path='images/profile/',0,0);
            $reqdata['image_id']=  $image_ids[0];
            if(!empty($reqdata['image_id'])){
                $reqdata['image_id']=$reqdata['image_id'];
                $user= User::where('id',$this->guard()->user()->id)->update($reqdata);
                return response()->json(['message' => 'User Updated Successfully']);
             }
 
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
        User::where('id',$this->guard()->user()->id)->update($data);*/
    }
    function last_active(){
        $data['last_ip']=$this->get_client_ip();
        $data['last_active']=date('Y-m-d-H:i:s');
        User::where('id',$this->guard()->user()->id)->update($data);
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