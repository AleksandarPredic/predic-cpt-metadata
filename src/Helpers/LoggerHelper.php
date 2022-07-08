<?php

namespace PredicCptMetadata\Helpers;

use PredicCptMetadata\Contracts\LoggerInterface;
use PredicCptMetadata\Core;

/**
 * Class LoggerHelper
 * @package PredicCptMetadata\Helpers
 */
class LoggerHelper implements LoggerInterface
{
    /**
     * If WP_DEBUG_LOG enabled
     * @var bool
     */
    private $debugLog;

    /**
     * LoggerHelper constructor.
     */
    public function __construct()
    {
        $this->debugLog = Core::DEBUG_LOG_ENABLED;
    }

    /**
     * @inheritDoc
     */
    public function log($message, $code)
    {
        if (! $this->debugLog) {
            return;
        }

        error_log(
            sprintf(
                'Message: %s. Code: %s.',
                $message,
                $code
            )
        );
    }
}
