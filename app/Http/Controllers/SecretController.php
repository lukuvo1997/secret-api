<?php

namespace App\Http\Controllers;

use App\Secret;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

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
        
        return response($response, 201);
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
               $query->where('expires_at', '>', Carbon::now())
                     ->orWhere('minutes',0);
            })->first();

        if(empty($secret)){
            $response = [
                'message' => 'Secret not found.'
            ];
            
            return response($response, 404);
        }

        // ha megtaláltuk a titkot, akkor a megtekintésből leveszek 1 értéket, majd updatelem az adatbázisban, és visszaadom a titkot json formátumban

        $remaining_views = $secret->remaining_views-1;

        Secret::where('hash',$request->hash)->update([ 'remaining_views' => $remaining_views ]);

        $response = [
            'secret' => $secret->name,
            'remaining_views' => $remaining_views
        ];
        
        return response($response, 201);
    }
}
