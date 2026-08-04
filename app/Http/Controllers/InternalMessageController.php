<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\InternalMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InternalMessageController extends Controller
{
    //
    public function index()
    {
        $userId = Auth::id();

        $messages = InternalMessage::where('recipient_id', $userId)
            ->orWhere('sender_id', $userId)
            ->with(['sender', 'recipient'])
            ->latest()
            ->paginate(15);

        return view('messages.index', compact('messages'));
    }

    public function show($id)
    {
        $message = InternalMessage::findOrFail($id);

        if ($message->recipient_id === Auth::id()) {
            $message->update(['is_read' => true]);
        }

        return view('messages.show', compact('message'));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
        ]);

        InternalMessage::create([
            'sender_id'    => Auth::id(),
            'recipient_id' => $validated['recipient_id'],
            'subject'      => $validated['subject'],
            'body'         => $validated['body'],
        ]);

        return back()->with('success', 'Message envoyé avec succès.');
    }
}
