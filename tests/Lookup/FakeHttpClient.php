<?php
namespace AntonioPrimera\Cif\Tests\Lookup;

use AntonioPrimera\Cif\Lookup\HttpClient;
use AntonioPrimera\Cif\Lookup\HttpException;

/**
 * Deterministic HttpClient test double — returns a canned response or simulates a
 * transport failure. Records the called URL so routing (ANAF vs VIES) can be asserted.
 */
class FakeHttpClient implements HttpClient
{
    public ?string $calledUrl = null;

    private function __construct(
        private ?array $response,
        private bool $fail = false,
    ) {}

    /** @param array<mixed> $response */
    public static function returning(array $response): self
    {
        return new self($response);
    }

    public static function failing(): self
    {
        return new self(null, true);
    }

    public function postJson(string $url, array $body, float $timeout = 8.0): array
    {
        $this->calledUrl = $url;

        if ($this->fail)
            throw new HttpException('simulated transport failure');

        return $this->response ?? [];
    }
}
