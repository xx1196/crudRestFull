<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\ApiController;
use App\Mail\QrEmail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class QrController extends ApiController
{
    const PATH = 'images/';
    const EXTENSION = '.png';

    public function generate(Request $request)
    {
        $name = 'qr-code' . Carbon::now()->unix();
        $routeQr = self::PATH . $name . self::EXTENSION;

        \QrCode::format('png')
            ->size(200)
            ->generate($request->message, public_path($routeQr));

        $this->sendQr($request->email, $routeQr);
        return $this->showMessage();
    }

    private function sendQr($email, $qr)
    {
        Mail::to($email)->send(new QrEmail($qr));
    }
}
