<?php

namespace App\Http\Middleware;

use App\Models\User\User;
use Closure;
use Illuminate\Http\Request;

class CheckAuthorize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $uid = $request->input("uid");
        $user = User::where("uid", $uid)->first();
        if($user == null){
            return response()->json([
                "message" => "User is not found",
                "messageInArabic" => "المستخدم غير موجود",
                "statusCode" => 404
            ], 404);
        }
        if($request->header('Authorization') != $user->token){
            return response()->json([
                "message" => "User is not authorized",
                "messageInArabic" => "المستخدم غير مصرّح له",
                "statusCode" => 421
            ], 421);
        }
        return $next($request);
    }
}
