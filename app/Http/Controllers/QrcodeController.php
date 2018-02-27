<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Crypt;

class QrcodeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
//        $this->middleware('jwt.auth');
        $this->middleware('role:admin|comite', ['only' => ['decrypt']]);
    }

    /**
     * Return a newly created QRCode from payload sended.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if(!$request->filled('payload')) {
            return response()->json(['error' => 'No payload given'], 400);
        }

        $crypt = $request->input('crypt', false);

        $payload = ($crypt) ? Crypt::encrypt($request->input('payload')) : $request->input('payload');
        $format = $request->input('format', 'png');
        $size = $request->input('size', 300);
        $correct = $request->input('errorCorrection', 'L');
        $margin = $request->input('margin', 0);
        $bgColor = $request->input('backgroundColor', array(255,255,255));
        $image = $request->input('image.src', '/public/images/logo_carre.jpg');
        $imageSize = $request->input('image.size', .2);
        $encoding = $request->input('encoding', 'UTF-8');

        QrCode::format($format);
        QrCode::size($size);
        QrCode::errorCorrection($correct);
        QrCode::margin($margin);
        QrCode::backgroundColor($bgColor[0], $bgColor[1], $bgColor[2]);
        return base64_encode(QrCode::encoding($encoding)->merge($image, $imageSize)->generate($payload));
    }

    /**
     * Decrypt the payload of the scanned QRCode.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function decrypt(Request $request)
    {
        if(!$request->filled('payload')) {
            return response()->json(['error' => 'No payload given'], 400);
        }

        return Crypt::decrypt($request->input('payload'));
    }
}
