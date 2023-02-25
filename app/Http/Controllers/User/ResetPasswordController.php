<?php

namespace App\Http\Controllers\User;

use App\User;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    public function create(Request $request)
    {
        //Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user,email',
            'token' => 'required|exists:password_resets,token'
        ]);

        if ($validator->fails()) {
            return redirect('login')->withErrors(['error' => 'Something went wrong ! ']);
        }

        $tokenData = DB::table('password_resets')->whereToken($request->token)->latest('id')
            ->first();

        abort_if(!$tokenData, 400, 'Invalid token.......');

        // $now = new DateTime();
        // $reset = new DateTime($tokenData->created_at);
        // $valdity = $now->diff($reset);
        // $valdity = $valdity->i <= 1 ? true : false;

        // if ($valdity) {
        //     return redirect('login')->withErrors(['error' => 'Token Expired !']);
        // }

        $email = $request->email;
        $token = $request->token;

        return view('admin.reset', compact('token', 'email'));
    }


    public function store(Request $request)
    {
        //Validate input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:user,email',
            'password' => 'required|confirmed',
            'token' => 'required|exists:password_resets,token'
        ]);

        //check if payload is valid before moving on
        if ($validator->fails()) {
            return 'Please complete the form';
            // return redirect('login')->withErrors(['error' => 'Please complete the form']);
        }

        try {
            $password = $request->password;
            // Validate the token
            $tokenData = DB::table('password_resets')
                ->where('token', $request->token)->first();
            // Redirect the user back to the password reset request form if the token is invalid
            if (!$tokenData)
                return 'Invalid token';
            // return redirect('login')->withErrors(['error', 'Invalid token']);

            $user = User::where('email', $tokenData->email)->first();

            // Redirect the user back if the email is invalid
            if (!$user)
                return 'Email not found';
            // return redirect('login')->withErrors(['error' => 'Email not found']);

            //Hash and update the new password
            $user->password = Hash::make($password);
            $user->update(); //or $user->save();

            //login the user immediately they change password successfully
            Auth::login($user);

            //Delete the token
            DB::table('password_resets')->where('email', $user->email)
                ->delete();
            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }



        // Send Email Reset Success Email
        // if ($this->sendSuccessEmail($tokenData->email)) {
        //     return 'success';
        //     // return view('admin.dashboard');
        // } else {
        //     return 'A Network Error occurred. Please try again';
        //     // return redirect('login')->withErrors(['error' => trans('A Network Error occurred. Please try again.')]);
        // }
    }

    private function sendSuccessEmail($email)
    {
        //Retrieve the user from the database
        $user = DB::table('user')->where('email', $email)->select('user_name', 'user_id', 'email')->first();
        $employee = DB::table('employee')->where('user_id', $user->user_id)->select('first_name', 'last_name')->first();

        try {
            //Here send the link with CURL with an external email API 

            // email data
            $email_data = array(
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'email' => $email,
                'message' => 'Password Reset Successfully',
            );

            // send email with the template
            Mail::send('emails.message', $email_data, function ($message) use ($email_data) {
                $message->to($email_data['email'])
                    ->subject('Reset Password')
                    ->from('ebulientcatcoc01@gmail.com',   'Duroflex PVT LTD.');
            });
            return true;
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
    }
}
