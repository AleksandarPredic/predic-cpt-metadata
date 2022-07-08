<?php

namespace {Namespace}\Contracts;

/**
 * Interface ServicePluginInterface
 */
interface ServicePluginInterface
{
    /**
     * Return the plugin name that we want to check
     * @return string
     */
    public function getPluginName(): string;

    /**
     * Return if the plugin is active
     * @return bool
     */
    public function isPluginActive(): bool;
}
