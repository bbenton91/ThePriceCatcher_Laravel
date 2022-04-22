<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackEmail;
use App\Models\Emails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class FeedbackController extends Controller
{
    public function send(Request $request){
        Log::debug("Starting send request");
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

        Log::debug("We made it here");
        if(count($errors) <= 0){
            //send email
            try{
                $data = [
                    'from' => 'admin@thepricecatcher.com',
                    'address' => 'admin@thepricecatcher.com',
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
