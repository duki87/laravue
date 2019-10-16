<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index()
    {
        return User::orderBy('created_at', 'desc')->paginate(10);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
          'name'  =>  'required|string|max:191',
          'email'  =>  'required|string|email|max:191|unique:users',
          'password'  =>  'required|string|min:6',
          'type'  =>  'required'
        ]);
        return User::create([
          'name'  =>  $request['name'],
          'email'  =>  $request['email'],
          'password'  =>  Hash::make($request['password']),
          'type'  =>  $request['type'],
          'bio'  =>  $request['bio'],
          'photo'  =>  $request['photo'] ? $request['photo'] : 'profile.png',
        ]);
    }

    public function show($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        $user = User::where(['id' => $id])->first();
        if($user) {
          $user->delete();
          return ['message' => 'User deleted.'];
        }
        return false;
    }
}
