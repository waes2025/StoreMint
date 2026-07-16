<?php

declare(strict_types=1);

namespace Modules\Shipment\Services\Shipment\Pathao;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoApi
{
    protected PathaoTokenManager $tokenManager;

    public function __construct(PathaoTokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * Get available cities.
     */
    public function getCities(int $businessId): array
    {
        return $this->request($businessId, 'GET', '/aladdin/api/v1/countries/1/cities');
    }

    /**
     * Get zones within a city.
     */
    public function getZones(int $businessId, int $cityId): array
    {
        return $this->request($businessId, 'GET', "/aladdin/api/v1/cities/{$cityId}/zone-list");
    }

    /**
     * Get areas within a zone.
     */
    public function getAreas(int $businessId, int $zoneId): array
    {
        return $this->request($businessId, 'GET', "/aladdin/api/v1/zones/{$zoneId}/area-list");
    }

    /**
     * Get merchant stores.
     */
    public function getStores(int $businessId): array
    {
        return $this->request($businessId, 'GET', '/aladdin/api/v1/stores');
    }

    /**
     * Calculate delivery charges.
     */
    public function calculatePrice(int $businessId, array $data): array
    {
        return $this->request($businessId, 'POST', '/aladdin/api/v1/merchant/price-plan', $data);
    }

    /**
     * Book/create a new shipment order.
     */
    public function createOrder(int $businessId, array $data): array
    {
        return $this->request($businessId, 'POST', '/aladdin/api/v1/orders', $data);
    }

    /**
     * Get order/shipment status/tracking info.
     */
    public function trackOrder(int $businessId, string $consignmentId): array
    {
        return $this->request($businessId, 'GET', "/aladdin/api/v1/orders/{$consignmentId}/info");
    }

    /**
     * Cancel an order/shipment.
     */
    public function cancelOrder(int $businessId, string $consignmentId): array
    {
        return $this->request($businessId, 'POST', "/aladdin/api/v1/orders/{$consignmentId}/cancel");
    }

    /**
     * Send HTTP requests with token management and automatic 401 retry.
     */
    protected function request(int $businessId, string $method, string $endpoint, array $data = [], bool $isRetry = false): array
    {
        $baseUrl = $this->getBaseUrl($businessId);
        $url = $baseUrl . $endpoint;
        
        try {
            $token = $this->tokenManager->getAccessToken($businessId);
        } catch (Exception $e) {
            Log::error("Pathao API Token Retreival Exception: " . $e->getMessage());
            throw new Exception("Pathao Authentication failure: " . $e->getMessage());
        }

        // Mask credentials in logs
        $loggedData = $data;
        foreach (['password', 'client_secret', 'access_token', 'refresh_token'] as $key) {
            if (isset($loggedData[$key])) {
                $loggedData[$key] = '********';
            }
        }

        Log::info("Pathao API Request: [{$method}] {$url}", [
            'business_id' => $businessId,
            'payload' => $loggedData,
            'is_retry' => $isRetry
        ]);

        $startTime = microtime(true);

        $client = Http::withToken($token)
            ->acceptJson()
            ->timeout(30);

        $response = $method === 'POST'
            ? $client->post($url, $data)
            : $client->get($url, $data);

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        Log::info("Pathao API Response: Status {$response->status()} in {$duration}ms", [
            'body' => $response->json() ?? $response->body()
        ]);

        // Auto-retry once on 401 Unauthorized
        if ($response->status() === 401 && !$isRetry) {
            Log::warning("Pathao API returned 401 Unauthorized. Refreshing token and retrying request to: {$endpoint}");
            try {
                // Force a fresh login to clear invalid token
                $this->tokenManager->login($businessId);
                return $this->request($businessId, $method, $endpoint, $data, true);
            } catch (Exception $retryException) {
                Log::error("Pathao API Retry Failed: " . $retryException->getMessage());
                throw new Exception("Pathao API Unauthorized & token refresh failed: " . $retryException->getMessage());
            }
        }

        if ($response->failed()) {
            $message = $response->json('message') ?? ($response->json('errors') ? json_encode($response->json('errors')) : $response->body());
            Log::error("Pathao API Error response for [{$method}] {$endpoint}: {$message}");
            throw new Exception("Pathao API Error: " . $message);
        }

        return $response->json() ?? [];
    }

    /**
     * Get base URL for the given business.
     */
    protected function getBaseUrl(int $businessId): string
    {
        $url = setting($businessId, 'pathao_base_url');
        if ($url) {
            return rtrim($url, '/');
        }
        return 'https://courier-api-sandbox.pathao.com'; // Default sandbox
    }
}
