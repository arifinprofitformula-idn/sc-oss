<?php

namespace App\Contracts;

interface ShippingProviderInterface
{
    /**
     * Get list of provinces
     */
    public function getProvinces();

    /**
     * Get list of cities in a province
     */
    public function getCities($provinceId);

    /**
     * Get list of subdistricts in a city
     */
    public function getSubdistricts($cityId);

    /**
     * Get list of villages in a subdistrict
     */
    public function getVillages($subdistrictId);

    /**
     * Search for destination (city/subdistrict)
     * 
     * @param string $query
     * @return array
     */
    public function searchDestination($query);

    /**
     * Calculate shipping cost
     *
     * @param int|string $origin Origin ID
     * @param int|string $destination Destination ID
     * @param int $weight Weight in grams
     * @param string|null $courier Courier code (optional)
     * @return array Standardized cost result
     */
    public function getCost($origin, $destination, $weight, $courier = null);
}
