<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZohoInventoryService;
use Illuminate\Http\JsonResponse;

class ItemController extends Controller
{
    public function __construct(protected ZohoInventoryService $zohoService) {}

    public function index(): JsonResponse
    {
        try {
            $items = $this->zohoService->getItems();
            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
