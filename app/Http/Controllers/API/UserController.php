<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\User;
use Storage;
use Illuminate\Support\Facades\Hash;
use Image;

class UserController extends Controller
{

    public function __construct()
    {
      $this->middleware('auth:api');
    }

    public function index()
    {
        if(Gate::allows('isAdmin') || Gate::allows('isAuthor')) {
          return User::orderBy('created_at', 'desc')->paginate(10);
        }
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
        if($this->authorize('isAdmin')) {
          $user = User::where(['id' => $id])->first();
          $this->validate($request, [
            'name'  =>  'required|string|max:191',
            'email'  =>  'required|string|email|max:191|unique:users,email,'.$user->id,
            'password'  =>  'sometimes|min:6',
            'type'  =>  'required'
          ]);
          if(!Hash::check($request->password, $user->password)) {
              $request->merge(['password' => Hash::make($request['password'])]);
          }
          $user->update($request->all());
          return ['message' => 'User updated.'];
        }
        return false;
    }

    public function destroy($id)
    {
        if($this->authorize('isAdmin')) {
          $user = User::where(['id' => $id])->first();
          if($user) {
            $user->delete();
            return ['message' => 'User deleted.'];
          }
        }
        return false;
    }

    public function profile()
    {
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        $this->validate($request, [
          'name'  =>  'required|string|max:191',
          'email'  =>  'required|string|email|max:191|unique:users,email,'.$user->id,
          'password'  =>  'sometimes|min:6'
        ]);
        if($request->photo && $request->photo != $user->photo) {
          $data = $request->photo;
          if(preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
              $data = substr($data, strpos($data, ',') + 1);
              $type = strtolower($type[1]); // jpg, png, gif
              if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                  throw new \Exception('invalid image type');
              }
              $data = base64_decode($data);
              if($data === false) {
                  throw new \Exception('base64_decode failed');
              }
          } else {
              throw new \Exception('did not match data URI with image data');
          }
          $name = md5(uniqid() . microtime()).'.'.$type;
          //file_put_contents("$name.{$type}", $data);
          if($request->photo != $user->photo) {
            Storage::disk('profile_images')->delete($user->photo);
          }
          Storage::disk('profile_images')->put($name, $data);
          $request->merge(['photo' => $name]);
        }
        if(!Hash::check($request->password, $user->password)) {
            $request->merge(['password' => Hash::make($request['password'])]);
        }
        $user->update($request->all());

        return ['message' => 'success'];
    }

    public function search()
    {
      $users = [];
      if($input = \Request::get('keywords')) {
      //if($input = \Request::get('keywords')) {
        // $users = User::where('name', 'LIKE', "%$search%")
        //                ->orWhere('email', 'LIKE', "%$search%")
        //                ->orWhere('type', 'LIKE', "%$search%")
        //                ->paginate(10);
        //                return $users;
        $users = User::where(function($query) use ($input) {
          $query->where('name', 'LIKE', "%$input%")
                ->orWhere('email', 'like', "%$input%")
                ->orWhere('type', 'like', "%$input%");
        })->paginate(10);
      } else {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
      }
      return $users;
    }

    public function invoice()
    {

    }
}
