<?php

namespace App\Http\Controllers\Auth;

use App\Services\UserService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
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

    public function __invoke()
    {
        return $this->_userService->logout();
    }
}
