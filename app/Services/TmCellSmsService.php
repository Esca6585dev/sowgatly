<?php

namespace App\Services;

use AlexandrMironov\PhpSmpp\Smpp;
use AlexandrMironov\PhpSmpp\SmppException;
use Illuminate\Support\Facades\Log; // Import the Log facade

class TmCellSmsService
{
    private $smpp;

    public function __construct()
    {
        $host = config('services.smpp.host');
        $port = config('services.smpp.port');
        $systemId = config('services.smpp.system_id'); // Correct config name
        $password = config('services.smpp.password');

        try {
            $this->smpp = new Smpp([
                'host' => $host,
                'port' => $port,
                'system_id' => $systemId, // Use the correct variable
                'password' => $password,
            ]);

            $this->smpp->connect();
            $this->smpp->bindTransmitter();

        } catch (SmppException $e) {
            Log::error("SMPP Connection Error: " . $e->getMessage()); // Use Log facade
            throw $e; // Re-throw for handling elsewhere
        }
    }

    public function sendOtp($recipient, $otp) // More descriptive variable names
    {
        $message = "Your OTP is: " . $otp; // Construct the message

        try {
            $this->smpp->sendSMS($recipient, $message);
            Log::info("OTP sent successfully to: " . $recipient); // Log success
            return true;

        } catch (SmppException $e) {
            Log::error("SMPP Send Error: " . $e->getMessage()); // Use Log facade
            return false;
        }
    }


    public function generateOtp($length = 6) { // Added OTP generation function
        $characters = '0123456789';
        $otp = '';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $otp .= $characters[rand(0, $charactersLength - 1)];
        }
        return $otp;
    }


    public function __destruct()
    {
        if (isset($this->smpp)) {
            try {
                $this->smpp->disconnect();
            } catch (SmppException $e) {
                Log::error("SMPP Disconnect Error: " . $e->getMessage());
            }
        }
    }
}