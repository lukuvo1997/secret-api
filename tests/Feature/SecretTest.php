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

        $response = $this->json('POST', '/api/secret', $formData)->assertStatus(201)->decodeResponseJson();

        $this->get('/api/secret/' . $response['hash'])->assertStatus(201);
    }
}
