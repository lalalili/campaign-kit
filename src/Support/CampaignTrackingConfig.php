<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

final class CampaignTrackingConfig
{
    /**
     * @return array{
     *     enabled: bool,
     *     driver: string,
     *     dataLayerName: string,
     *     measurementId: ?string,
     *     currency: string,
     *     affiliation: string,
     *     eventMap: array<string, string>
     * }
     */
    public function toFrontendConfig(): array
    {
        $rawTracking = config('campaign-kit.tracking', []);
        $tracking = is_array($rawTracking) ? $rawTracking : [];
        $eventMap = $this->resolveEventMap($tracking['event_map'] ?? null);

        return [
            'enabled'       => (bool) ($tracking['enabled'] ?? true),
            'driver'        => $this->resolveDriver($this->stringFrom($tracking['driver'] ?? null, 'data_layer')),
            'dataLayerName' => $this->stringFrom($tracking['data_layer_name'] ?? null, 'dataLayer'),
            'measurementId' => $this->resolveMeasurementId($tracking['gtag_measurement_id'] ?? null),
            'currency'      => $this->stringFrom($tracking['currency'] ?? null, 'TWD'),
            'affiliation'   => $this->stringFrom($tracking['affiliation'] ?? null),
            'eventMap'      => $eventMap,
        ];
    }

    public function resolveDriver(string $driver): string
    {
        $normalized = strtolower(trim($driver));

        if (in_array($normalized, ['data_layer', 'gtag', 'null'], true)) {
            return $normalized;
        }

        return 'data_layer';
    }

    private function resolveMeasurementId(mixed $measurementId): ?string
    {
        if (! is_string($measurementId)) {
            return null;
        }

        $trimmed = trim($measurementId);

        return $trimmed !== '' ? $trimmed : null;
    }

    /**
     * @return array<string, string>
     */
    private function resolveEventMap(mixed $eventMap): array
    {
        $defaults = [
            'view_promotion'   => 'view_promotion',
            'select_promotion' => 'select_promotion',
            'select_item'      => 'select_item',
            'add_to_cart'      => 'add_to_cart',
        ];

        if (! is_array($eventMap)) {
            return $defaults;
        }

        $normalized = $defaults;

        foreach ($eventMap as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $resolved = $this->stringFrom($value);
            if ($resolved === '') {
                continue;
            }

            $normalized[$key] = $resolved;
        }

        return $normalized;
    }

    private function stringFrom(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }
}
