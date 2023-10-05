<?php
// tests/Feature/Api/ShippingCostPost.php
namespace Tests\Feature\Api;

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Requests\Api\ShippingCostRequest;
use App\Models\Shipping;
use Database\Seeders\CitySeeder;
use Database\Seeders\ProvinceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShippingCostPostTest extends TestCase
{
  use RefreshDatabase;

  private string $uri = '/api/shipping_costs';

  /** @test */
  public function can_get_shipping_cost()
  {
    $this->seed([ProvinceSeeder::class, CitySeeder::class]);
    $data = [
      'destination' => 154, // Kota Jakarta Timur
      'weight' => 1500
    ];

    $response = $this->postJson($this->uri, $data);

    $response->assertOk()
      ->assertJsonStructure([
        'data' => ['*' => [
          "courier",
          "courier_name",
          "service",
          "service_name",
          "cost",
          "etd",
        ]]
      ])
      ->assertJsonFragment([
        'courier' => 'jne',
        'courier_name' => 'Jalur Nugraha Ekakurir (JNE)',
        'service' => 'REG',
        'service_name' => 'Layanan Reguler',
        'cost' => 34000,
        'etd' => '1-2 hari'
      ])
      ->assertJsonCount(8, 'data');
  }

  /** @test */
  public function shipping_cost_uses_the_correct_form_request()
  {
    $this->assertActionUsesFormRequest(
      CheckoutController::class,
      'shippingCost',
      ShippingCostRequest::class
    );
  }

  /** @test */
  public function shipping_cost_request_has_the_correct_validation_rules()
  {
    $this->assertValidationRules([
      'destination' => ['required', 'integer', 'exists:cities,id'],
      'weight' => ['required', 'integer', 'max:' . Shipping::MAX_WEIGHT],
    ], (new ShippingCostRequest())->rules());
  }
}
