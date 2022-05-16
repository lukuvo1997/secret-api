<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SecretTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_addSecret()
    {
        $formData = [
            'secret' => 'Lorem ipsum.',
            'remainingViews' => '1',
            'expireAfter' => '1'
        ];

        // postoljuk a megadott url címre a formdata tömböt benne a titokkal, majd ha a válasz helyes egy újabb kérést indítunk, melyen az előbb létrehozott titkot fogjuk visszakérni

        $this->withHeaders([
            'Accept' => 'application/json'
        ]);

        $response = $this->json('POST', '/api/v1/secret', $formData)->assertStatus(201)->decodeResponseJson();

        $this->get('/api/v1/secret/' . $response['hash'])->assertStatus(201);
    }
}
