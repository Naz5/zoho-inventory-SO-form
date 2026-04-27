<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Validator;

class SalesOrderDTO
{
    public function __construct(
        public ?string $customerId,
        public string $date,
        public array $lineItems,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $customerPhone = null,
        public ?string $referenceNumber = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        Validator::make($data, self::rules())->validate();

        return new self(
            customerId: $data['customer_id'] ?? null,
            date: $data['date'] ?? date('Y-m-d'),
            lineItems: array_map(fn($item) => ItemDTO::fromArray($item), $data['line_items']),
            customerName: $data['customer_name'] ?? null,
            customerEmail: $data['customer_email'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            referenceNumber: $data['reference_number'] ?? null,
        );
    }

    public static function rules(): array
    {
        return [
            'customer_id' => 'nullable|string',
            'customer_name' => 'required_without:customer_id|nullable|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'line_items' => 'required|array|min:1',
            'line_items.*.item_id' => 'required|string',
            'line_items.*.quantity' => 'required|integer|min:1',
            'line_items.*.create_po' => 'boolean',
            'reference_number' => 'nullable|string',
            'date' => 'nullable|date_format:Y-m-d',
        ];
    }

    public function toZohoArray(): array
    {
        return [
            'customer_id' => $this->customerId,
            'date' => $this->date,
            'line_items' => array_map(fn(ItemDTO $item) => $item->toZohoArray(), $this->lineItems),
            'reference_number' => $this->referenceNumber,
        ];
    }
}

class ItemDTO
{
    public function __construct(
        public string $itemId,
        public int $quantity,
        public ?float $rate = null,
        public ?string $name = null,
        public ?string $description = null,
        public bool $createPo = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            itemId: $data['item_id'],
            quantity: (int) $data['quantity'],
            rate: isset($data['rate']) ? (float) $data['rate'] : null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            createPo: (bool) ($data['create_po'] ?? false),
        );
    }

    public function toZohoArray(): array
    {
        return array_filter([
            'item_id' => $this->itemId,
            'quantity' => $this->quantity,
            'rate' => $this->rate,
            'description' => $this->description,
        ]);
    }
}

