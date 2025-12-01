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

        Comment::create([
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'comment' => $request->comment,
        ]);

        return back()->with('success', 'Comment added!');
    }

    public function destroy($id)
    {
        $comment = Comment::where('user_id', Auth::id())->findOrFail($id);
        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }
}
