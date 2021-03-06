<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackEmail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    public function sendFeedback(Request $request){
        $data = $request->all();

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
                $data = [
                    'from' => 'admin@thepricecatcher.com', // We are sending it from ourselves
                    'address' => $email, // This is the address they listed
                    'message' => $message,
                    'subject' => $subject
                ];

                Mail::to('admin@thepricecatcher.com')->send(new FeedbackEmail($data));

                return response()->json(['result' => 'success', 'output' => 'yes']);
            }catch(Exception $e){
                $errors['server'] = $e;
                Log::error($e->getMessage());
                return response()->json(['result' => 'failure', 'error' => $errors]);
            }
        }else
            return response()->json(['result' => 'failure', 'error' => $errors]);

    }
}
