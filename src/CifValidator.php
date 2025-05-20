<?php
namespace AntonioPrimera\Cif;

class CifValidator
{

    public static function validate(Cif $cif, string|null $countryCode = 'RO'): bool
    {
        $countryCode = $cif->countryCode() ?? $countryCode;

        if (!$countryCode)
            throw new \InvalidArgumentException('Country code was not provided and could not be extracted from the provided Cif object.');

        $validationMethod = 'isValid' . strtoupper($countryCode);
        if (!method_exists(static::class, $validationMethod))
            return static::isLikelyVatNumber($cif);

        return static::$validationMethod($cif->withoutCountryCode());
    }

    //--- Country-specific validation methods -------------------------------------------------------------------------

    /**
     * Validate a vat number, for which we don't have a specific validation method.
     * This may return false positives or false negatives, but it is only a
     * fallback method.
     */
    protected static function isLikelyVatNumber(Cif $vatNumber): bool
    {
        $wcc = $vatNumber->withoutCountryCode();

        // max length 13
        if (strlen($wcc) > 13)
            return false;

        // must only contain digits
        if (!ctype_digit($wcc))
            return false;

        return true;
    }

    protected static function isValidRO(string $vatNumber): bool
    {
        // Must have between 2 and 10 digits
        if (!preg_match('/^[0-9]{2,10}$/', $vatNumber))
            return false;

        // Control number
        $v = 753217532;
        //$controlDigit = 2;
        //$controlNumber = 75321753;
        $numericCif = intval($vatNumber);

        // Extract the last digit (control digit)
        $cifControlDigit = $numericCif % 10;
        $numericCif = (int) ($numericCif / 10);

        // Multiply each digit with the corresponding digit from the control number (starting from the end)
        $t = 0;
        while($numericCif > 0){
            $t += ($numericCif % 10) * ($v % 10);
            $numericCif = (int) ($numericCif / 10);
            $v = (int) ($v / 10);
        }

        // Multiply the sum with 10 and extract the last digit [0 - 10]
        $controlDigit = $t * 10 % 11;

        // If the control digit is 10, make it 0
        if($controlDigit === 10)
            $controlDigit = 0;

        return $cifControlDigit === $controlDigit;
    }
}
