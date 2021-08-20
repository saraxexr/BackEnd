<?php

namespace App\Http\Controllers\Brand;

use App\Http\Validation\ValidationError;
use App\Models\Brand\Brand;
use Error;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{
            $brands= Brand::all()->toArray();
            return response()->json([
                "brands" => $brands,
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
             */
        
            $rules = [
                "brandNameInArabic" => "required|string|min:2|max:30|unique:brands,brandNameInArabic|regex:/^[؀-ۿ\s]+$/",
                "brandName" => "required|string|min:2|max:30|unique:brands,brandName|regex:/^[A-Za-z\s]+$/",
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
                $error->messageInArabic= "";
                $error->statusCode = 422;
                throw $error;
            }

            /**
             * Here We create a new brand if all user inputs passed the validation.
             * We hash the password to encrypt it from stealing.
             * We convert the address field into json because the column address type is in json format
             */
            $brand = Brand::create([
                "brandNameInArabic" => $request->input("brandNameInArabic"),
                "brandName" => $request->input("brandName"),
                "field" => $request->input("field")
                ]);

             /**
                 * Here we check if there a brand inserted or not.
                 * If not inserted successfully. The system returns an error message.
                 */

        
            if($brand == null ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->validationMessage = null;
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "brand has been successfully registered",
                "messageInArabic" => "تم تسجيل العلامة تجارية بنجاح",
                "brandId" => $brand->id,
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

    public function show(Request $request)
    {
        try{
  
            /**
             * Here we check the coming brand id wheter a number or not.
             * If a number we will store it in the brandId variable.
             * If not a number we will assign the variable to zero
             */
              $brandId = intval($request->input("brandId")) ? $request->input("brandId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $brand = Brand::with("models")->where("brandId", $brandId)->first();
  
              /**
               * System checks the brand if exists or not.
               * If no brand is found in the brand table, system will return an error
               */
              if($brand == null){
                  $error = new Error(null);
                  $error->errorMessage = "There is no brand with this id";
                  $error->messageInArabic = "لا يوجد علامة تجارية مسجلة";
                  $error->statusCode = 404;
                  throw $error;
              }
  
              return response()->json([
                  "brand" => $brand,
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

     


      public function update(Request $request)
      {
          
          try{
  
            /**
             * Here we check the coming brand id wheter a number or not.
             * If a number we will store it in the brandId variable.
             * If not a number we will assign the variable to zero
             */
              $brandId = intval($request->input("brandId")) ? $request->input("brandId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $brand = Brand::where("brandId", $brandId)->first();
  
              /**
               * System checks the brand if exists or not.
               * If no brand is found in the brand table, system will return an error
               */
              if($brand == null){
                  $error = new Error(null);
                  $error->errorMessage = "There is no brand with this id";
                  $error->messageInArabic = "لا يوجد علامة تجارية مسجلة";
                  $error->statusCode = 404;
                  throw $error;
              }
  
              $rules = [
                "brandNameInArabic" => "required|string|min:2|max:30|regex:/^[؀-ۿ\s]+$/" ,
                "brandName" => "required|string|min:2|max:30|regex:/^[A-Za-z\s]+$/",
                ];
      
          
              $validator = ValidationError::validationUserInput($request, $rules);
  
             
              if($validator->fails()){
                  $error = new Error(null);
                  $error->errorMessage = $validator->errors();
                  $error->statusCode = 422;
                  throw $error;
              }
  
              
                
             
              $brand = brand::where("brandId", $brandId)->update([
                "brandNameInArabic" => $request->input("brandNameInArabic"),
                "brandName" => $request->input("brandName"),
                ]);
              
  
          
                
  
              /**
               * Here we check if the brand updated or not.
               * If not updated successfully. The system returns an error message.
               */
              if($brand == 0 ){
                  $error = new Error(null);
                  $error->errorMessage = "There is something wrong happened";
                  $error->messageInArabic = "حصل خطأ";
                  $error->statusCode = 500;
                  throw $error;
              }
  
              return response()->json([
                  "message" => "brand has been updated successfully",
                  "messageInArabic" => "تم تحديث العلامة التجارية بنجاح",
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Brand  $brand
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try{
  
            /**
             * Here we check the coming brand id wheter a number or not.
             * If a number we will store it in the brandId variable.
             * If not a number we will assign the variable to zero
             */
              $brandId = intval($request->input("brandId")) ? $request->input("brandId") : 0;
              /**
               * System will call the brand with the coming id
               */
              
              $brand = Brand::where("brandId", $brandId)->first();
  
              /**
               * System checks the brand if exists or not.
               * If no brand is found in the brand table, system will return an error
               */
              if($brand == null){
                  $error = new Error(null);
                  $error->errorMessage = "There is no brand with this id";
                  $error->messageInArabic = "لا يوجد علامة تجارية مسجلة";
                  $error->statusCode = 404;
                  throw $error;
              }
             
              $brand = brand::where("brandId", $brandId)->delete();
              

            /**
             * Here we check if the brand deleted or not.
             * If not deleted successfully. The system returns an error message.
             */
            if($brand == 0 ){
                $error = new Error(null);
                $error->errorMessage = "There is something wrong happened";
                $error->messageInArabic = "حصل خطأ";
                $error->statusCode = 500;
                throw $error;
            }

            return response()->json([
                "message" => "brand has been deleted successfully",
                "messageInArabic" => "تم حذف العلامة التجارية بنجاح",
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

