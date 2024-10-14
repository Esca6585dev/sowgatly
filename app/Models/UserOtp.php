<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Twilio\Rest\Client;

class UserOtp extends Model
{
    use HasFactory;

    protected $table = 'user_otps';

    protected $casts = [
        'user_id' => 'integer',
        'otp' => 'string',
        'expire_at' => 'string',
    ];

    protected $fillable = [
        'user_id',
        'otp',
        'expire_at',
    ];

    public function sendSMS($receiverNumber)
    {
        $message = "Login OTP is " . $this->otp;

        try {

            $account_sid = getenv("TWILIO_SID");

            $auth_token = getenv("TWILIO_TOKEN");

            $twilio_number = getenv("TWILIO_FROM");

            $client = new Client($account_sid, $auth_token);

            $client->messages->create($receiverNumber, [
                'from' => $twilio_number, 
                'body' => $message
            ]);

            info('SMS Sent Successfully.');

        } catch (Exception $e) {

            info("Error: ". $e->getMessage());

        }
    }
}
