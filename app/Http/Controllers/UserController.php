<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function createUser(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'unique:users|required',
                'password' => 'required',
                'name' => 'required',
                'account_type' =>  'in:INDIVIDUAL,BUSINESS|required'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->messages(),
                    'status' => 'validation-error'
                ]);
            }

            $user  = User::create([
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'name' => $request->get('name'),
                'account_type' => $request->get('account_type'),
                'api_key' => Str::random(36)
            ]);

            if ($user) {
                return Response::json([
                    'data' => $user,
                    'message' => 'New user created',
                    'status' => 'success'
                ]);
            }

            return Response::json([
                'message' => 'Failed to create user',
                'status' => 'error'
            ]);
        } catch (Exception $e) {
            logger('New User Create Error Line[52]:',[$e]);
            return Response::json([
                'message' => 'Failed to create user',
                'status' => 'error'
            ]);
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->messages(),
                    'status' => 'validation-error'
                ]);
            }

            $user = User::where('email', $request->get('email'))->first();

            if($user){
                $verifyPassword = Hash::check($request->get('password'),$user->password);

                if($verifyPassword){
                    return Response::json([
                        'data' => [
                            'api_key' => $user->api_key
                        ],
                        'message' => 'User logged in',
                        'status' => 'success'
                    ]);
                }
            }

            return Response::json([
                'message' => 'No user found',
                'status' => 'error'
            ]);
        } catch (Exception $e) {
            logger('Login Error Line[98]:',[$e]);
            return Response::json([
                'message' => 'Failed to login',
                'status' => 'error'
            ]);
        }
    }

    public function profile(Request $request): JsonResponse
    {
        try{
            $user = User::where('api_key', $request->get('api_key'))->first();

            if(!$user){
                return Response::json([
                    'message' => 'No user found',
                    'status' => 'error'
                ]);
            }

            $transactions = Transaction::where('user_id', $user->id)->get();
            
            return Response::json([
                'data' => [
                    'balance' => $user->balance,
                    'transactions' => $transactions
                ],
                'message' => 'User data found',
                'status' => 'success'
            ]);
        } catch (Exception $e) {
            logger('Login Error Line[130]:',[$e]);
            return Response::json([
                'message' => 'Failed to profile',
                'status' => 'error'
            ]);
        }
    }
}
