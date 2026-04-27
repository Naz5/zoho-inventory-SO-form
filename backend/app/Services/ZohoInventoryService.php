<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZohoInventoryService
{
    protected $clientId;
    protected $clientSecret;
    protected $refreshToken;
    protected $orgId;
    protected $baseUrl = 'https://www.zohoapis.eu/inventory/v1';
    protected $authUrl = 'https://accounts.zoho.eu/oauth/v2/token';

    public function __construct()
    {
        $this->clientId = env('ZOHO_CLIENT_ID');
        $this->clientSecret = env('ZOHO_CLIENT_SECRET');
        $this->refreshToken = env('ZOHO_REFRESH_TOKEN');
        $this->orgId = env('ZOHO_ORG_ID');
    }

    protected function getAccessToken()
    {
        return Cache::remember('zoho_access_token', 3500, function () {
            $response = Http::asForm()->post($this->authUrl, [
                'refresh_token' => $this->refreshToken,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'refresh_token'
            ]);

            $data = $response->json();
            if ($response->successful() && isset($data['access_token'])) {
                return $data['access_token'];
            }

            throw new \Exception('Failed to refresh Zoho Access Token: ' . ($data['error'] ?? $response->body()));
        });
    }        

    protected function request()
    {
        return Http::withToken($this->getAccessToken())
            ->withQueryParameters(['organization_id' => $this->orgId]);
    }

    public function getItems()
    {
        $response = $this->request()->get($this->baseUrl . '/items');
        return $response->json()['items'] ?? [];
    }

    public function getContacts($type = 'customer')
    {
        $response = $this->request()->get($this->baseUrl . '/contacts', [
            'contact_type' => $type
        ]);
        return $response->json()['contacts'] ?? [];
    }

    public function findContactByEmail(string $email)
    {
        $response = $this->request()->get($this->baseUrl . '/contacts', [
            'email' => $email
        ]);
        $contacts = $response->json()['contacts'] ?? [];
        return $contacts[0] ?? null;
    }

    public function getOrCreateContact(string $name, ?string $email = null, ?string $phone = null)
    {
        if ($email) {
            $existing = $this->findContactByEmail($email);
            if ($existing) {
                return $existing['contact_id'];
            }
        }

        $contact = $this->createContact([
            'contact_name' => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

        if (!$contact) {
            throw new \Exception('Failed to create contact in Zoho');
        }

        return $contact['contact_id'];
    }

    public function createContact(array $data)
    {
        $response = $this->request()->post($this->baseUrl . '/contacts', $data);
        return $response->json()['contact'] ?? null;
    }

    public function createSalesOrder(array $data)
    {
        $response = $this->request()->post($this->baseUrl . '/salesorders', $data);
        return $response->json()['salesorder'] ?? null;
    }

    public function createPurchaseOrder(array $data)
    {
        $response = $this->request()->post($this->baseUrl . '/purchaseorders', $data);
        return $response->json()['purchaseorder'] ?? null;
    }

    public function getItem(string $itemId)
    {
        $response = $this->request()->get($this->baseUrl . '/items/' . $itemId);
        return $response->json()['item'] ?? null;
    }
}

