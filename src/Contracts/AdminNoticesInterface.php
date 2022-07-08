<?php

namespace PredicCptMetadata\Contracts;

/**
 * Interface AdminNoticesInterface
 * @package PredicCptMetadata\Contracts
 */
interface AdminNoticesInterface
{
    /**
     * Add text to display in the notice
     * @param string $text
     */
    public function add(string $text): void;

    /**
     * Set notice type
     * @return $this
     */
    public function setTypeSuccess(): AdminNoticesInterface;

    /**
     * Set notice type
     * @return $this
     */
    public function setTypeWarning(): AdminNoticesInterface;

    /**
     * Set notice type
     * @return $this
     */
    public function setTypeError(): AdminNoticesInterface;
}
