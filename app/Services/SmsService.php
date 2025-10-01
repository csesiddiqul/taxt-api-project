<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    protected $url;
    protected $token;

    public function __construct()
    {
        $this->url = 'http://api.greenweb.com.bd/api.php?json';
        $this->token = env('GREENWEB_API_TOKEN');
    }

    public function sendSms($to, $message)
    {
        $response = Http::get($this->url, [
            'to' => $to,
            'message' => $message,
            'token' => $this->token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to send SMS: ' . $response->body());
        }
        
        return $response->body();
    }

}
