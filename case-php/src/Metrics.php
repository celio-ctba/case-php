<?php

namespace App;

use Prometheus\CollectorRegistry;
use Prometheus\Storage\APC;

class Metrics
{
    private static $registry;

    public static function getRegistry(): CollectorRegistry
    {
        if (!self::$registry) {
            // APCu é um adaptador de armazenamento em memória eficiente para o PHP.
            self::$registry = new CollectorRegistry(new APC());
        }
        return self::$registry;
    }

    public static function incrementRequestCounter(string $endpointLabel)
    {
        $registry = self::getRegistry();
        $counter = $registry->getOrRegisterCounter(
            'php_api',
            'http_requests_total',
            'Total number of HTTP requests',
            ['endpoint']
        );
        $counter->inc([$endpointLabel]);
    }
}
?>