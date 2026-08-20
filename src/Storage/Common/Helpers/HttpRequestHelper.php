<?php

declare(strict_types=1);

namespace AzureOss\Storage\Common\Helpers;

/**
 * Normalizes request values accepted by both supported Guzzle major versions.
 *
 * @internal
 */
final class HttpRequestHelper
{
    /**
     * @param  array<string, string|null>  $headers
     * @return array<string, non-empty-array<string>>
     */
    public static function headers(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $value) {
            if ($value !== null) {
                $normalized[$name] = [$value];
            }
        }

        return $normalized;
    }

    /** Serializes an XML request body, failing before dispatch when serialization fails. */
    public static function xml(\SimpleXMLElement $xml): string
    {
        $body = $xml->asXML();
        if ($body === false) {
            throw new \RuntimeException('Unable to serialize the XML request body.');
        }

        return $body;
    }
}
