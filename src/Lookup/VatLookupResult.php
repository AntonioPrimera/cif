<?php
namespace AntonioPrimera\Cif\Lookup;

/**
 * Result of an online VAT registry lookup (ANAF for RO, VIES for the rest of the EU).
 *
 *  status:   VALID      - the code resolves to a real, active entity
 *            INVALID    - non-existent / inactive / malformed
 *            UNVERIFIED - the registry could not be reached (down / timeout)
 *  vatPayer: registered for VAT (RO: scpTVA; EU: == valid) → relevant for reverse-charge
 */
readonly class VatLookupResult
{
    public const VALID = 'valid';

    public const INVALID = 'invalid';

    public const UNVERIFIED = 'unverified';

    public function __construct(
        public string $status,
        public string $countryCode,
        public string $vatNumber,
        public bool $vatPayer = false,
        public ?string $name = null,
        public ?string $address = null,
        public ?string $postalCode = null,
        public ?string $registrationNumber = null, // RO: nr. Reg. Comerțului
        public string $source = '',                // 'anaf' | 'vies'
        public ?string $error = null,
        public array $raw = [],
    ) {}

    public function isValid(): bool
    {
        return $this->status === self::VALID;
    }

    public function isInvalid(): bool
    {
        return $this->status === self::INVALID;
    }

    public function isUnverified(): bool
    {
        return $this->status === self::UNVERIFIED;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'status'             => $this->status,
            'valid'              => $this->isValid(),
            'vatPayer'           => $this->vatPayer,
            'countryCode'        => $this->countryCode,
            'vatNumber'          => $this->vatNumber,
            'name'               => $this->name,
            'address'            => $this->address,
            'postalCode'         => $this->postalCode,
            'registrationNumber' => $this->registrationNumber,
            'source'             => $this->source,
            'error'              => $this->error,
        ];
    }
}
