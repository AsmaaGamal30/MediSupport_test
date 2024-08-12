<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Services\User\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index()
    {
        return response()->json($this->userService->getPaginatedUsers());
    }

    public function getFirstEightUsers()
    {
        try {
            $this->authorizeAdmin();
            $result = $this->userService->getFirstEightUsers();

            return response()->json($result['data'], $result['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function getUsersCount()
    {
        try {
            $this->authorizeAdmin();
            $result = $this->userService->getUsersCount();

            return response()->json($result['data'], $result['status']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    private function authorizeAdmin()
    {
        if (!auth()->guard('admin')->user()) {
            return $this->error('You Are Not authorized', 401);
        }
    }
}
