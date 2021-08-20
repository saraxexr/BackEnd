<?php



namespace App\Http\Controllers\Client;
use App\Http\Validation\ValidationError;
use App\Models\Client\Client;
use Error;
use Illuminate\Http\Request;
use App\Models\RRequest\Request as Rrequest;
use App\Models\User\User;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;



class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */



    public function index()
    {
        $clients= Client::with(["account","requests", "brands"])->get();

        return response()->json([
            "clients" => $clients,
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
             * For example, phone => ^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/ : "+966550099881" this will return true because  it accepts only numbers and the country entery number.
             */
        
            $rules = [
                "nameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/",
                "name" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                "password" => "required|string|min:7|max:20|regex:/^[A-Za-z\s].+$/",
                "email" =>"required|email|unique:users_info,email",
                // "address" => "required",
                "phone"  => "required|string|min:10|max:13|unique:users_info,phone|regex:/^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/",
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
             * Here is the important part, Why do we need to sanitize the address rather than using regex?
             * The answer is: because the address field is an object which contains multiple fields inside of it. Regex will not go inside the object and check whether some fileds matche the pattern or not. 
             */

            // $sanitizedAddress= ValidationError::sanitizeArray($request->input("address"));


            /**
             * Here We create a new client if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             * We convert the address field into json because the column address type is in json format
             */
            $clientUser = User::create([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "password" => Hash::make($request->input("password")),
                "email"=> $request->input("email"),
                "phone" => "+".$request->input("phone")
            ]);

            //If registering a new account failed
            if($clientUser == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->validationMessage = null;
                $error->statusCode = 500;
                throw $error;
            }

            //If assigned the new account to the client failed
            $client = Client::create([
                "clientId" => $clientUser->id,
                // "address" => json_encode($sanitizedAddress)
            ]);


            /**
             * Here we check if there a client inserted or not.
             * If not inserted successfully. The system returns an error message.
             */
            // if($client == null || $clientUser == null ){
            //     $error = new Error(null);
            //     $error->errorMessage = "There is something wrong happened";
            //     $error->messageInArabic = "حصل خطأ";
            //     $error->statusCode = 500;
            //     throw $error;
            // }

             /**
             * System will send a response to the client to notify him the registration was succeed
             */
            return response()->json([
                "message" => "Client has successfully registered",
                "messageInArabic" => "تم تسجيل العميل بنجاح",
                "statusCode" => 201,
            ], 200);
            

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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show($clientId)
    {
        try{

            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the clientId variable.
             * If not a number we will assign the variable to zero
             */
            $clientId = intval($clientId) ? $clientId : 0;

            /**
             * System will call the client with the coming id
             */
            $client = Client::with("account")->where("clientId", $clientId)->first();

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


            return response()->json([
                "client" => $client,
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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        
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
            $clientAccount = User::where("uid", $clientId)->first();
            $client = Client::where("clientId", $clientId)->first();

            /**
             * System checks the client if exists or not.
             * If no client is found in the clients table, system will return an error
             */
            if($client == null || $clientAccount== null){
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

           
            $clientAccount = User::where("uid", $clientId)->update([
                "nameInArabic" => $request->input("nameInArabic"),
                "name" => $request->input("name"),
                "email"=> $request->input("email"),
                "phone" => $request->input("phone")
            ]);


            // $client = Client::where("clientId", $clientId)->update([
            //     "address" => json_encode($sanitizedAddress)
            // ]);

            


            return response()->json([
                "message" => "Client has been updated successfully",
                "messageInArabic" => "تم تحديث العميل بنجاح",
                "statusCode" => 200,
            ], 200);
            

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
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
  
            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the client$clientId variable.
             * If not a number we will assign the variable to zero
             */
              $clientId = intval($request->input("clientId")) ? $request->input("clientId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $clientAccount = User::where("uid", $clientId)->first();
              
            /**
             * System checks the client if exists or not.
             * If no client is found in the clients table, system will return an error
             */
            if($clientAccount== null){
                $error = new Error(null);
                $error->errorMessage = "There is no client with this id";
                $error->messageInArabic = "لا يوجد عميل مسجل";
                $error->statusCode = 404;
                throw $error;
            }
             
              $clientAccount = User::where("uid", $clientId)->delete();

                /**
               * Here we check if the client deleted or not.
               * If not deleted successfully. The system returns an error message.
               */
              if($clientAccount == 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }


            return response()->json([
                "message" => "Client has been deleted successfully",
                "messageInArabic" => "تم حذف العميل بنجاح",
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

    
    public function allRequests(Request $request)
    {
        try{
  
            /**
             * Here we check the coming client id wheter a number or not.
             * If a number we will store it in the client$clientId variable.
             * If not a number we will assign the variable to zero
             */
              $clientId = intval($request->input("clientId")) ? $request->input("clientId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              if($clientId == 0 ){
                $error = new Error(null);
                $error->errorMessage ="Invalid id for client";
                $error->messageInArabic = "معرّف خاطئ للعميل";
                $error->statusCode = 422;
                throw $error;
            }

                /**
               * System will call the brand with the coming id
               * The single request will carry the model as well, becasue clients want to see the models information
               */

              $selectedRequests = Rrequest::with("brands:brands.brandName,brands.brandNameInArabic")->where("clientId", $clientId)->get();
  
              /**
               * System checks the client if exists or not.
               * If no client is found in the client table, system will return an error
               */
              if(count($selectedRequests) < 1){
                  $error = new Error(null);
                  $error->errorMessage = "There is no requests related to this client";
                  $error->messageInArabic = "لا يوجد طلبات مسجلة لهذا العميل";
                  $error->statusCode = 404;
                  throw $error;
              }
             
             

            return response()->json([
                "requests" => $selectedRequests,
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

}
