<?php

use Illuminate\Support\Facades\Config;

it('rejects requests with no X-API-Key header', function () {
    Config::set('api.key', 'test-secret-key');

    $response = $this->getJson('/api/ping');

    $response->assertStatus(401)
        ->assertExactJson(['error' => 'Unauthorized']);
});

it('rejects requests with a wrong X-API-Key header', function () {
    Config::set('api.key', 'test-secret-key');

    $response = $this->getJson('/api/ping', ['X-API-Key' => 'wrong-key']);

    $response->assertStatus(401)
        ->assertExactJson(['error' => 'Unauthorized']);
});

it('allows requests with the correct X-API-Key header', function () {
    Config::set('api.key', 'test-secret-key');

    $response = $this->getJson('/api/ping', ['X-API-Key' => 'test-secret-key']);

    $response->assertStatus(200)
        ->assertExactJson(['ok' => true]);
});
