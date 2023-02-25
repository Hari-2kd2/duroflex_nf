<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function validatePassword(Request $request)
    {
        //You can add validation login here
        $user = DB::table('user')->where('email', '=', $request->email)
            ->first();
        //Check if the user exists
        if (!$user) {
            return redirect()->back()->withErrors(['email' => trans('User does not exist')]);
        }

        //Create Password Reset Token
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => str_random(60),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        //Get the token just created above
        $tokenData = DB::table('password_resets')
            ->where('email', $request->email)->first();

        if ($this->sendResetEmail($request->email, $tokenData->token)) {
            return redirect()->back()->with('success', trans('A reset link has been sent to your email address.'));
        } else {
            return redirect()->back()->withErrors(['error' => trans('A Network Error occurred. Please try again.')]);
        }
    }

    private function sendResetEmail($email, $token)
    {
        //Retrieve the user from the database
        $user = DB::table('user')->where('email', $email)->select('user_name', 'user_id', 'email')->first();
        $employee = DB::table('employee')->where('user_id', $user->user_id)->select('first_name', 'last_name')->first();
        //Generate, the password reset link. The token generated is embedded in the link
        $link = url('') . '/password/reset-password' . '?token=' . $token . '&email=' . urlencode($user->email);

        try {
            //Here send the link with CURL with an external email API 

            // email data
            $email_data = array(
                'token' => $token,
                'name' => $user->user_name,
                'email' => $email,
                'link' => $link,
            );
            // send email with the template
            Mail::send('emails.reset-password', $email_data, function ($message) use ($email_data) {
                $message->to($email_data['email'], $email_data['name'])
                    ->subject('Reset Password')
                    ->from('ebulientcatcoc01@gmail.com',   'Duroflex PVT LTD.' . config('services.var.org'));
            });
            return true;
        } catch (\Exception $e) {
            throw $e;
            return false;
        }
    }
}
