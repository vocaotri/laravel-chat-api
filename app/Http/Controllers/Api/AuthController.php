<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private $user;
    private $request;

    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    public function register()
    {
        $validator = Validator::make($this->request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'birthday'=>'required|date',
            'gender' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        $user = User::create($this->request->all());
        $token = $user->createToken('Register_token')->accessToken;
        return response(["data" => ["user" => $user, "token" => $token]], 201);
    }

    public function login()
    {
        $validator = Validator::make($this->request->all(), [
            'email' => 'required|exists:users',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'error' => "Parameter invalid",
                'data' => $validator->errors()
            ], 422);
        }
        if (Auth::attempt(['email' => $this->request->input('email'), 'password' => $this->request->input('password')])) {
            $token = Auth::user()->createToken('Login_token')->accessToken;
            return response(["data" => ["user" => Auth::user(), "token" => $token]]);
        }
        return response(['error'=>"Email or password not match"],400);
    }
}
