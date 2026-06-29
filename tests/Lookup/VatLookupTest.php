<?php
use AntonioPrimera\Cif\Lookup\AnafRegistry;
use AntonioPrimera\Cif\Lookup\ViesRegistry;
use AntonioPrimera\Cif\Lookup\VatLookup;
use AntonioPrimera\Cif\Tests\Lookup\FakeHttpClient;

/** Canned ANAF "found" payload (valid + VAT payer + active). */
function anafFound(array $generale = [], bool $scpTva = true, array $stareInactiv = []): array
{
    return ['found' => [[
        'date_generale' => array_merge([
            'denumire'  => 'ECOBAT ENERGY S.R.L.',
            'adresa'    => 'JUD. ILFOV, ORȘ. OTOPENI',
            'codPostal' => '075100',
            'nrRegCom'  => 'J23/1299/2022',
        ], $generale),
        'inregistrare_scop_Tva' => ['scpTVA' => $scpTva],
        'stare_inactiv' => array_merge(['dataInactivare' => '', 'dataReactivare' => '', 'dataRadiere' => ''], $stareInactiv),
    ]]];
}

function vatLookupWith(FakeHttpClient $client): VatLookup
{
    return new VatLookup($client, new AnafRegistry($client), new ViesRegistry($client));
}

it('RO valid CUI → valid + company data from ANAF', function () {
    $r = vatLookupWith(FakeHttpClient::returning(anafFound()))->lookup('RO46801317');

    expect($r->isValid())->toBeTrue()
        ->and($r->vatPayer)->toBeTrue()
        ->and($r->name)->toBe('ECOBAT ENERGY S.R.L.')
        ->and($r->registrationNumber)->toBe('J23/1299/2022')
        ->and($r->source)->toBe('anaf');
});

it('RO company that exists but is not a VAT payer → valid, vatPayer false', function () {
    $r = vatLookupWith(FakeHttpClient::returning(anafFound(scpTva: false)))->lookup('RO46801317');

    expect($r->isValid())->toBeTrue()
        ->and($r->vatPayer)->toBeFalse();
});

it('RO inactive company → invalid', function () {
    $http = FakeHttpClient::returning(anafFound(stareInactiv: ['dataInactivare' => '2024-01-01']));
    expect(vatLookupWith($http)->lookup('RO46801317')->isInvalid())->toBeTrue();
});

it('RO not found → invalid', function () {
    expect(vatLookupWith(FakeHttpClient::returning(['found' => []]))->lookup('RO46801317')->isInvalid())->toBeTrue();
});

it('RO bad checksum → invalid WITHOUT hitting the network', function () {
    $http = FakeHttpClient::returning(anafFound());
    $r = vatLookupWith($http)->lookup('RO46801318'); // checksum invalid

    expect($r->isInvalid())->toBeTrue()
        ->and($http->calledUrl)->toBeNull();
});

it('ANAF down → unverified (never invalid on a service outage)', function () {
    expect(vatLookupWith(FakeHttpClient::failing())->lookup('RO46801317')->isUnverified())->toBeTrue();
});

it('EU VAT valid via VIES → valid + name', function () {
    $http = FakeHttpClient::returning(['valid' => true, 'name' => 'SOME GMBH', 'address' => 'Berlin']);
    $r = vatLookupWith($http)->lookup('DE123456789');

    expect($r->isValid())->toBeTrue()
        ->and($r->name)->toBe('SOME GMBH')
        ->and($r->source)->toBe('vies');
});

it('EU VAT invalid via VIES → invalid, "---" cleaned to null', function () {
    $http = FakeHttpClient::returning(['valid' => false, 'name' => '---', 'address' => '---']);
    $r = vatLookupWith($http)->lookup('DE000000000');

    expect($r->isInvalid())->toBeTrue()
        ->and($r->name)->toBeNull();
});

it('VIES down → unverified', function () {
    expect(vatLookupWith(FakeHttpClient::failing())->lookup('DE123456789')->isUnverified())->toBeTrue();
});

it('routes RO to ANAF and other EU countries to VIES', function () {
    $anaf = FakeHttpClient::returning(anafFound());
    vatLookupWith($anaf)->lookup('RO46801317');
    expect($anaf->calledUrl)->toContain('anaf.ro');

    $vies = FakeHttpClient::returning(['valid' => true]);
    vatLookupWith($vies)->lookup('DE123456789');
    expect($vies->calledUrl)->toContain('vies');
});
