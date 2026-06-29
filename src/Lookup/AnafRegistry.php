<?php
namespace AntonioPrimera\Cif\Lookup;

use AntonioPrimera\Cif\Cif;

/**
 * Romanian VAT registry lookup via ANAF (Registrul plătitorilor de TVA), API v9.
 * Reuses Cif's RO checksum as a format gate before hitting the network.
 */
class AnafRegistry
{
    public function __construct(
        private HttpClient $http,
        private string $url = 'https://webservicesp.anaf.ro/api/PlatitorTvaRest/v9/tva',
        private float $timeout = 8.0,
    ) {}

    public function lookup(Cif $cif): VatLookupResult
    {
        $number = $cif->withoutCountryCode();

        // format gate — a malformed CUI is invalid without any network call
        if (!$cif->isValid('RO'))
            return new VatLookupResult(VatLookupResult::INVALID, 'RO', $number, source: 'anaf', error: 'bad_format');

        try {
            $data = $this->http->postJson(
                $this->url,
                [['cui' => (int) $number, 'data' => date('Y-m-d')]],
                $this->timeout,
            );
        } catch (HttpException $e) {
            return new VatLookupResult(VatLookupResult::UNVERIFIED, 'RO', $number, source: 'anaf', error: $e->getMessage());
        }

        $found = $data['found'][0] ?? null;
        if (!$found)
            return new VatLookupResult(VatLookupResult::INVALID, 'RO', $number, source: 'anaf', error: 'not_found');

        $general = $found['date_generale'] ?? [];
        $scpTva  = (bool) ($found['inregistrare_scop_Tva']['scpTVA'] ?? false);
        $state   = $found['stare_inactiv'] ?? [];
        $inactive = (!empty($state['dataInactivare']) && empty($state['dataReactivare']))
            || !empty($state['dataRadiere']);

        return new VatLookupResult(
            status: $inactive ? VatLookupResult::INVALID : VatLookupResult::VALID,
            countryCode: 'RO',
            vatNumber: $number,
            vatPayer: $scpTva && !$inactive,
            name: $general['denumire'] ?? null,
            address: $general['adresa'] ?? null,
            postalCode: $general['codPostal'] ?? null,
            registrationNumber: $general['nrRegCom'] ?? null,
            source: 'anaf',
            raw: $found,
        );
    }
}
