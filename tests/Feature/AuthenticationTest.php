<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
  /**
   * @test
   */
  public function login_success()
  {
    $response = $this->post(route('login'), [
      'phone_number' => '085795118959',
      'password' => 'barca1899!'
    ]);

    $response->assertStatus(200);
  }

  /**
   * @test
   */
  public function is_empty_login_field()
  {
    $response = $this->post(route('login'), [
      'phone_number' => '',
      'password' => ''
    ]);

    $response->assertStatus(500);
  }

  /**
   * @test
   */
  public function login_failed()
  {
    $response = $this->post(route('login'), [
      'phone_number' => '085795118959',
      'password' => 'barca1899'
    ]);

    $response->assertStatus(400);
  }

  /**
   * @test
   */
  public function register_success()
  {
    $response = $this->post(route('register'), [
      "name" => "Ahmad Mujani",
      "email" => "ahmad@gmail.com",
      "phone_number" => "085795118950",
      "password" => "barca1899!"
    ]);

    $response->assertStatus(200);
  }

  /**
   * @test
   */
  public function is_empty_register_field()
  {
    $response = $this->post(route('register'), [
      "name" => "",
      "email" => "",
      "phone_number" => "",
      "password" => ""
    ]);

    $response->assertStatus(500);
  }
}
