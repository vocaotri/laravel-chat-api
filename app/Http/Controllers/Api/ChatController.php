<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    private $request;
    private $user;
    private $chat;
    private $conversation;
    private $group;

    public function __construct(Request $request, User $user, Chat $chat, Conversation $conversation, Group $group)
    {
        $this->request = $request;
        $this->user = $user;
        $this->chat = $chat;
        $this->conversation = $conversation;
        $this->group = $group;
    }

    public function chat()
    {
        $validator = Validator::make($this->request->all(), [
            'user_id' => 'required|exists:users,id',
            'content' => 'required_without_all:file_id',
            'file_id' => 'required_without_all:content'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        $data = $this->request->all();
        $data['own_id'] = Auth::id();
        $chat = $this->chat::create($data);
        $conversation = $this->conversation::create([
            'chat_id' => $chat->id,
            'user_id' => (int)$this->request->input('user_id'),
            'own_id' => Auth::id(),
            'type' => 0
        ]);
        $conversation->load('chat');
        return response(["data" => ["conversation" => $conversation]], 201);
    }

    public function chatGroup()
    {
        $validator = Validator::make($this->request->all(), [
            'group_id' => 'required|exists:groups,id',
            'content' => 'required_without_all:file_id',
            'file_id' => 'required_without_all:content'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        $data = $this->request->all();
        $data['own_id'] = Auth::id();
        $chat = $this->chat::create($data);
        $userGroup = $this->group::with('users')->find($this->request->input('group_id'));
        $userGroup = $userGroup->users ?? [];
        $conversation = new $this->conversation();
        foreach ($userGroup as $user) {
            $conversation = $this->conversation::create([
                'chat_id' => $chat->id,
                'user_id' => (int)$user->id,
                'group_id' => (int)$this->request->input('group_id'),
                'type' => 1
            ]);
        }
        $conversation->load('chat');
        return response(["data" => ["conversation" => $conversation]], 201);
    }
}
