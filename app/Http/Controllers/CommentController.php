<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Comment;
use Illuminate\Http\Request;

class CommentController extends Controller
{
   
    public function __construct()
    {
       // $this->middleware('auth:api', ['except' => []]);
    }

    public function get_comments(Request $request){
        
        try{
           $comments=Comment::get()->where("article_id",$request->articleId)->where("is_deleted",0)->where("is_published",1);
         } catch(Exception $e){
           return response()->json(['message' => 'Comment not Found']);
         }
        
        if(count($comments)>0){
            
            $comments_arr=Array();
            foreach($comments as $k=>$comment){
                if($comment["parent_comment_id"]==0) {
                    $comments_arr[$comment["id"]]=$comment;
                    unset($comments[$k]);
                }
             }
             if(count($comments)>0){
                foreach($comments_arr as $key=>$comment_arr){
                    foreach($comments as $k=>$comment){
                        if($key==$comment["parent_comment_id"]){
                            $comments_arr[$key]["reply"]=$comment;
                            unset($comments[$k]);
                         }
                    }
                    
                }
             }
          
            return response()->json($comments_arr);

       } else return response()->json(['message' => 'Comment not Found']);
       
       
   }

    public function get_comment(Request $request){
        try{
         $comment=Comment::get()->where("id",$request->commentId)->where("is_deleted",0);
       } catch(Exception $e){
         return response()->json(['message' => 'Comment not Found']);
       }
         if(count($comment)>0){
             $comment=current((Array)$comment);
             $key_first=array_key_first($comment);
             $comment=$comment[$key_first];
 
 
             return response()->json($comment);
         } else return response()->json(['message' => 'Comment not Found']);
 
            
     }

    public function create(Request $request){
       
        //return response()->json($request);
 
         $comment = Comment::create([
             'article_id' => $request->article_id,
             'user_id' => $request->loginId,
             'parent_comment_id' => ($request->parent_comment_id?$request->parent_comment_id:0),
             'content' => $request->content,
             'published_at'=> now(),
            ]);
 
         return response()->json(['message' => 'Comment Saved Successfully']);
         // return response()->json($comment);
     }


     public function update(Request $request){
        $reqdata = $request->all(); 
        
        //return response()->json($reqdata);
        $comment= Comment::where('id',$request->id)->where('user_id',$request->loginId)->update($reqdata);
        //return response()->json($comment);              
            return response()->json(['message' => 'Comment Updated Successfully']);
      
    }

    public function delete(Request $request){
        $comment= Comment::where('id',$request->commentId)->where('user_id',$request->loginId)->update(Array('is_deleted'=>1));
        
        return response()->json(['message' => 'Comment Deleted Successfully']);
    }




    
}
