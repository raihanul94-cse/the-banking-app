<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function postDeposit(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'amount' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->messages(),
                    'status' => 'validation-error'
                ]);
            }
            
            $transaction = Transaction::create([
                'user_id' => $request->get('user_id'),
                'transaction_type' => Transaction::TRANSACTION_TYPE_DEPOSIT,
                'amount' => $request->get('amount'),
                'date' => Carbon::now()
            ]);

            if ($transaction) {
                User::where('id', $request->get('user_id'))->increment('balance', $request->get('amount'));

                return Response::json([
                    'data' => $transaction,
                    'message' => 'New deposit created',
                    'status' => 'success'
                ]);
            }

            return Response::json([
                'message' => 'Failed to create deposit',
                'status' => 'error'
            ]);
        } catch (Exception $e) {
            logger('Post Deposit Error Line[28]:',[$e]);
            return Response::json([
                'message' => 'Failed to post deposit',
                'status' => 'error'
            ]);
        }
    }

    public function getDeposit(Request $request): JsonResponse
    {
        try{
            $user = User::where('api_key', $request->get('api_key'))->first();

            if(!$user){
                return Response::json([
                    'message' => 'No user found',
                    'status' => 'error'
                ]);
            }

            $deposits = Transaction::where('user_id', $user->id)->where('transaction_type', Transaction::TRANSACTION_TYPE_DEPOSIT)->get();

            return Response::json([
                'data' => $deposits,
                'message' => 'User all deposits',
                'status' => 'success'
            ]);         
        } catch (Exception $e) {
            logger('Login Error Line[55]:',[$e]);
            return Response::json([
                'message' => 'Failed to get deposit',
                'status' => 'error'
            ]);
        }
    }    

    public function getWithdrawal(Request $request): JsonResponse
    {
        try{
            $user = User::where('api_key', $request->get('api_key'))->first();

            if(!$user){
                return Response::json([
                    'message' => 'No user found',
                    'status' => 'error'
                ]);
            }

            $withdrawals = Transaction::where('user_id', $user->id)->where('transaction_type', Transaction::TRANSACTION_TYPE_WITHDRAWAL)->get();

            return Response::json([
                'data' => $withdrawals,
                'message' => 'User all withdrawal',
                'status' => 'success'
            ]);         
        } catch (Exception $e) {
            logger('Login Error Line[55]:',[$e]);
            return Response::json([
                'message' => 'Failed to get withdrawal',
                'status' => 'error'
            ]);
        }
    }

    public function postWithdrawal(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'amount' => 'required'
            ]);

            if ($validator->fails()) {
                return Response::json([
                    'errors' => $validator->messages(),
                    'status' => 'validation-error'
                ]);
            }

            $user = User::where('id', $request->get('user_id'))->first();

            if(!$user){
                return Response::json([
                    'message' => 'No user found',
                    'status' => 'error'
                ]);
            }

            $fee = 0;

            if($user->account_type == User::ACCOUNT_TYPE_BUSINESS){
                if($user->balance > 50000){
                    $fee = (0.015 / 100) * (float) $request->get('amount');
                }else{
                    $fee = (0.025 / 100) * (float) $request->get('amount');
                }
            }else if($user->account_type == User::ACCOUNT_TYPE_INDIVIDUAL){
                if ($request->get('amount') <= 1000) {
                    $fee = 0;
                } else {
                    $remainingAmount = $$request->get('amount') - 1000;
                    $fee = (0.015 / 100) * $remainingAmount;

                    $today = new Carbon();
                    if($today->dayOfWeek != Carbon::FRIDAY){
                        $fee = (0.015 / 100) * (float) $request->get('amount');   
                    }else{
                        $fee = 0;
                    }
                }
            }
            
            $transaction = Transaction::create([
                'user_id' => $request->get('user_id'),
                'transaction_type' => Transaction::TRANSACTION_TYPE_WITHDRAWAL,
                'amount' => $request->get('amount'),
                'date' => Carbon::now(),
                'fee' => $fee
            ]);

            if ($transaction) {
                User::where('id', $request->get('user_id'))->decrement('balance', ((float) $request->get('amount')) + $fee);

                return Response::json([
                    'data' => $transaction,
                    'message' => 'New withdrawal created',
                    'status' => 'success'
                ]);
            }

            return Response::json([
                'message' => 'Failed to create withdrawal',
                'status' => 'error'
            ]);
        } catch (Exception $e) {
            logger('Post withdrawal Error Line[28]:',[$e]);
            return Response::json([
                'message' => 'Failed to post withdrawal',
                'status' => 'error'
            ]);
        }
    }
}
