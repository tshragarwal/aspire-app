<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRegistrationRequest;
use App\Http\Requests\LoginUserRequest;
use App\Services\AuthService;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use HttpResponses;

    function __construct(
        protected AuthService $_authService
    ){}

    /**
     * Allow customer to logged in
     * 
     * @param LoginUserRequest
     * @return JsonResponse
     */
    public function login(LoginUserRequest $request) : JsonResponse {

        $data = $request->validated();

        try{
            return $this->success($this->_authService->login($data), 'Login successful');
        } catch (\Exception $e) {
            if($e->getCode() === 10404) {
                return $this->error([], $e->getMessage(), Response::HTTP_NOT_FOUND);
            }
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * To register customer
     * 
     * @param CustomerRegistrationRequest
     * @return JsonResponse
     */
    public function register(CustomerRegistrationRequest $request) : JsonResponse {

        $data = $request->validated();
        
        try{
            return $this->success($this->_authService->registerMe($data), REGISTER_SUCCESSFULLY, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout customer
     * 
     * @return JsonResponse
     */
    public function logout() : JsonResponse {
        try {
            $this->_authService->logout();
            return $this->success([], "Logout successfully");
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
