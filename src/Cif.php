<?php
namespace AntonioPrimera\Cif;

use Stringable;

/**
 * Handle romanian Vat Identification Numbers (optionally starting with RO)
 */
readonly class Cif implements Stringable
{
    public string $cif;

    //--- Construction & Factories ------------------------------------------------------------------------------------

    public function __construct(string $cif)
    {
        //remove all spaces and convert to uppercase
        $this->cif = $this->cleanCif($cif);
    }

    public static function from(string|Cif $cif): Cif
    {
        return $cif instanceof Cif ? $cif : new static($cif);
    }

    //--- Public API --------------------------------------------------------------------------------------------------

    /**
     * Check if the vatNumber has a country code. If a specific country code is provided, check
     * for that specific country code, otherwise check if any country code is present.
     */
    public function hasCountryCode(string|null $countryCode = null): bool
    {
        return $countryCode
            ? str_starts_with($this->cif, strtoupper($countryCode))
            : preg_match('/^[A-Z]{2}/', $this->cif) === 1;
    }

    function withoutCountryCode(): string
    {
        // Remove spaces and extract the numeric part
        return preg_replace('/^[A-Z]{2}/', '', $this->cif);
    }

    function countryCode(): ?string
    {
        // Extract the country code if present
        return preg_match('/^([A-Z]{2})/', $this->cif, $matches)
            ? $matches[1]
            : null;
    }

    public function is(Cif|string $cif): bool
    {
        return is_string($cif)
            ? $this->cif === $this->cleanCif($cif)
            : $this->cif === $cif->cif;
    }

    public function isNot(Cif|string $cif): bool
    {
        return !$this->is($cif);
    }

    //--- Validation --------------------------------------------------------------------------------------------------

    /**
     * This only checks if the vatNumber is a valid romanian VAT number
     */
    public function isValid(string $countryCode = 'RO'): bool
    {
        return CifValidator::validate($this, $countryCode);
    }

    //--- Interface implementation ------------------------------------------------------------------------------------

    public function __toString()
    {
        return $this->cif;
    }

    //--- Protected helpers -------------------------------------------------------------------------------------------

    protected function cleanCif(string $cif): string
    {
        return strtoupper(str_replace(' ', '', $cif));
    }
}
