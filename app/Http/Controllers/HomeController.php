<?php

namespace App\Http\Controllers;

use App\Models\Exportacao;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function dashboard()
    {
        $empresa = auth()->user()->empresa_id;
        return view('g.dashboard.index', compact('empresa'));
    }

    public function concordarTermos()
    {
        auth()->user()->update(['termos' => true]);
        return response()->json([], 201);
    }

    public function saveToken(Request $request)
    {
        auth()->user()->update(['device_token' => $request->token]);

        return response()->json(['token saved successfully.']);
    }

    public function downloads()
    {
        $downloads = auth()->user()->Exportacoes()->get();
        return response()->json($downloads);
    }

    public function downloadArquivo($arquivo)
    {
        $dono = Exportacao::whereArquivo($arquivo)->whereUserId(auth()->user()->id)->first();
        if ($dono) {
            $disco = 'disco-exportacao';
            if (Storage::disk($disco)->exists($arquivo)) {
                return \Storage::disk($disco)->response($arquivo);
            }
        }
        abort(404);
    }

    public function sendNotification(Request $request)
    {
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();
//        $firebaseToken = "cvjflEfU6DgKL03PfiefEi:APA91bF23tJ5roJjInZtIqAcj4NUXS67ug1Ie_V-m4v90a0K6yhL7PTcMCFdtMtbwrhSfr4wYb43LsfzT8fSKYyTPwc8-cFN3V5kwcrCfyCL6JQWJwPvUXd9p2Vku2FKN5ypN-HglZkw";
        $SERVER_API_KEY = 'AAAAO9mvkTk:APA91bFuCMoJQvCltJ8dhfL6LdsNbtQtsjWPDr2ysBofXY_I4yCQOYZf_5GlMatqL-4-eU1SRpXj5sGO2qZe4BW-etjKEEd9vDB4_y6RXarLN17UUpZwL_kqtBc6PO0TAaCThXSNokPg';

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request->title,
                "body" => $request->body,
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        dd($response);
    }

}

//
//curl -X POST -H "Authorization: key=AAAAW4bVfs8:APA91bE5-c6EEvMd8uXMmRGUa-wVT-eWIY5teaCfANL_MgNwztFOKAiTaKe03qsgJmKkQaZMa0hlzG2lfjMB378Q5NCKEuJM6h9TUdY-ZuFOdeHZXoXtJ-46LMCV1cfCZY2mvnOkzWLN" \
//-H "Content-Type: application/json" \
//-d '{
//  "data": {
//    "notification": {
//        "title": "FCM Message",
//        "body": "This is an FCM Message",
//        "icon": "/itwonders-web-logo.png",
//    }
//  },
//  "to": "cvjflEfU6DgKL03PfiefEi:APA91bF25ESs0XBadB5PRYZslO8ZSdKi3QClDT72Rq9EcOnXLgwa_kTuOBhwBWP9ulP06KKZWlomHd61KsUWxRpPIWsCbW0y_0N-6oJYYZyIUcFvwYJWE4EgaUw0rNbP1rteOp5vrIbe"
//}' https://fcm.googleapis.com/fcm/send
