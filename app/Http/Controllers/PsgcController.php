<?php

namespace App\Http\Controllers;

use App\Services\PsgcAddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PsgcController extends Controller
{
    public function provinces(PsgcAddressService $psgcAddressService): JsonResponse
    {
        return response()->json($psgcAddressService->provinces());
    }

    public function citiesMunicipalities(PsgcAddressService $psgcAddressService, string $provinceCode): JsonResponse
    {
        return response()->json($psgcAddressService->citiesByParent($provinceCode));
    }

    public function barangays(PsgcAddressService $psgcAddressService, string $cityCode): JsonResponse
    {
        return response()->json($psgcAddressService->barangaysByCity($cityCode));
    }

    public function cityMunicipality(PsgcAddressService $psgcAddressService, string $cityCode): JsonResponse
    {
        $city = $psgcAddressService->city($cityCode);

        if ($city === null) {
            return response()->json(['message' => 'City or municipality not found.'], 404);
        }

        if (empty($city['zip_code'])) {
            $city['zip_code'] = $this->resolveCityZipCode($cityCode);
        }

        return response()->json($city);
    }

    private function resolveCityZipCode(string $cityCode): ?string
    {
        $normalizedCode = preg_replace('/\D+/', '', $cityCode) ?: $cityCode;
        $cacheKey = 'psgc.city.zip.' . $normalizedCode;

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($normalizedCode) {
            $endpoints = [
                "https://psgc.cloud/api/cities-municipalities/{$normalizedCode}",
                "https://psgc.cloud/api/cities/{$normalizedCode}",
                "https://psgc.cloud/api/municipalities/{$normalizedCode}",
            ];

            foreach ($endpoints as $endpoint) {
                try {
                    $response = Http::timeout(4)->acceptJson()->get($endpoint);
                    if (!$response->ok()) {
                        continue;
                    }

                    $zip = trim((string) data_get($response->json(), 'zip_code', ''));
                    if ($zip !== '' && strtolower($zip) !== 'null') {
                        return $zip;
                    }
                } catch (\Throwable $e) {
                    // Ignore network failures and continue with the next fallback.
                }
            }

            return null;
        });
    }
}
