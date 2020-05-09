<?php

namespace App\Http\Controllers;

use App\post;
use App\comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\postimages;
use App\postvideo;
use DB;

class PostController extends Controller
{
    public function index()
    {
        $posts = auth()->user()->posts;
 
        $tmp_data = [];
        foreach($posts as $post){
            $post['Images'] = postimages::where('post_id','=',$post->id)->get();
            $tmp_data[] = $post;
        }
        $tmpp_data = [];
        foreach($posts as $post){
            $post['videos'] = postvideo::where('post_id','=',$post->id)->get();
            $tmpp_data[] = $post;
        }
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
 
    public function show($id)
    {
        $post = auth()->user()->posts()->find($id);
 
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post with id ' . $id . ' not found'
            ], 400);
        }
        
        $post['Images'] = postimages::where('post_id','=',$post->id)->get();
        $post['videos'] = postvideo::where('post_id','=',$post->id)->get();
        return response()->json([
            'success' => true,
            'data' => $post->toArray()
        ], 200);
    }
    
 
    public function store(Request $request)
    {
        $this->validate($request, [
            'description' => 'required',
            'uploadfile' =>'required',
            'uploadfile.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'uploadvideo' => 'required',
            'uploadvideo.*' => 'mimes:mpeg,ogg,mp4,webm,3gp,mov,flv,avi,wmv,ts|max:2048',
            'likecount' => 'required',
            'dislikecount' => 'required'
        ]);


        $file_count = count($request->uploadfile);
        if($file_count > 5){
            return response()->json([
                'success' => false,
                'message' => ' upload max 5 images'
            ], 500);
        }

        $video_count = count($request->uploadvideo);
        if($video_count > 5){
            return response()->json([
                'success' => false,
                'message' => ' upload max 5 videos'
            ], 500);
        }
 
        $imageName = "";
        $videoName = "";
        $post = new Post();
        $post->description = $request->description;
        $post->uploadfile = $imageName;
        $post->uploadvideo = $videoName;
        $post->likecount = $request->likecount;
        $post->dislikecount = $request->dislikecount;
 
        if (auth()->user()->posts()->save($post)){

            $res = $post->toArray();

            if ($request->hasFile('uploadfile')) {
        
                $image = $request->uploadfile;

                $count = 1;
                foreach($image as $img){
                    $count++;
                    $imageName = time().$count.'.'.$img->getClientOriginalExtension();
    
                    $t = Storage::disk('s3')->put($imageName, file_get_contents($img), 'public');
                    $imageName = Storage::disk('s3')->url($imageName);

                    $insert_image['post_id'] = $res['id'];
                    $insert_image['uploadfile'] = $imageName;
                    postimages::create($insert_image);
                }

                

            }
            if ($request->hasFile('uploadvideo')) {
        
                $video = $request->uploadvideo;

                
                $countt = 1;
                foreach($video as $vid){
                    $countt++;
                    $videoName = time().$countt.'.'.$vid->getClientOriginalExtension();
    
                    $t = Storage::disk('s3')->put($videoName, file_get_contents($vid), 'public');
                    $videoName = Storage::disk('s3')->url($videoName);

                    $insert_video['post_id'] = $res['id'];
                    $insert_video['uploadvideo'] = $videoName;
                    postvideo::create($insert_video);
                }


                

            }

            return response()->json([
                'success' => true,
                'data' => $post->toArray()
            ]);
        }
        else{
            return response()->json([
                'success' => false,
                'message' => 'Post could not be added'
            ], 500);
        }
    }
 
    public function update(Request $request, $id)
    {
        Log::info('Update post: '.$id);
        Log::info('Request: '.$request);
        $post = auth()->user()->posts()->find($id);
 
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post with id ' . $id . ' not found'
            ], 400);
        }
 
        $updated = $post->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Post could not be updated'
            ], 500);
    }

    
 
    public function destroy($id)
    {
        $post = auth()->user()->posts()->find($id);
        postimages::where('post_id',$id)->delete();
        postvideo::where('post_id',$id)->delete();
        comment::where('post_id',$id)->delete();
        // DB:table('comments')->where('post_id',$post)->delete();
        // DB:table('postimages')->where('post_id',$post)->delete();
        // DB:table('postvideo')->where('post_id',$post)->delete();
        
        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post with id ' . $id . ' not found'
            ], 400);
        }
 
        if ($post->delete()) {
            return response()->json([
                'success' => true
            ],200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Post could not be deleted'
            ], 500);
        }
    }

    Public function like(Request $request){

        $post_id = $request->post_id;
        $previousLike = DB::table('posts')->where('id',$post_id)->first();
        $previousLike = json_decode(json_encode($previousLike),true);
       
        $previousLikeCount = $previousLike['likecount'];
       

        $newLikeCount = $previousLikeCount + 1;

        DB::table('posts')->where('id',$post_id)->update(['likecount'=>$newLikeCount]);


    }
    
    Public function dislike(Request $request){

        $post_id = $request->post_id;
        $previousDisLike = DB::table('posts')->where('id',$post_id)->first();
        $previousDisLike = json_decode(json_encode($previousDisLike),true);
       
        $previousDisLikeCount = $previousDisLike['dislikecount'];
       

        $newDisLikeCount = $previousDisLikeCount + 1;

        DB::table('posts')->where('id',$post_id)->update(['dislikecount'=>$newDisLikeCount]);


    }
    
    
}
