<?php
namespace AntonioPrimera\Cif\Lookup;

/**
 * Thrown when an online registry call fails at transport level (timeout, DNS,
 * non-2xx, invalid JSON). A failed call is treated as "unverified", never as
 * "invalid" — a registry being down must not flag a real VAT number as wrong.
 */
class HttpException extends \RuntimeException {}
