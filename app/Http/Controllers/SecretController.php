<?php

namespace App\Http\Controllers;

use App\Secret;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Yaml\Yaml;

class SecretController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addSecret(Request $request)
    {
        // validáljuk a beérkezett kéréseket, hiba eseten jelezzük a hiányosságot
        // a secret kötelező elem, minimum 1 hosszúságúnak kell legyen
        // a remainingViews kötelező elem, csak szám lehet és minimum 1 lehet az értéke
        // a expireAfter kötelező elem, csak szám lehet és minimum 0 értékű lehet

        $request->validate([
            'secret' => 'required|min:1',
            'remainingViews' => 'required|numeric|min:1',
            'expireAfter' => 'required|numeric|min:0'
        ]);
        
        // létrehozzuk az egyedi hash-t a titokhoz

        $hash = base64_encode(Hash::make('secret'));

        // titok mentése az adatbázisba

        $save = new Secret;
        $save->hash = $hash;
        $save->name = $request->secret;
        $save->remaining_views = $request->remainingViews;
        $save->minutes = $request->expireAfter;
        $save->expires_at = Carbon::now()->addMinutes($request->expireAfter);
        $save->save();

        // válasz visszatérése json formátumban

        $response = [
            'hash' => $hash,
            'secretText' => $request->secret,
            'remainingViews' => $request->remainingViews
        ];

        // a reszponzív válaszadás miatt lekérem a header fejlécet, hogy megtudjuk milyen típusba szeretné a választ kapni
        // jelenleg json / xml formátum elérhető
        // ha nem érkezik fejléc akkor eldobom a kérést

        switch (request()->header('accept')) {
            case 'application/json':
                return response($response, 201)->header('Content-Type', 'application/json');
                break;

            case 'application/xml':
                return response()->view('response.xml.found', compact('response'), 201)->header('Content-Type', 'application/xml');
                break;

            case 'application/x-yaml':
                return response(Yaml::dump($response, 1), 201)->header('Content-Type', 'application/x-yaml');
                break;
            
            default:
                $response = [
                    'message' => 'Accept not found.'
                ];
                return response($response, 405);
                break;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Secret  $secret
     * @return \Illuminate\Http\Response
     */
    public function getSecretByHash(Request $request)
    {
        // megkeressük a hash alapján a titkot
        // a titkot akkor fogjuk megtalálni, ha a hash megeggyezik, illetve maradt még legalább 1 megtekintés és nem járt le
        // a lejárati dátum melett a percet is mentettem, így könnyebb figyelni, hogy örök életű e vagy sem (hasonlóan, mint egy flag)
        // ha a minutes 0, akkor csak akkor érhető el a titok, ha maradt még legalább 1 megtekintés, így a lejárati dátumot figyelmen kívűl hagyjuk

        $secret = Secret::select('name','remaining_views')->where('hash',$request->hash)->where('remaining_views','>',0)->where(function ($query) {
               $query->where('expires_at', '>=', Carbon::now())
                     ->orWhere('minutes',0);
            })->first();

        if(empty($secret)){
            $response = [
                'message' => 'Secret not found.'
            ];

            switch (request()->header('accept')) {
                case 'application/json':
                    return response($response, 405)->header('Content-Type', 'application/json');
                    break;

                case 'application/xml':
                    return response()->view('response.xml.not-found', compact('response'), 405)->header('Content-Type', 'application/xml');
                    break;

                case 'application/x-yaml':
                    return response(Yaml::dump($response, 1), 201)->header('Content-Type', 'application/x-yaml');
                    break;
                
                default:
                    $response = [
                        'message' => 'Accept not found.'
                    ];
                    return response($response, 405);
                    break;
            }
        }

        // ha megtaláltuk a titkot, akkor a megtekintésből leveszek 1 értéket, majd updatelem az adatbázisban, és visszaadom a titkot json formátumban

        $remaining_views = $secret->remaining_views-1;

        Secret::where('hash',$request->hash)->update([ 'remaining_views' => $remaining_views ]);

        $response = [
            'hash' => $request->hash,
            'secret' => $secret->name,
            'remaining_views' => $remaining_views
        ];

        // a reszponzív válaszadás miatt lekérem a header fejlécet, hogy megtudjuk milyen típusba szeretné a választ kapni
        // jelenleg json / xml formátum elérhető
        // ha nem érkezik fejléc akkor eldobom a kérést
        
        switch (request()->header('accept')) {
            case 'application/json':
                return response($response, 201)->header('Content-Type', 'application/json');
                break;

            case 'application/xml':
                return response()->view('response.xml.found', compact('response'), 201)->header('Content-Type', 'application/xml');
                break;

            case 'application/x-yaml':
                return response(Yaml::dump($response, 1), 201)->header('Content-Type', 'application/x-yaml');
                break;
            
            default:
                $response = [
                    'message' => 'Accept not found.'
                ];
                return response($response, 405);
                break;
        }
    }
}
