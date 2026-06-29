# Changelog

---
### `v1.1.0 - 2026.06.29`

Added an online VAT **registry lookup** layer — fully additive and backwards-compatible.
The existing `Cif` / `CifValidator` format validation is **unchanged** (all prior tests pass).

- `Lookup\VatLookup` — routes RO codes to **ANAF** (v9) and the rest of the EU to **VIES**,
  returning existence/active status **plus company data** (name, address, postal code,
  registration number, VAT-payer flag).
- `Lookup\VatLookupResult` — result DTO with status `valid` | `invalid` | `unverified`.
- `Lookup\AnafRegistry` (reuses the RO checksum from `Cif` as a format gate) + `Lookup\ViesRegistry`.
- `Lookup\HttpClient` interface + zero-dependency `Lookup\CurlHttpClient` default; inject your own
  (e.g. a Guzzle/PSR-18 adapter). `ext-curl` is **suggested, not required**.
- A registry that is down/unreachable returns `unverified` — never `invalid`.

---
### `v1.0.0 - 2025.05.19`

Created the Cif class and the CifValidator class.
Created the cif() helper function as a shortcut to the Cif::from() method.
