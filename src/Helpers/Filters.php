<?php

namespace PredicCptMetadata\Helpers;

use PredicCptMetadata\Traits\SingletonTrait;

/**
 * Filters that provides data from other plugins.
 * Not for model operations but for other data this plugin depend on, like for which post types we make support.
 */
class Filters
{
    use SingletonTrait;

    private function __constructor()
    {
    }

    /**
     * Return registered post type ids to use this plugin.
     * This filter is intended to be used in other plugins
     * @return array
     */
    public function getExternalPostTypeObjects(): array
    {
        return apply_filters('predic_cpt_metadata_post_type_objects', []);
    }
}
