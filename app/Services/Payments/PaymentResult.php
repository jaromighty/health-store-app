<?php

namespace App\Services\Payments;

readonly class PaymentResult
{
    public function __construct(
        public bool $success,
        public ?string $reference = null,
        public ?string $error = null,
    ) {}

    public static function success(?string $ref = null): self
    {
        return new self(true, $ref);
    }

    public static function failure(string $error): self
    {
        return new self(false, null, $error);
    }
}
