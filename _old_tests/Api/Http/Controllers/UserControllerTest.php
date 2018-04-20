<?php

namespace Tests;

use GetCandy\Api\Auth\Models\User;

/**
 * @group controllers
 * @group api
 * @group users
 */
class UserControllerTest extends TestCase
{
    protected $baseStructure = [
        'id',
        'name',
        'email',
    ];

    public function testIndex()
    {
        $response = $this->get($this->url('users'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => [$this->baseStructure],
            'meta' => ['pagination'],
        ]);

        $this->assertEquals(200, $response->status());
    }

    public function testStore()
    {
        $response = $this->post($this->url('users'), [
                'email' => 'dom@neondigital.co.uk',
                'password' => 'password',
                'name' => 'Dom',
                'password_confirmation' => 'password',
            ], [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]);

        $this->assertEquals(200, $response->status());

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);
    }

    public function testPasswordConfirmationFails()
    {
        $response = $this->post($this->url('users'), [
                'email' => 'dom@neondigital.co.uk',
                'password' => 'passwosrd',
                'name' => 'Dom',
                'password_confirmation' => 'password',
            ], [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]);

        $this->assertEquals(422, $response->status());

        $response->assertJsonStructure([
            'password',
        ]);
    }

    public function testValidationFailedStore()
    {
        $response = $this->post($this->url('users'), [], [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]);

        $this->assertEquals(422, $response->status());

        $response->assertJsonStructure([
            'password', 'email', 'name',
        ]);
    }

    public function testDuplicateEmailValidationErrorStore()
    {
        User::create([
            'email' => 'person@neondigital.co.uk',
            'password' => 'password',
            'name' => 'Person',
        ]);

        $response = $this->post($this->url('users'), [
                'email' => 'person@neondigital.co.uk',
                'password' => 'password',
                'name' => 'Person',
                'password_confirmation' => 'password',
            ], [
                'Authorization' => 'Bearer '.$this->accessToken(),
            ]);

        $this->assertEquals(422, $response->status());

        $response->assertJsonStructure([
            'email',
        ]);
    }

    public function testCurrentUser()
    {
        $response = $this->get($this->url('users/current'), [
            'Authorization' => 'Bearer '.$this->accessToken(),
        ]);

        $response->assertJsonStructure([
            'data' => $this->baseStructure,
        ]);

        $this->assertEquals(200, $response->status());
    }
}
