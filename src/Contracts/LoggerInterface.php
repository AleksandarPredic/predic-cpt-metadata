<?php

namespace PredicCptMetadata\Contracts;

/**
 * Interface LoggerInterface
 * @package PredicCptMetadata\Contracts
 */
interface LoggerInterface
{
    /**
     * Log errors the WP native way as we don't need more
     *
     * @param string $message
     * @param int $code
     *
     * @return mixed
     */
    public function log($message, $code);
}
