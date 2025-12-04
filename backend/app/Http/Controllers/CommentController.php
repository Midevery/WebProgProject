<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'comment' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'comment' => $request->comment,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Comment added!',
                'comment' => $comment->load('user'),
            ], 201);
        }

        return back()->with('success', 'Comment added!');
    }

    public function destroy($id)
    {
        $comment = Comment::where('user_id', Auth::id())->findOrFail($id);
        $comment->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'message' => 'Comment deleted!',
            ]);
        }

        return back()->with('success', 'Comment deleted!');
    }

    public function apiStore(Request $request)
    {
        $request->headers->set('Accept', 'application/json');
        return $this->store($request);
    }

    public function apiDestroy($id)
    {
        request()->headers->set('Accept', 'application/json');
        return $this->destroy($id);
    }
}
