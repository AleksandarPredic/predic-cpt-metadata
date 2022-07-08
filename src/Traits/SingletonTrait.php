<?php

namespace PredicCptMetadata\Traits;

/**
 * Trait SingletonTrait
 * @package PredicCptMetadata\Traits
 */
trait SingletonTrait
{
    /**
     * Class instance
     * @var SingletonTrait
     */
    private static $instance;

    /**
     * Return Class instance
     */
    final public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
