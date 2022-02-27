<?php

namespace App\Http\Controllers\Auth;

use App\Services\UserService;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;

class LoginController extends Controller
{
    /**
     * @var $userService
     */
    private $_userService;

    /**
     * Login controller constructor.
     */
    public function __construct()
    {
        $this->_userService = new UserService();
    }

    public function __invoke(LoginRequest $request)
    {
        return $this->_userService->login($request);
    }
}
