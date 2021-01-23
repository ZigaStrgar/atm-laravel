<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected $userData = ['first_name' => 'Žiga', 'last_name' => 'Strgar', 'email' => 'ziga.strgar@gmail.com', 'country' => 'SI'];
    protected $route = 'api/users';

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testCreateUserSuccesfully()
    {
        $response = $this->postJson($this->route, $this->userData);

        $response->assertStatus(200)->assertJson($this->userData)->assertJson(['gender' => 'undefined', 'balance' => 0, 'bonus_balance' => 0]);
        $this->assertDatabaseCount('users', 1);
    }

    public function testCreateUserUniqueEmailRule()
    {
        $response = $this->postJson($this->route, $this->userData);

        $response->assertStatus(200);

        $failedResponse = $this->postJson($this->route, $this->userData);

        $failedResponse->assertStatus(422)->assertJsonValidationErrors(['email']);
        $this->assertDatabaseCount('users', 1);
    }

    public function testCreateUserValidCountryCodeAsISO2()
    {
        $cases = ['XX', 'SLO'];

        foreach ($cases as $case) {
            $response = $this->postJson($this->route, array_merge($this->userData, ['country' => $case]));

            $response->assertStatus(422)->assertJsonValidationErrors(['country']);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function testCreateUserRequiredFields()
    {
        $fields = ['first_name', 'last_name', 'email', 'country'];

        foreach ($fields as $field) {
            $response = $this->postJson($this->route, array_merge($this->userData, [$field => null]));

            $response->assertStatus(422)->assertJsonValidationErrors([$field]);
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function testCreateUserWithUndefinedGender()
    {
        $response = $this->postJson($this->route, array_merge($this->userData, ['gender' => 'undefined']));

        $response->assertStatus(422)->assertJsonValidationErrors(['gender']);
        $this->assertDatabaseCount('users', 0);
    }
}
