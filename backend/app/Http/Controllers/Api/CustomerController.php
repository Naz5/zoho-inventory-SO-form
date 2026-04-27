<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZohoInventoryService;
use App\DTOs\CustomerDTO;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function __construct(protected ZohoInventoryService $zohoService) {}

    public function index(): JsonResponse
    {
        try {
            $contacts = $this->zohoService->getContacts('customer');
            return response()->json($contacts);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(CustomerDTO $dto): JsonResponse
    {
        try {
            $contact = $this->zohoService->createContact($dto->toZohoArray());
            return response()->json($contact);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
