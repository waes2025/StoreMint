<?php
 
namespace Modules\Shipment\Services;
 
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
 
class PathaoService
{
    protected string $baseUrl;
 
    protected string $clientId;
 
    protected string $clientSecret;
 
    protected string $username;
 
    protected string $password;
 
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->clientId = config('services.pathao.client_id');
        $this->clientSecret = config('services.pathao.client_secret');
        $this->username = config('services.pathao.username');
        $this->password = config('services.pathao.password');
 
        $env = config('services.pathao.env', 'sandbox');
        $this->baseUrl = $env === 'production'
            ? 'https://api-hermes.pathao.com'
            : 'https://hermes-api.p-stageenv.xyz'; // Sandbox URL
    }
 
    /**
     * Fetch or return a cached OAuth Access Token.
     */
    public function getAccessToken(): string
    {
        return Cache::remember('pathao_access_token', now()->addDays(14), function () {
            $response = Http::post("{$this->baseUrl}/aladdin/api/v1/issue-token", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'username' => $this->username,
                'password' => $this->password,
                'grant_type' => 'password',
            ]);
 
            if ($response->failed()) {
                throw new Exception('Pathao Authentication Failed: '.$response->body());
            }
 
            return $response->json('access_token');
        });
    }
 
    /**
     * Create a standard delivery order.
     */
    public function createOrder(array $data)
    {
        $token = $this->getAccessToken();
 
        $response = Http::withToken($token)
            ->acceptJson()
            ->post("{$this->baseUrl}/aladdin/api/v1/orders", $data);
 
        if ($response->failed()) {
            throw new Exception('Pathao Order Creation Failed: '.$response->body());
        }
 
        return $response->json();
    }
}
