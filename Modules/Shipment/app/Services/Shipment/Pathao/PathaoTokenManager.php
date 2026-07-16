<?php

declare(strict_types=1);

namespace Modules\Shipment\Services\Shipment\Pathao;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PathaoTokenManager
{
    /**
     * Get a valid access token for the given business ID.
     * Automatically handles logins, refreshes, and database updates.
     */
    public function getAccessToken(int $businessId): string
    {
        $accessToken = setting($businessId, 'pathao_access_token');
        $refreshToken = setting($businessId, 'pathao_refresh_token');
        $expiresAtStr = setting($businessId, 'pathao_token_expire_at');

        $isExpired = false;
        if ($expiresAtStr) {
            try {
                $expiresAt = new \DateTime($expiresAtStr);
                // Subtract 5 minutes grace period
                $expiresAt->modify('-5 minutes');
                if (new \DateTime() >= $expiresAt) {
                    $isExpired = true;
                }
            } catch (Exception $e) {
                $isExpired = true;
            }
        } else {
            $isExpired = true;
        }

        if (!$accessToken || $isExpired) {
            if ($refreshToken) {
                try {
                    return $this->refreshAccessToken($businessId, $refreshToken);
                } catch (Exception $e) {
                    Log::warning("Pathao token refresh failed for business {$businessId}, falling back to full login: " . $e->getMessage());
                    return $this->login($businessId);
                }
            }
            return $this->login($businessId);
        }

        return $accessToken;
    }

    /**
     * Perform login (password grant) and store tokens.
     */
    public function login(int $businessId): string
    {
        Log::info("Pathao: Performing fresh login for business {$businessId}");

        $clientId = setting($businessId, 'pathao_client_id');
        $clientSecret = setting($businessId, 'pathao_client_secret');
        $username = setting($businessId, 'pathao_username');
        $password = setting($businessId, 'pathao_password');
        $baseUrl = $this->getBaseUrl($businessId);

        if (!$clientId || !$clientSecret || !$username || !$password) {
            throw new Exception("Missing Pathao configuration credentials for business ID: {$businessId}");
        }

        $response = Http::acceptJson()->post("{$baseUrl}/aladdin/api/v1/issue-token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $username,
            'password' => $password,
            'grant_type' => 'password',
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error("Pathao Login Failed for business {$businessId}: {$errorMsg}");
            throw new Exception("Pathao Authentication Failed: {$errorMsg}");
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? '';
        $refreshToken = $data['refresh_token'] ?? '';
        $expiresIn = (int) ($data['expires_in'] ?? 31536000);

        if (!$accessToken) {
            throw new Exception("Pathao issue-token response did not return access_token.");
        }

        $this->storeTokens($businessId, $accessToken, $refreshToken, $expiresIn);

        return $accessToken;
    }

    /**
     * Refresh access token using refresh_token.
     */
    public function refreshAccessToken(int $businessId, string $refreshToken): string
    {
        Log::info("Pathao: Refreshing token for business {$businessId}");

        $clientId = setting($businessId, 'pathao_client_id');
        $clientSecret = setting($businessId, 'pathao_client_secret');
        $baseUrl = $this->getBaseUrl($businessId);

        if (!$clientId || !$clientSecret) {
            throw new Exception("Missing Pathao client_id or client_secret for token refresh.");
        }

        $response = Http::acceptJson()->post("{$baseUrl}/aladdin/api/v1/issue-token", [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('message') ?? $response->body();
            Log::error("Pathao Token Refresh Failed for business {$businessId}: {$errorMsg}");
            throw new Exception("Pathao Token Refresh Failed: {$errorMsg}");
        }

        $data = $response->json();
        $accessToken = $data['access_token'] ?? '';
        $newRefreshToken = $data['refresh_token'] ?? $refreshToken;
        $expiresIn = (int) ($data['expires_in'] ?? 31536000);

        if (!$accessToken) {
            throw new Exception("Pathao token refresh response did not return access_token.");
        }

        $this->storeTokens($businessId, $accessToken, $newRefreshToken, $expiresIn);

        return $accessToken;
    }

    /**
     * Store tokens and expiration details in settings.
     */
    protected function storeTokens(int $businessId, string $accessToken, string $refreshToken, int $expiresIn): void
    {
        $expireTime = new \DateTime();
        $expireTime->modify("+{$expiresIn} seconds");
        $expireAtStr = $expireTime->format('Y-m-d H:i:s');

        updateSetting($businessId, 'pathao_access_token', $accessToken);
        updateSetting($businessId, 'pathao_refresh_token', $refreshToken);
        updateSetting($businessId, 'pathao_token_expire_at', $expireAtStr);

        Log::info("Pathao: Saved new tokens for business {$businessId}. Expires at: {$expireAtStr}");
    }

    /**
     * Get API base URL for the given business.
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
