<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ZohoInventoryService;
use App\DTOs\SalesOrderDTO;
use Illuminate\Http\JsonResponse;

class SalesOrderController extends Controller
{
    public function __construct(protected ZohoInventoryService $zohoService) {}

    public function store(SalesOrderDTO $dto): JsonResponse
    {
        try {
            
            // 1. Resolve or Create Customer
            if (!$dto->customerId) {
                $dto->customerId = $this->zohoService->getOrCreateContact(
                    $dto->customerName,
                    $dto->customerEmail,
                    $dto->customerPhone
                );
            }
            
            // 2. Create Sales Order
            $salesOrder = $this->zohoService->createSalesOrder($dto->toZohoArray());
            
            // 3. Handle Per-Item Purchase Orders
            $purchaseOrders = $this->handlePurchaseOrders($dto->lineItems);

            return response()->json([
                'sales_order' => $salesOrder,
                'purchase_orders' => $purchaseOrders,
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales Order Creation Failed: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function handlePurchaseOrders(array $lineItems): array
    {
        $posCreated = [];
        $vendorItems = [];

        foreach ($lineItems as $itemDto) {
            // Check if user requested a PO for THIS item specifically
            if (!$itemDto->createPo) {
                continue;
            }

            $item = $this->zohoService->getItem($itemDto->itemId);
            
            $stockAvailable = $item['stock_on_hand'] ?? 0;
            $needed = $itemDto->quantity - $stockAvailable;

            if ($needed > 0) {
                $vendorId = $item['vendor_id'] ?? null;
                
                if ($vendorId) {
                    $vendorItems[$vendorId][] = [
                        'item_id' => $itemDto->itemId,
                        'quantity' => $needed,
                        'rate' => $item['purchase_rate'] ?? $item['rate'],
                    ];
                }
            }
        }

        foreach ($vendorItems as $vendorId => $items) {
            $poData = [
                'vendor_id' => $vendorId,
                'line_items' => $items,
                'date' => date('Y-m-d'),
            ];
            $posCreated[] = $this->zohoService->createPurchaseOrder($poData);
        }

        return $posCreated;
    }
}

