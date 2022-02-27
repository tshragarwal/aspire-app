<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegistrationRequest;
use App\Services\UserService;

class RegisterController extends Controller
{

    /**
     * @var $userService
     */
    private $_userService;

    /**
     * Register controller constructor.
     */
    public function __construct()
    {
        $this->_userService = new UserService();
    }

    /**
     * Register new user
     * @param RegistrationRequest $request
     * @return JsonResponse
     */

    public function __invoke(RegistrationRequest $request)
    {
        return $this->_userService->register($request);
    }
}
