<?php
use AntonioPrimera\Cif\Cif;

it('can hold and parse a simple romanian CIF', function () {
    $cifString = 'RO46801317';
    $cif = Cif::from($cifString);

    expect($cif->cif)->toBe($cifString)
        ->and($cif->countryCode())->toBe('RO')
        ->and($cif->withoutCountryCode())->toBe('46801317');
});

it('can compare two CIFs', function () {
    expect(Cif::from('RO46801317')->is('RO 46801317'))->toBeTrue()
        ->and(Cif::from('RO46801317')->isNot('RO 46801318'))->toBeTrue();
});

it('can validate a romanian CIF', function () {
    expect(Cif::from('RO46801317')->isValid())->toBeTrue()
        ->and(Cif::from('RO46801318')->isValid())->toBeFalse()
        ->and(Cif::from('42009129')->isValid())->toBeTrue()
        ->and(Cif::from('42009128')->isValid())->toBeFalse();
});

it('can instantiate a Cif using the helper function', function () {
    expect(cif('RO46801317'))->toBeInstanceOf(Cif::class)
        ->and(cif('RO46801317')->is(Cif::from('RO46801317')))->toBeTrue();
});
