<?php
namespace AntonioPrimera\Cif\Lookup;

use AntonioPrimera\Cif\Cif;

/**
 * Online VAT registry lookup — routes RO codes to ANAF, the rest of the EU to VIES,
 * and returns existence/active status + company data (name, address, …).
 *
 * Works out of the box (zero wiring, cURL default); inject an HttpClient for tests
 * or to reuse a framework HTTP client. Caching is intentionally left to the caller.
 *
 *   $result = (new VatLookup())->lookup('RO45707266');   // or '45707266', 'DE123…', 'EL094…'
 *   $result->isValid();  $result->vatPayer;  $result->name;  $result->address;
 */
class VatLookup
{
    private HttpClient $http;

    private AnafRegistry $anaf;

    private ViesRegistry $vies;

    public function __construct(
        ?HttpClient $http = null,
        ?AnafRegistry $anaf = null,
        ?ViesRegistry $vies = null,
    ) {
        $this->http = $http ?? new CurlHttpClient;
        $this->anaf = $anaf ?? new AnafRegistry($this->http);
        $this->vies = $vies ?? new ViesRegistry($this->http);
    }

    public function lookup(Cif|string $cif): VatLookupResult
    {
        $cif = Cif::from($cif);

        // no explicit country prefix → assume Romania (CUI); 'GR' → 'EL'
        $country = strtoupper($cif->countryCode() ?? 'RO');
        $country = $country === 'GR' ? 'EL' : $country;

        return $country === 'RO'
            ? $this->anaf->lookup($cif)
            : $this->vies->lookup($cif);
    }
}
