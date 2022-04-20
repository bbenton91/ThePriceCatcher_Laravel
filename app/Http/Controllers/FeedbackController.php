<?php

namespace App\Http\Controllers;

use App\Models\Emails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeedbackController extends Controller
{
    public function send(Request $request){
        $data = $request->all();

        // file_put_contents("log.txt", "I DID");
        $errors = [];

        $email = $_POST['email'];
        $subject = $_POST['subject'];
        $message = $_POST['message'];

        $matches = [];
        preg_match("/@/", $email, $matches);

        if(count($matches) <= 0)
            $errors['email'] = "Please enter a valid email";

        if(strlen($subject) <= 0)
            $errors['subject'] = "Please enter a subject";

        if(strlen($message) < 10)
            $errors['message'] = "Please enter more than 10 characters";

        if(count($errors) <= 0){
            //send email
            try{
                $result = @mail("support@thepricecatcher.com", "From $email: $subject", $message);
                return response()->json(['result' => 'success', 'output' => $result]);
            }catch(Exception $e){
                $errors['server'] = $e;
                echo json_encode($errors);
            }
        }else
            return response()->json(['result' => 'failure', 'error' => $errors]);

    }
}
