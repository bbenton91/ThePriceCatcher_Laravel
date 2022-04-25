<?php

namespace App\Http\Controllers;

use App\Models\Emails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailSubscribeController extends Controller
{
    public function store(Request $request){
        $data = $request->all();

        // return response()->json($data, 201);

        $errors = [];

        // Grab our email and sku
        $email = $_POST['email'];
        $sku = $_POST['sku'];

        $matches = [];
        preg_match("/.+@.+\..+/", $email, $matches); // This matches cases like test@gmail.com

        if(count($matches) <= 0)
            $errors['email'] = "Please enter a valid email";

        // If no errors, call the model to insert it
        if(count($errors) <= 0){

            // Insert the email into the table
            DB::table('emails')->insertOrIgnore(
                [
                    'email'=>$email,
                    'created_at'=>now()->toDateTimeString(),
                    'updated_at'=>now()->toDateTimeString()
                ]
            );

            // Then retrieve for id
            $email = Emails::where('email', $email)->first();

            // Then we enter the sku and email id into the sku_emails table
            DB::table('sku_emails')->insertOrIgnore([
                'product_sku'=>$sku,
                'email_id'=>$email->id,
                'created_at'=>now()->toDateTimeString(),
                'updated_at'=>now()->toDateTimeString()
            ]);

            return response()->json(['result' => 'success']);
        }

        return response()->json(['result' => 'failure', 'error' => $errors]);
    }
}
