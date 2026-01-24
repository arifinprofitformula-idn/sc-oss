<?php

namespace App\Services;

use App\Contracts\ShippingProviderInterface;
use App\Services\IntegrationService;
use App\Services\RajaOngkirService;
use App\Services\ApiIdService;

class ShippingService
{
    protected $integrationService;
    protected $rajaOngkirService;
    protected $apiIdService;

    public function __construct(
        IntegrationService $integrationService,
        RajaOngkirService $rajaOngkirService,
        ApiIdService $apiIdService
    ) {
        $this->integrationService = $integrationService;
        $this->rajaOngkirService = $rajaOngkirService;
        $this->apiIdService = $apiIdService;
    }

    public function getProvider($name = null): ShippingProviderInterface
    {
        // If name is explicitly provided, use it. Otherwise use default from DB.
        $provider = $name ?: $this->integrationService->get('shipping_provider', 'rajaongkir');
        
        if ($provider === 'api_id') {
            return $this->apiIdService;
        }

        return $this->rajaOngkirService;
    }

    public function getCost($origin, $destination, $weight, $courier = null, $provider = null)
    {
        return $this->getProvider($provider)->getCost($origin, $destination, $weight, $courier);
    }

    public function getProvinces($provider = null)
    {
        return $this->getProvider($provider)->getProvinces();
    }

    public function getCities($provinceId, $provider = null)
    {
        return $this->getProvider($provider)->getCities($provinceId);
    }

    public function getSubdistricts($cityId, $provider = null)
    {
        return $this->getProvider($provider)->getSubdistricts($cityId);
    }

    public function getVillages($subdistrictId, $provider = null)
    {
        return $this->getProvider($provider)->getVillages($subdistrictId);
    }
    
    public function searchDestination($query, $provider = null)
    {
         $service = $this->getProvider($provider);
         if (method_exists($service, 'searchDestination')) {
             return $service->searchDestination($query);
         }
         
         // Fallback
         return [];
    }
}
