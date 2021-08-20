<?php

namespace App\Http\Controllers;

use App\Http\Validation\ValidationError;
use App\Models\User\User;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{

    public function addToken(Request $request){
        try{
            
            
            $uid = intval($request->input("uid")) ? $request->input("uid") : 0;

          
            $user = User::where("uid", $uid)->first();

            
            if($user == null){
                $error = new Error(null);
                $error->errorMessage = "There is no user with this id";
                $error->messageInArabic = "لا يوجد مستخدم مسجل";
                $error->statusCode = 404;
                throw $error;
            }


           User::where("uid", $uid)->update([
               "token" => $request->input("token"),
           ]);

           /**
            * System will send a response to the admin to notify him the registration was succeed
            */
           return response()->json([
               "statusCode" => 200,
           ],200);

   
       }catch(Error $err){
           return response()->json([
               "message" => $err->errorMessage,
               "messageInArabic" => $err->messageInArabic,
               "statusCode" => $err->statusCode
           ]);
       }
    }

    public function logout(Request $request){
        $uid = $request->input("uid");

        User::where('uid', $uid)->update([
            "token" => null
        ]);
    }

    public function storeRememberToken(Request $request){
        try{


            $rules = [
                "email" =>"required|email",
            ];
    
            /**
             * ValidationError is a class contains validationUserInput function which accepts 2 arguments
             * First one is the request which holds all the coming data to be validated
             * Second argument is the rules that will be matched with the coming data to validate.
             */
            $validator = ValidationError::validationUserInput($request, $rules);

            /**
             * Here we will check if some of the fields are failed, so the system will return a validation error of the specific field.
             */
            if($validator->fails()){
                $error = new Error(null);
                $error->validationMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->errorMessage= "";
                $error->statusCode = 422;
                throw $error;
            }
            
            $email = $request->input("email");
            
            $user = User::where("email", $email)->first();

            
            if($user == null){
                $error = new Error(null);
                $error->errorMessage = "There is no user with this id";
                $error->messageInArabic = "لا يوجد مستخدم مسجل";
                $error->validationMessage = null;
                $error->statusCode = 404;
                throw $error;
            }


           $user = User::where("email", $email)->update([
               "rememberToken" => $request->input("rememberToken"),
           ]);

           /**
            * System will send a response to the admin to notify him the registration was succeed
            */
           return response()->json([
               "statusCode" => 200,
           ],200);

   
       }catch(Error $err){
           return response()->json([
               "message" => $err->errorMessage,
               "messageInArabic" => $err->messageInArabic,
               "validationMessage" => $err->validationMessage,
               "statusCode" => $err->statusCode
           ]);
       }
    }
    
    public function updatePassword(Request $request){
        try{
            
            $rules = [
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
            ];

            $validator= ValidationError::validationUserInput($request, $rules);
            
            /**
             * Here we will check if some of the fields are failed, so the system will return a validation error of the specific field.
             */
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = "";
                $error->validationMessage = $validator->errors();
                $error->messageInArabic= "";
                $error->statusCode = 422;
                throw $error;
            }
            
            $rememberToken = $request->input("rememberToken");
          
            $user = User::where("rememberToken", $rememberToken)->first();
        
            
            if($user == null){
                $error = new Error(null);
                $error->errorMessage = "There is no user with this id";
                $error->messageInArabic = "لا يوجد مستخدم مسجل";
                $error->statusCode = 404;
                $error->validationMessage = null;
                throw $error;
            }



           User::where("rememberToken", $rememberToken)->update([
               "password" => Hash::make($request->input("password")),
               "rememberToken" => null
           ]);

           /**
            * System will send a response to the admin to notify him the registration was succeed
            */
           return response()->json([
               "statusCode" => 200,
               "message" => "Password has been updated",
               "messageInArabic" => "تم تحديث كلمة المرور"
           ],200);

   
       }catch(Error $err){
           return response()->json([
               "message" => $err->errorMessage,
               "messageInArabic" => $err->messageInArabic,
               "validationMessage" => $err->validationMessage,
               "statusCode" => $err->statusCode
           ]);
       }
    }

    // public function testEmail() {
    //     Mail::raw('Welcome in our website, We"re glad to be in our team !', function ($message) {
    //         $message->from('no-reply@isp.com', 'ISP');
    //         $message->sender('no-reply@isp.com', 'ISP');
    //         $message->to('abofahad.en@gmail.com', 'ABDULAZIZ');
    //         $message->subject('Welcome');
    //     });

    //     return response()->json([
    //         "message" => "message sent successfully"
    //     ], 200);
    // }
} 
