<?php

namespace App\DTOs;

use Illuminate\Support\Facades\Validator;

class CustomerDTO
{
    public function __construct(
        public string $contactName,
        public ?string $companyName = null,
        public ?string $email = null,
        public ?string $phone = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        Validator::make($data, self::rules())->validate();

        return new self(
            contactName: $data['contact_name'],
            companyName: $data['company_name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
        );
    }

    public static function rules(): array
    {
        return [
            'contact_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:25',
        ];
    }

    public function toZohoArray(): array
    {
        return array_filter([
            'contact_name' => $this->contactName,
            'company_name' => $this->companyName,
            'email' => $this->email,
            'phone' => $this->phone,
            'contact_type' => 'customer',
        ]);
    }
}
