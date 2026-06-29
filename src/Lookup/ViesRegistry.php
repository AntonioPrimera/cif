<?php
namespace AntonioPrimera\Cif\Lookup;

use AntonioPrimera\Cif\Cif;

/**
 * EU VAT registry lookup via the VIES REST API.
 * Some member states (e.g. DE) don't share trader name/address → returned as "---" (→ null).
 */
class ViesRegistry
{
    public function __construct(
        private HttpClient $http,
        private string $url = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number',
        private float $timeout = 8.0,
    ) {}

    public function lookup(Cif $cif): VatLookupResult
    {
        // Greece: ISO 'GR' but the VAT/VIES prefix is 'EL'
        $country = strtoupper($cif->countryCode() ?? '');
        $country = $country === 'GR' ? 'EL' : $country;
        $number  = $cif->withoutCountryCode();

        try {
            $data = $this->http->postJson(
                $this->url,
                ['countryCode' => $country, 'vatNumber' => $number],
                $this->timeout,
            );
        } catch (HttpException $e) {
            return new VatLookupResult(VatLookupResult::UNVERIFIED, $country, $number, source: 'vies', error: $e->getMessage());
        }

        if (!array_key_exists('valid', $data))
            return new VatLookupResult(VatLookupResult::UNVERIFIED, $country, $number, source: 'vies', error: $data['userError'] ?? 'no_valid_field', raw: $data);

        $valid = (bool) $data['valid'];
        $clean = fn ($v) => (is_string($v) && $v !== '' && $v !== '---') ? trim($v) : null;

        return new VatLookupResult(
            status: $valid ? VatLookupResult::VALID : VatLookupResult::INVALID,
            countryCode: $country,
            vatNumber: $number,
            vatPayer: $valid,
            name: $clean($data['name'] ?? null) ?? $clean($data['traderName'] ?? null),
            address: $clean($data['address'] ?? null) ?? $clean($data['traderStreet'] ?? null),
            postalCode: $clean($data['traderPostalCode'] ?? null),
            source: 'vies',
            raw: $data,
        );
    }
}
