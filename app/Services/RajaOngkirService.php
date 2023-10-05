<?php

// app/Http/Services/RajaOngkirService.php

namespace App\Services;

use App\Models\Shipping;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class RajaOngkirService
{
  private string $origin, $key, $baseUrl;

  public function __construct()
  {
    $this->origin = config('shop.address.city_id');
    $this->key =  config('shop.rajaongkir.key');
    $this->baseUrl = config('shop.rajaongkir.base_url');
  }

  public function getCosts(int $destination, int $weight, ?array $couriers = Shipping::COURIERS): array
  {
    $costs = [];

    foreach ($couriers as $courier) {
      $costs = array_merge(
        $costs,
        $this->fetchCost($destination, $weight, $courier)
      );
    }

    return $costs;
  }

  public function fetchCost(int $destination, int $weight, string $courier): array
  {
    $origin = $this->origin;

    $response = Http::acceptJson()
      ->withHeader('key', $this->key)
      ->post($this->baseUrl . '/cost', compact('origin', 'destination', 'weight', 'courier'))
      ->throwUnlessStatus(Response::HTTP_OK);

    $results = $response->object()->rajaongkir->results[0] ?? [];
    $costs = [];

    if ($results && $results->costs) {
      $costs = collect($results->costs)->map(fn ($cost) => [
        'courier' => $courier,
        'courier_name' => $results->name,
        'service' => $cost->service,
        'service_name' => $cost->description,
        'cost' => $cost->cost[0]->value,
        'etd' => str_replace(['hari', ' '], '', strtolower($cost->cost[0]->etd)) . ' hari',
      ])->toArray();
    }

    return $costs;
  }

  public function getService(string $service, int $destination, int $weight, string $courier)
  {
    $costs = $this->fetchCost($destination, $weight, $courier);
    $service = collect($costs)->firstWhere('service', $service);

    return $service;
  }
}
