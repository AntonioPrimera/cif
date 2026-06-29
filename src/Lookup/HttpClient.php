<?php
namespace AntonioPrimera\Cif\Lookup;

/**
 * Minimal HTTP abstraction used by the online VAT registries (ANAF / VIES).
 * Inject your own implementation (e.g. a Guzzle/PSR-18 adapter) in tests or in
 * frameworks that already ship an HTTP client. A zero-dependency cURL default
 * is provided (see CurlHttpClient).
 */
interface HttpClient
{
    /**
     * POST a JSON body and return the decoded JSON response as an array.
     *
     * @param  array<mixed>  $body
     * @return array<mixed>
     *
     * @throws HttpException on transport error, non-2xx status or invalid JSON
     */
    public function postJson(string $url, array $body, float $timeout = 8.0): array;
}
