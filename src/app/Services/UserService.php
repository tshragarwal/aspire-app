<?php

namespace App\Services;

use App\Models\User;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegistrationRequest;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * class UserService
 */

 class UserService extends BaseService
 {
     # Messages
     const REGISTER_SUCCESSFULLY = 'User register successfully';
     const INVALID_CREDENTIALS = 'Invalid credentials';
     const LOGIN_SUCCESSFUL = 'Login successfully.';
     const LOGOUT_SUCCESSFUL = 'Logout successfully';
     const INVALID_REQUEST = 'Invalid request';
     

     /**
      * Register user into system
      * @param RegistrationRequest $request
      * @return \Illuminate\Http\Response
      */
    public function register(RegistrationRequest $request): HttpResponse 
    {
        $data = $request->validated();
        
        //generating password hash
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);
        return $this->sendReponse($user, self::REGISTER_SUCCESSFULLY, Response::HTTP_CREATED);
    }

    /**
     * User to login
     * @param LoginRequest $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request): HttpResponse
    {
        $user = User::whereEmail($request->email)->first();

        if(! $user || ! Hash::check($request->password, $user->password)) {
            return $this->sendReponse([], self::INVALID_CREDENTIALS, Response::HTTP_UNAUTHORIZED, 'FAIL');
        }

        $token = $user->createToken('aspireApi')->plainTextToken;

        return $this->sendReponse(['user' => $user, 'token' => $token], self::LOGIN_SUCCESSFUL);
    }

    /**
     * User to logout
     * @return \Illuminate\Http\Response
     */
    public function logout(): HttpResponse
    {
        auth()->user()->tokens()->delete();
        return $this->sendReponse([], self::LOGOUT_SUCCESSFUL);
    }
    
 }