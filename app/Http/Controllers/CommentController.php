<?php

namespace App\Http\Controllers;
use App\comment;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CommentController extends Controller
{
    public function index()
    {
        $comments = auth()->user()->comments;
 
        return response()->json([
            'success' => true,
            'data' => $comments
        ]);
    }
 
    public function show($id)
    {
        $comment = auth()->user()->comments()->find($id);
 
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment with id ' . $id . ' not found'
            ], 400);
        }
 
        return response()->json([
            'success' => true,
            'data' => $comment->toArray()
        ], 200);
    }
 
    public function store(Request $request)
    {
        $this->validate($request, [
            'comment' => 'required',
            'post_id' => 'required'
        ]);
 
        $comment = new comment();
        $comment->comment = $request->comment;
        $comment->post_id = $request->post_id;
        
 
        // echo "<pre>";
        // print_r($comment);
        // die("hello");
        if (auth()->user()->comments()->save($comment))
            return response()->json([
                'success' => true,
                'data' => $comment->toArray()
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Comment could not be added'
            ], 500);
    }
 
    public function update(Request $request, $id)
    {
        Log::info('Update comment: '.$id);
        Log::info('Request: '.$request);
        $comment = auth()->user()->comments()->find($id);
 
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment with id ' . $id . ' not found'
            ], 400);
        }
 
        $updated = $comment->fill($request->all())->save();
 
        if ($updated)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Comment could not be updated'
            ], 500);
    }
 
    public function destroy($id)
    {
        $comment = auth()->user()->comments()->find($id);
 
        if (!$comment) {
            return response()->json([
                'success' => false,
                'message' => 'Comment with id ' . $id . ' not found'
            ], 400);
        }
 
        if ($comment->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Comment could not be deleted'
            ], 500);
        }
    }
}
