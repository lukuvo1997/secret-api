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
        $request->validate([
            'secret' => 'required|min:1',
            'remainingViews' => 'required|numeric|min:1',
            'expireAfter' => 'required|numeric'
        ]);
        
        $hash = base64_encode(Hash::make('secret'));

        $save = new Secret;
        $save->hash = $hash;
        $save->name = $request->secret;
        $save->remaining_views = $request->remainingViews;
        $save->minutes = $request->expireAfter;
        $save->expires_at = Carbon::now()->addMinutes($request->expireAfter);
        $save->save();

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

        $remaining_views = $secret->remaining_views-1;

        Secret::where('hash',$request->hash)->update([ 'remaining_views' => $remaining_views ]);

        $response = [
            'secret' => $secret->name,
            'remaining_views' => $remaining_views
        ];
        
        return response($response, 201);
    }
}
