<?php

namespace {Namespace}\Services;

use {Namespace}\Contracts\CptMetadataServiceInterface;
use {Namespace}\Contracts\ServicePluginInterface;

/**
 * Service for the predic-cpt-metadata plugin integration
 * @link https://github.com/AleksandarPredic/predic-cpt-metadata
 */
class CptMetadataService implements ServicePluginInterface, CptMetadataServiceInterface
{
    /**
     * Define for which custom post types we will add support for
     */
    private const CUSTOM_POST_TYPES = [
        'post-type-id' // The id when you registered the post type
    ];

    public function __construct()
    {
    }

    public function getPluginName(): string
    {
        return 'Predic CPT metadata plugin';
    }

    public function isPluginActive(): bool
    {
        return defined('PREDIC_CPT_METADATA_PLUGIN_FILE');
    }

    /**
     * Register support for all CPTs that will use this plugin
     */
    public function registerCustomPostTypes()
    {
        add_filter('predic_cpt_metadata_post_type_objects', function (array $postTypes): array {
            return array_merge(
                $postTypes,
                static::CUSTOM_POST_TYPES
            );
        });
    }

    /**
     * Get all meta from the database and return array
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     *
     * @return array
     */
    public function getAllMeta(string $postTypeId, int $postId): array
    {
        if (! has_filter('predic_cpt_metadata_post_type_get_all_meta')) {
            return [];
        }

        return apply_filters(
            'predic_cpt_metadata_post_type_get_all_meta',
            $postTypeId,
            $postId
        );
    }

    /**
     * Get the single meta
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     * @param string $metaKey Metadata key. Meta table column to retrieve data for.
     * @param bool $single Optional. If true, return only the first value of the specified meta_key.
     *
     * @return mixed Single metadata value, or array of values
     */
    public function getMeta(string $postTypeId, int $postId, string $metaKey)
    {
        if (! has_filter('predic_cpt_metadata_post_type_get_meta')) {
            return '';
        }

        return apply_filters(
            'predic_cpt_metadata_post_type_get_meta',
            $postTypeId,
            $postId,
            $metaKey
        );
    }

    /**
     * Return all results for the meta key. Return array of objects in format
     * array (
    0 => array (
    'predic_cpt_metadata_book_id' => '5',
    'meta_key' => 'test_key',
    'meta_value' => 'value_test',
    ),
    )
     * @param string $postTypeId
     * @param string $metaKey
     *
     * @return array|null
     */
    public function getAllForMetaKey(string $postTypeId, string $metaKey): array
    {
        if (! has_filter('predic_cpt_metadata_post_type_get_all_meta_for_meta_key')) {
            return [];
        }

        return apply_filters(
            'predic_cpt_metadata_post_type_get_all_meta_for_meta_key',
            $postTypeId,
            $metaKey
        );
    }

    /**
     * Return all meta for given post ids.
     * Every row is returned separately even if it is the same post_id.
     *
     * @param string $postTypeId Post type id as string, the one you used to register the CPT
     * @param array $ids Array of post type ID. DB column post_id as integer, not CPT id
     *
     * @return array|null
     */
    public function getAllByIds(string $postTypeId, array $ids): array
    {
        if (! has_filter('predic_cpt_metadata_post_type_get_all_meta_by_ids')) {
            return [];
        }

        return apply_filters(
            'predic_cpt_metadata_post_type_get_all_meta_by_ids',
            $postTypeId,
            $ids
        );
    }

    /**
     * Update or create the data for the meta key
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     * @param string $metaKey Metadata key. Meta table column to retrieve data for.
     * @param mixed $data Cache value. Must be serializable if non-scalar.
     *
     * @return bool
     */
    public function updateMeta(string $postTypeId, int $postId, string $metaKey, $data): bool
    {
        if (! has_action('predic_cpt_metadata_post_type_update_meta')) {
            return false;
        }

        try {
            do_action(
                'predic_cpt_metadata_post_type_update_meta',
                $postTypeId,
                $postId,
                $metaKey,
                $data
            );

            return true;
        } catch (\Exception $e) {
            // Log Exception if you wish :)

            return false;
        }
    }

    /**
     * Delete the data for the meta key and post ID
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     * @param string $metaKey Metadata key. Meta table column to retrieve data for.
     *
     * @return bool True on successful delete, false on failure.
     */
    public function deleteMeta(string $postTypeId, int $postId, string $metaKey): bool
    {
        if (! has_action('predic_cpt_metadata_post_type_delete_meta')) {
            return false;
        }

        try {
            do_action(
                'predic_cpt_metadata_post_type_delete_meta',
                $postTypeId,
                $postId,
                $metaKey
            );

            return true;
        } catch (\Exception $e) {
            // Log Exception if you wish :)

            return false;
        }
    }
}
