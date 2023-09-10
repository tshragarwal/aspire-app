<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthService {
    public function registerMe(array $data) : User {
        try{
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);
            return $user;
        } catch(QueryException $e) {
            report($e);
            throw new \Exception(INTERNAL_SERVER_ERROR);
        }
    }

    public function login(array $data) : array {
        try{
            if(!Auth::attempt($data)) {
                throw new \Exception(INVALID_CREDENTIALS, 10404);
            }
            $user = User::where('email', $data['email'])->first();
            return [
                'user' => $user,
                'token' => $user->createToken('API token '. $user->name)->plainTextToken
            ];
        } catch(QueryException $e) {
            report($e);
            throw new \Exception(INTERNAL_SERVER_ERROR);
        }
    }

    public function logout() : bool {
        try{
            $currentAccessToken = Auth::user()->currentAccessToken();
            if($currentAccessToken != null) {
                $currentAccessToken->delete();
            }
            return true;
        } catch(\Exception $e) {
            report($e);
            throw new \Exception(INTERNAL_SERVER_ERROR);
        }
    }
}