<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    private $group;
    private $user;
    private $request;

    public function __construct(Request $request, Group $group, User $user)
    {
        $this->group = $group;
        $this->user = $user;
        $this->request = $request;
    }

    public function store()
    {
        $idUser = Auth::id();
        $data = $this->request->all();
        if (!isset($data['name'])) {
            $data['name'] = "Group: " . (Auth::user()->name ?? "...");
        }
        $data['own_id'] = $idUser;
        $users = [$idUser];
        if ($this->request->input('user_id') && is_array($this->request->input('user_id')))
            $users = array_merge($users, $this->request->input('user_id'));
        $group = $this->group::create($data);
        $group->attach($users)->load('users');
        return response(["data" => ["group" => $group]], 201);
    }

    public function update($id)
    {
        $group = $this->group::find($id)->fill($this->request->all())->save();
        return response(["data" => ["group" => $group]], 202);
    }

    public function destroy($id)
    {
        $group = $this->group::with('users')->find($id);
        $users = array_map(function ($user) {
            return $user->id;
        }, $group);
        if (!empty($users))
            $group->detach($users);
        try {
            $group->delete();
            return response(["data" => ["group" => "Delete group success"]], 203);
        } catch (\Exception $e) {
            return response(["error" => "Delete failed , more: $e", "data" => []], 203);
        }
    }

    public function addMember()
    {
        $validator = Validator::make($this->request->all(), [
            'group_id' => 'required|exists:groups,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        if ($this->request->input('user_id') && is_array($this->request->input('user_id'))) {
            $group = $this->group::find($this->request->input('group_id'));
            $group->attach($this->request->input('user_id'))->load('users');
            return response(["data" => ["group" => $group]], 201);
        }
        return response(["error" => "Add member failed", "data" => []], 401);
    }

    public function removeMember()
    {
        $validator = Validator::make($this->request->all(), [
            'group_id' => 'required|exists:groups,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        if ($this->request->input('user_id') && is_array($this->request->input('user_id'))) {
            $group = $this->group::find($this->request->input('group_id'));
            $group->detach($this->request->input('user_id'))->load('users');
            return response(["data" => ["group" => $group]], 201);
        }
        return response(["error" => "Add member failed", "data" => []], 401);
    }
}
