<?php
namespace AntonioPrimera\Cif\Lookup;

/**
 * Zero-dependency HttpClient using ext-curl (suggested, not required — so the
 * format-validation side of the package keeps working without any extension).
 */
class CurlHttpClient implements HttpClient
{
    public function postJson(string $url, array $body, float $timeout = 8.0): array
    {
        if (!function_exists('curl_init'))
            throw new HttpException('ext-curl is required for online VAT lookups (or inject your own HttpClient).');

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => (int) ceil($timeout),
            CURLOPT_CONNECTTIMEOUT => (int) ceil($timeout),
        ]);

        $raw    = curl_exec($ch);
        $errno  = curl_errno($ch);
        $error  = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno !== 0)
            throw new HttpException("cURL error: {$error}");

        if ($status >= 400)
            throw new HttpException("HTTP {$status}");

        $data = json_decode((string) $raw, true);
        if (!is_array($data))
            throw new HttpException('Invalid JSON response');

        return $data;
    }
}
