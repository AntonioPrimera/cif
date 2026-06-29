# VAT Number Utility

## Installation

You can install the package via composer:

```bash
composer require antonioprimera/cif
```

## Usage

### Format validation (offline)

Validate the shape of a VAT number (RO checksum + a generic fallback for other countries):

```php
use AntonioPrimera\Cif\Cif;

cif('RO46801317')->isValid();        // true  (valid RO checksum)
Cif::from('46801317')->isValid();    // true  (defaults to RO)
cif('RO46801318')->isValid();        // false

$cif = Cif::from('RO46801317');
$cif->countryCode();                 // 'RO'
$cif->withoutCountryCode();          // '46801317'
```

### Online registry lookup (ANAF + VIES)

Verify that a VAT number belongs to a real, active entity **and** fetch the company data.
Romanian codes are checked against **ANAF**, the rest of the EU against **VIES**:

```php
use AntonioPrimera\Cif\Lookup\VatLookup;

$result = (new VatLookup())->lookup('RO46801317'); // or '46801317', 'DE123456789', 'EL094…'

$result->isValid();          // entity exists and is active
$result->vatPayer;           // registered for VAT (→ reverse-charge)
$result->name;               // 'ECOBAT ENERGY S.R.L.'
$result->address;            // fiscal address
$result->postalCode;
$result->registrationNumber; // RO: nr. Reg. Comerțului
$result->source;             // 'anaf' | 'vies'
$result->status;             // 'valid' | 'invalid' | 'unverified'
```

`unverified` means the registry (ANAF/VIES) could not be reached — treat it as "could not
verify", **never** as invalid, so an outage doesn't reject a real VAT number.

The default HTTP client uses `ext-curl`. To use your own (e.g. in tests, or a framework's
HTTP client), implement `Lookup\HttpClient` and inject it:

```php
$lookup = new VatLookup($myHttpClient);
```

> Note: lookups are not cached inside the package — wrap `lookup()` with your own cache
> (ANAF/VIES can be slow), since results for a given VAT number rarely change within a day.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Antonio Primera](https://github.com/AntonioPrimera)

## License

This package is licensed under the [Proprietary Software License](LICENSE.md). Please see the [LICENSE](LICENSE.md) file for more information.
