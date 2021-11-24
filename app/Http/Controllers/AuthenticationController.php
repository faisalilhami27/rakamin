<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AuthenticationController extends Controller
{
  /**
   * Login user
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function login(Request $request): \Illuminate\Http\JsonResponse
  {
    $validate = Validator::make($request->all(), [
      'phone_number' => 'required',
      'password' => 'required',
    ]);

    /* check if phone number and password is empty */
    if ($validate->fails()) {
      return ResponseFormatter::error(null, $validate->errors(), 500);
    }

    $user = User::where('phone_number', $request->phone_number)->first();

    if (is_null($user)) {
      return ResponseFormatter::error(null, "Unregistered phone number", 404);
    }

    $credentials = $request->only('phone_number', 'password');
    $attempt = Auth::attempt($credentials);

    /* check user success login or not */
    if ($attempt) {
      $token = $request->user()->createToken($user->id)->plainTextToken;
      $response = [
        'user' => $user,
        'access_token' => $token,
        'token_type' => 'Bearer',
      ];

      return ResponseFormatter::error($response, "Login successfully", 200);
    } else {
      return ResponseFormatter::error(null, "Phone number or password is wrong");
    }
  }

  /**
   * register user
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse|void
   */
  public function register(Request $request)
  {
    $data = $request->all();
    $validate = Validator::make($data, [
      'name' => 'required|regex:/^[a-zA-Z ]*$/',
      'email' => 'required|email|unique:users',
      'phone_number' => 'required|regex:/^[0-9]*$/|unique:users',
      'password' => Password::min(8)
        ->numbers()
        ->letters()
        ->symbols()
    ]);

    /* check if data is empty */
    if ($validate->fails()) {
      return ResponseFormatter::error(null, $validate->errors(), 500);
    }

    $data['password'] = Hash::make($data['password']);
    $user = User::create($data);

    if ($user) {
      return ResponseFormatter::error($user, "Register successfully", 200);
    } else {
      return ResponseFormatter::error(null, "Register failed please try again");
    }
  }

  /**
   * logout user
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function logout(Request $request): \Illuminate\Http\JsonResponse
  {
    $request->user()->currentAccessToken()->delete();
    return ResponseFormatter::error(null, "Logout successfully", 200);
  }
}
