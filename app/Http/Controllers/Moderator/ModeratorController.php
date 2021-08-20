<?php

namespace App\Http\Controllers\Moderator;
use App\Http\Validation\ValidationError;
use App\Models\Brand\Brand;
use App\Models\Client\Client;
use App\Models\Moderator\Moderator;
use App\Models\Supplier\Supplier;
use App\Models\User\User;
use App\Models\RRequest\Request as Rrequests;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;

class ModeratorController extends Controller
{
    public function __construct()
    {
        $this->middleware("isAuthorized")->except(["index", "lastFiveRecords" ,"allRecords", "show"]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $moderators= Moderator::with("account")->get();

            return response()->json([
                "moderators" => $moderators,
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
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                "email" =>"required|email|unique:users_info,email",
                "phone"  => "required|string|min:10|max:13|unique:users_info,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/"
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
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }


            /**
             * Here We create a new Moderator if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             */
            $moderatorAccount = User::create([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "password" => Hash::make($request->input("password")),
                "email"=> $request->input("email"),
                "phone" => "+" . $request->input("phone"),
                "userType" => "1",
            ]);

            if($moderatorAccount == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->validationMessage = null;
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }


            $moderator = Moderator::create([
                "moderatorId" => $moderatorAccount->id,
                "enterId" => uniqid($request->input("name")[0].$request->input("name")[1]."-", true),
            ]);


            /**
             * Here we check if there a Moderator inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            if($moderator == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->validationMessage = null;
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "Moderator has successfully registered",
                "messageInArabic" => "تم تسجيل المشرف بنجاح",
                "moderatorId" => $moderatorAccount->id,
                "statusCode" => 201,
            ], 201);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
        
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function show($moderatorId)
    {
        try{

            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the moderatorId variable.
             * If not a number we will assign the variable to zero
             */
            $moderatorId = intval($moderatorId) ? $moderatorId : 0;

            /**
             * System will call the client with the coming id
             */
            $moderator = Moderator::with("account")->where("moderatorId", $moderatorId)->first();

            /**
             * System checks the moderator if exists or not.
             * If no moderator is found in the moderators table, system will return an error
             */
            if($moderator == null){
                $error = new Error(null);
                $error->errorMessage = "There is no moderator with this id";
                $error->messageInArabic = "لا يوجد مشرف مسجل";
                $error->statusCode = 404;
                throw $error;
            }


            return response()->json([
                "moderator" => $moderator,
                "statusCode" => 200,
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
        try{

            /**
             * Here we check the coming Moderator id wheter a number or not.
             * If a number we will store it in the ModeratorId variable.
             * If not a number we will assign the variable to zero
             */
            $moderatorId = intval($request->input("moderatorId")) ? $request->input("moderatorId") : 0;

            /**
             * System will call the Moderator with the coming id
             */
            $moderatorAccount = User::where("uid", $moderatorId)->first();

            if($moderatorAccount == null){
                $error = new Error(null);
                $error->errorMessage = "There is no Moderator with this id";
                $error->messageInArabic = "لا يوجد مشرف مسجل";
                $error->statusCode = 404;
                throw $error;
            }


            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "phone"  => "required|string|min:10|max:13|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                "email" => "required|email",
            ];
    
        
            $validator = ValidationError::validationUserInput($request, $rules);

           
            if($validator->fails()){
                $error = new Error(null);
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic= "";
                $error->statusCode = 422;
                throw $error;
            }

            

           
            $moderatorAccount = User::where("uid", $moderatorId)->update([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "phone" => $request->input("phone"),
                "email" => $request->input("email")
            ]);

        


            return response()->json([
                "message" => "Moderator has been updated successfully",
                "messageInArabic" => "تم تحديث المشرف بنجاح",
                "statusCode" => 200,
            ],200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
    }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Moderator  $moderator
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
  
            /**
             * Here we check the coming moderator id wheter a number or not.
             * If a number we will store it in the $moderatorId variable.
             * If not a number we will assign the variable to zero
             */
              $moderatorId = intval($request->input("moderatorId")) ? $request->input("moderatorId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $moderatorAccount = User::where("uid", $moderatorId)->first();
            
            /**
             * System checks the client if exists or not.
             * If no client is found in the clients table, system will return an error
             */
            if($moderatorAccount== null){
                $error = new Error(null);
                $error->errorMessage = "There is no moderator with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }
             
              $moderatorAccount = User::where("uid", $moderatorId)->delete();

                /**
               * Here we check if the client deleted or not.
               * If not deleted successfully. The system returns an error message.
               */

              if($moderatorAccount == 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }


              $moderator = Moderator::where("moderatorId", $moderatorId)->delete();
              

            return response()->json([
                "message" => "Moderator has been deleted successfully",
                "messageInArabic" => "تم حذف المشرف بنجاح",
                "statusCode" => 200,
            ], 200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "statusCode" => $err->statusCode,
            ], $err->statusCode);
            
        }
    }  


    public function updateClient(Request $request){
        try{

            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the clientId variable.
             * If not a number we will assign the variable to zero
             */
            $clientId = intval($request->input("clientId")) ? $request->input("clientId") : 0;

            /**
             * System will call the client with the coming id
             */
            $client = Client::where("clientId", $clientId)->first();

            /**
             * System checks the client if exists or not.
             * If no client is found in the clients table, system will return an error
             */
            if($client == null){
                $error = new Error(null);
                $error->errorMessage = "There is no client with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "email" =>"required|email",
                "phone"  => "required|string|min:10|max:13|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
            ];

        
            $validator = ValidationError::validationUserInput($request, $rules);

            
            if($validator->fails()){
                $error = new Error(null);
                $error->validationMessage = $validator->errors();
                $error->errorMessage = "";
                $error->messageInArabic = "";
                $error->statusCode = 422;
                throw $error;
            }

            
            // $sanitizedAddress= ValidationError::sanitizeArray($request->input("address"));

            User::where("uid", $clientId)->update([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "email"=> $request->input("email"),
                "phone" => $request->input("phone"),
            ]);

            
            // Client::where("clientId", $clientId)->update([
            //     "address" => json_encode($sanitizedAddress),
            // ]);

    
            return response()->json([
                "message" => "Client has been updated successfully",
                "messageInArabic" => "تم تحديث العميل بنجاح",
                "statusCode" => 200,
            ],200);
            

        }catch(Error $err){
            return response()->json([
                "message" => $err->errorMessage,
                "messageInArabic" => $err->messageInArabic,
                "validationMessage" => $err->validationMessage,
                "statusCode" => $err->statusCode
            ], $err->statusCode);
            
        }
    }

    public function updateSupplier(Request $request){
        try{
            /**
              * Here we check the coming client id wheter a number or not.
              * If a number we will store it in the supplierId variable.
              * If not a number we will assign the variable to zero
              */
             $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;
 
             /**
              * System will call the client with the coming id
              */
             $supplierAccount = User::where("uid", $supplierId)->first();
 
             /**
              * System checks the supplier if exists or not.
              * If no supplier is found in the suppliers table, system will return an error
              */
             if($supplierAccount == null){
                 $error = new Error(null);
                 $error->errorMessage = "There is no supplier with this id";
                 $error->messageInArabic = "لا يوجد موّرد مسجل";
                 $error->statusCode = 404;
                 throw $error;
             }
         
             $rules = [
                 "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                 "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                 "email" =>"required|email",
                 "phone"  => "required|string|min:10|max:13|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
                 "companyInEnglish" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                 "companyInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
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
                 $error->errorMessage = "";
                 $error->messageInArabic = "";
                 $error->statusCode = 422;
                 throw $error;
             }
 
             
             $supplierAccount = User::where("uid", $supplierId)->update([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "email"=> $request->input("email"),
                "phone" => $request->input("phone")
             ]);
 
             /**
              * Here We create a new supplier if all user inputs passed the validation.
              * We hash the password to encrypt it from stealing.
              * We convert the address field into json because the column address type is in json format
              */
             $supplier = Supplier::where("supplierId", $supplierId)->update([
                 "companyInEnglish" => $request->input("companyInEnglish"),
                 "companyInArabic" => $request->input("companyInArabic"),
             ]);
 
 
 
              /**
              * System will send a response to the client to notify him the registration was succeed
              */
             return response()->json([
                 "message" => "Supplier has successfully updated",
                 "messageInArabic" => "تم تحديث الموّرد بنجاح",
                 "statusCode" => 200,
             ],200);
             
 
         }catch(Error $err){
             return response()->json([
                 "message" => $err->errorMessage,
                 "messageInArabic" => $err->messageInArabic,
                 "validationMessage" => $err->validationMessage,
                 "statusCode" => $err->statusCode
             ], $err->statusCode);
             
         }
    }


    
    public function verifySupplier(Request $request)
    {
        try{

            //System checks the supplier id
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

             //If supplier id not a number then system will return an error
            if($supplierId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

            //Fetch the supplier data
            $supplier= Supplier::where("supplierId", $supplierId)->first();

            //If there is no supplier found in the database system will return an error
            if($supplier == null) {
                $error = new Error(null);
                $error->errorMessage = "There is no supplier with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            //Accept the supplier by changing verified value to 1
            $supplier = Supplier::where("supplierId", $supplierId)->update([
                "verified" => "1"
            ]);

            //If there is no record updated system will display an error 
            if($supplier == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "supplier's account has been verified",
                "messageInArabic" => "تم تفعيل حساب المورّد",
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

    public function suspendSupplier(Request $request)
    {
        try{

            //System checks the supplier id
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

            //If supplier id not a number then system will return an error
            if($supplierId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

            //Fetch the supplier data
            $supplier= Supplier::where("supplierId", $supplierId)->first();

            //If there is no supplier found in the database system will return an error
            if($supplier == null) {
                $error = new Error(null);
                $error->errorMessage = "There is no supplier with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            //Suspend the supplier by changing verified value to 2
            $supplier = Supplier::where("supplierId", $supplierId)->update([
                "verified" => "2"
            ]);

            //If there is no record updated system will display an error
            if($supplier == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "supplier's account has been suspended",
                "messageInArabic" => "تم تجميد حساب المورّد",
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

    
    public function unverifySupplier(Request $request)
    {
        try{

            //System checks the supplier id
            $supplierId = intval($request->input("supplierId")) ? $request->input("supplierId") : 0;

             //If supplier id not a number then system will return an error
            if($supplierId == 0){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for supplier";
                $error->messageInArabic = "معرّف خاطئ للمورّد";
                $error->statusCode = 422;
                throw $error;
            }

            //Fetch the supplier data
            $supplier= Supplier::where("supplierId", $supplierId)->first();

            //If there is no supplier found in the database system will return an error
            if($supplier == null) {
                $error = new Error(null);
                $error->errorMessage = "There is no supplier with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }

            //Cancel the supplier by changing verified value to 3
            $supplier = Supplier::where("supplierId", $supplierId)->update([
                "verified" => "0"
            ]);

            //If there is no record updated system will display an error
            if($supplier == 0){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "supplier's account has been unverified",
                "messageInArabic" => "تم الغاء تفعيل حساب المورّد",
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

    public function lastFiveRecords()
    {
        $clients = Client::with("account")->orderBy("clientId", "DESC")->limit(5)->get(["clientId"]);
        $approvedSuppliers = Supplier::with("account")->where("verified", 1)->orderBy("supplierId", "DESC")->limit(5)->get(["supplierId"]);
        $unApprovedSuppliers = Supplier::with("account")->where("verified", 0)->orderBy("supplierId", "DESC")->limit(5)->get(["supplierId"]);
        $submittedRequests = Rrequests::with("clients")->where("requestStatus", 0)->orderBy("created_at", "DESC")->limit("5")->get(["requestId", "address"]);
        $completedRequests = Rrequests::with("clients")->where("requestStatus", 2)->orderBy("created_at", "DESC")->limit("5")->get(["requestId", "address"]);
        $canceledRequests = Rrequests::with("clients")->where("requestStatus", 3)->orderBy("created_at", "DESC")->limit("5")->get(["requestId", "address"]);

        return response()->json([
            "clients" => $clients,
            "approvedSuppliers" => $approvedSuppliers,
            "unApprovedSuppliers" => $unApprovedSuppliers,
            "submittedRequests" => $submittedRequests,
            "completedRequests" => $completedRequests,
            "canceledRequests" => $canceledRequests,
            "statusCode" => 200
        ], 200);
    }
    
    public function allRecords($status)
    {
        $moderator = null;
        $clients = Client::with("account")->get();
        $suppliers = Supplier::with("account")->get();
        $requests = Rrequests::with(["clients", "brands", "suppliers"])->get();
        $brands = Brand::all()->toArray();
        if($status == "admin"){
            $moderator = Moderator::with("account")->get();
        }
        return response()->json([
            "clients" => $clients,
            "suppliers" => $suppliers,
            "requests" => $requests,
            "brands" => $brands,
            "moderators" => $moderator,
            "statusCode" => 200
        ], 200);
    }

}
