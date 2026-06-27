<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Requests\LoginRequest;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
  public function __construct(
    private readonly AuthService $authService
  ) {
  }

  public function register(RegisterRequest $request): JsonResponse
  {
    $result = $this->authService->register($request->validated());

    return response()->json($result, 201);
  }

  public function login(LoginRequest $request): JsonResponse
  {
    $result = $this->authService->login($request->validated());

    return response()->json($result, 200);
  }

  public function logout(Request $request): JsonResponse
  {
    $request->user()->currentAccessToken()->delete();

    return response()->json([
      'message' => 'Logout realizado com sucesso.',
    ], 200);
  }
}
