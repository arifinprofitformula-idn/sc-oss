<?php

namespace App\Contracts;

interface ShippingServiceInterface
{
    public function getProvinces();
    public function getCities($provinceId);
    public function getSubdistricts($cityId);
    public function searchDestination($query);
    public function getCost($origin, $destination, $weight, $courier);
}
