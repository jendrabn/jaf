<?php

namespace App\Services;

use App\Models\Shipping;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RajaOngkirService
{
  public function getCosts(int $destination, int $weight, array $couriers = Shipping::COURIERS): array
  {
    $costs = [];
    foreach ($couriers as $courier) {
      $costs = array_merge($costs, $this->fetchCosts($destination, $weight, $courier));
    }

    return $costs;
  }

  public function getService(string $service, int $destination, int $weight, string $courier): array|null
  {
    $costs = $this->fetchCosts($destination, $weight, $courier);

    return collect($costs)->firstWhere('service', $service);
  }

  public function fetchCosts(int $destination, int $weight, string $courier): array
  {
    $baseUrl = config('shop.rajaongkir.base_url');
    $key =  config('shop.rajaongkir.key');
    $origin = config('shop.address.city_id');

    $response = Http::acceptJson()
      ->withHeader('key', $key)
      ->post("$baseUrl/cost", compact('origin', 'destination', 'weight', 'courier'))
      ->throwUnlessStatus(Response::HTTP_OK);

    $results = $response->json('rajaongkir.results')[0];

    return collect($results['costs'] ?? [])->map(fn ($cost) => [
      'courier' => $courier,
      'courier_name' => $results['name'],
      'service' => $cost['service'],
      'service_name' => $cost['description'],
      'cost' => $cost['cost'][0]['value'],
      'etd' => str_replace(['hari', ' '], '', strtolower($cost['cost'][0]['etd'])) . ' hari',
    ])->toArray();
  }
}
