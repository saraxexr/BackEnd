<?php

namespace App\Http\Controllers\Payment;

use App\Models\Payment\Payment;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentController extends Controller
{

    public function __construct()
    {
        $this->middleware("isAuthorized")->except(["store", "index"]);
    }
    public function index()
    {
        $logs = Payment::with(["requests", "clients:phone,email"])->orderBy("created_at", "DESC")->get();

        return response()->json([
            "logs" => $logs,
            "statusCode" => 200
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $payment = Payment::create([
                "requestId" => $request->input("requestId"),
                "status" => $request->input("status")
            ]);

            if($payment == null){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Payment has been added",
                "messageInArabic" => " تم اضافة الدفع",
                "statusCode" => 201

            ],201);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    public function refund(Request $request)
    {
        try{

            $paymentId = $request->input("paymentId");

            $payment = Payment::where("paymentId", $paymentId)->update([
                "isRefund" => "1",
                "status" => "refunded"
            ]);

            if($payment == null){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Refund has been added",
                "messageInArabic" => " تم اعادة المبلغ",
                "statusCode" => 200

            ],200);

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }


    public function destroy()
    {
        //
    }

}
