<?php

namespace App\Http\Controllers\Shipper;
use App\Http\Validation\ValidationError;
use App\Models\Shipper\Shipper;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;


class ShipperController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $requests= Shipper::all()->toArray();
            // if(count($requests) < 1){
            //     $error= new Error(null);
            //     $error->message = "No request is found";
            //     $error->messageInArabic = "لم يتم ايجاد طلب";
            //     $error->statusCode= 404;
            //     throw $error;
            // }

            return response()->json([
                "shippers" => $requests,
                "statusCode" => 200
            ], 200);
        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * Why do we use try and catch ?
         * Because it has two ways, succeed and failure
         * Succeessful part is inside try which means all processes considered to be passed and succeed.
         * catch which accepts error object to catch any error will happen in try.
         * If an error happened in try, catch will handle it and will stop executing try from working.
         */
        try{
            /**
             * This variable rule holds all validation that can be implemented to the column fields.
             * You will see regex for both nameInArabic, name. These are expressions to ensure the coming data are clean without any smbols or characters might cause a problem to the systme.
             * For example, nameInArabic => /^[؀-ۿ\s]+$/ : "احمد سالم @kknd" this will return an error because  it accepts only arabic characters.
             * For example, name => /^[A-Za-z\s]+$/ : "Jo bat <><>##" this will return an error because  it accepts only english characters.
             * For example, password => /^[A-Za-z\s]+$/ : "AZSWA\@WASS 111" this will return true because  it accepts only english characters from A-Z,a-z and any character @,$,#,etc...
             */
        
            $rules = [
                "companyNameArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "companyName" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
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
                $error->errorMessage = $validator->errors();
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

           

            /**
             * Here We create a new client if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             * We convert the address field into json because the column address type is in json format
             */
            $shipper = Shipper::create([
                "companyNameArabic" => $request->input("companyNameArabic"),
                "companyName" => $request->input("companyName"),
                 ]);


            /**
             * Here we check if there a shipper inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            if(count(array($shipper))== 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "shipper has successfully registered",
                "messageInArabic" => "تم تسجيل الشركة الشحن عميل بنجاح",
                "shipperId" => $shipper->id,
                "statusCode" => 201,
            ]);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "message" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ]);
            
        }
        
    }




    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Shipper  $shipper
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        try{

            /**
             * Here we check the coming shipper id wheter a number or not.
             * If a number we will store it in the clientId variable.
             * If not a number we will assign the variable to zero
             */
            $shipperId = intval($request->input("shipperId")) ? $request->input("shipperId") : 0;

            /**
             * System will call the shipper with the coming id
             */
            $shipper = Shipper::where("shipperId", $shipperId)->first();

            /**
             * System checks the shipper if exists or not.
             * If no shipper is found in the clients table, system will return an error
             */
            if($shipper == null){
                $error = new Error(null);
                $error->errorMessage = "There is no shipper with this id";
                $error->messageInArabic = "لا يوجد شركة شحن مسجلة";
                $error->statusCode = 404;
                throw $error;
            }

            $rules = [
                "companyNameArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "companyName" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
            ];
    
        
            $validator = ValidationError::validationUserInput($request, $rules);

           
            if($validator->fails()){
                $error = new Error(null);
                $error->errorMessage = $validator->errors();
                $error->statusCode = 422;
                throw $error;
            }

            

           
            $shipper = Shipper::where("shipperId", $shipperId)->update([
                "companyNameArabic" => $request->input("companyNameArabic"),
                "companyName" => $request->input("companyName"),
            ]);

        


            return response()->json([
                "message" => "shipper has been updated successfully",
                "messageInArabic" => "تم تحديث شركة الشحن بنجاح",
                "statusCode" => 200,
            ]);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "message" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ]);
            
    }
}


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shipper  $shipper
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{

            /**
             * Here we check the coming shipper id wheter a number or not.
             * If a number we will store it in the clientId variable.
             * If not a number we will assign the variable to zero
             */
            $shipperId = intval($request->input("shipperId")) ? $request->input("shipperId") : 0;

            /**
             * System will call the shipper with the coming id
             */
            $shipper = Shipper::where("shipperId", $shipperId)->first();

            /**
             * System checks the shipper if exists or not.
             * If no shipper is found in the clients table, system will return an error
             */
            if($shipper == null){
                $error = new Error(null);
                $error->errorMessage = "There is no shipper with this id";
                $error->messageInArabic = "لا يوجد شركة شحن مسجلة";
                $error->statusCode = 404;
                throw $error;
            }

           
            $shipper = Shipper::where("shipperId", $shipperId)->delete();

        

            return response()->json([
                "message" => "shipper has been deleted successfully",
                "messageInArabic" => "تم حذف شركة الشحن بنجاح",
                "statusCode" => 200,
            ]);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "message" => $err->messageInArabic,
                "statusCode" => $err->statusCode
            ]);
            
    }
    }
}
