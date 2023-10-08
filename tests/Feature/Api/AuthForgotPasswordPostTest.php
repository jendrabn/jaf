<?php
// tests/Feature/Api/AuthForgotPasswordPostTest.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\AuthController;
use App\Http\Requests\Api\ForgotPasswordRequest;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Tests\TestCase;

class AuthForgotPasswordPostTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/auth/forgot_password';

  /** @test */
  public function can_send_password_reset_link()
  {
    Notification::fake();

    $user = $this->createUser();

    $response = $this->postJson($this->uri, $user->only('email'));

    $response->assertOk()
      ->assertExactJson(['data' => true]);

    Notification::assertSentTo(
      $user,
      ResetPassword::class,
      function ($notification, $channels, $notifiable) use ($user) {
        $mail = $notification->toMail($user)->toArray();
        $resetUrl = config('app.url')
          . '/reset_password?token=' . $notification->token
          . '&email=' . $user->email;

        $this->assertEquals($resetUrl, $mail['actionUrl']);

        return true;
      }
    );
  }

  /** @test */
  public function send_password_reset_link_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      AuthController::class,
      'sendPasswordResetLink',
      ForgotPasswordRequest::class
    );
  }

  /** @test */
  public function  forgot_password_request_has_the_correct_rules()
  {
    $this->assertValidationRules(
      [
        'email' => [
          'required',
          'email',
          Rule::exists('users', 'email'),
        ],
      ],
      (new ForgotPasswordRequest())->rules()
    );
  }
}
