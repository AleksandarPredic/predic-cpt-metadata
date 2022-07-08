<?php

namespace {Namespace}\Contracts;

interface CptMetadataServiceInterface
{

    /**
     * Register support for all CPTs that will use this plugin
     */
    public function registerCustomPostTypes();

    /**
     * Get all meta from the database and return array in format array( meta_key => value, meta_key => value... )
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     *
     * @return array
     */
    public function getAllMeta(string $postTypeId, int $postId): array;

    /**
     * Get the single meta
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     * @param string $metaKey Metadata key. Meta table column to retrieve data for.
     *
     * @return mixed Single metadata value
     */
    public function getMeta(string $postTypeId, int $postId, string $metaKey);

    /**
     * Return all results for the meta key in format
     * * array(1) {
     * [0]=>
     * object(stdClass) {
     * ["post_id"]=> "759"
     * ["meta_value"]=> "194"
     * }
     * }
     * @param string $postTypeId
     * @param string $metaKey
     *
     * @return array
     */
    public function getAllForMetaKey(string $postTypeId, string $metaKey): array;

    /**
     * Return all meta for given post ids.
     * Every row is returned separately even if it is the same post_id.
     * * array(1) {
     * [0]=>
     * object(stdClass) {
     * ["post_id"]=> "102"
     * ["meta_key"]=> "casinolib_id"
     * ["meta_value"]=> "132"
     * }
     * }
     * @param string $postTypeId Post type id as string, the one you used to register the CPT
     * @param array $ids Array of post type ID. DB column post_id as integer, not CPT id
     *
     * @return array
     */
    public function getAllByIds(string $postTypeId, array $ids): array;

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
    public function updateMeta(string $postTypeId, int $postId, string $metaKey, $data): bool;

    /**
     * Delete the data for the meta key and post ID
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     * @param string $metaKey Metadata key. Meta table column to retrieve data for.
     *
     * @return bool True on successful delete, false on failure.
     */
    public function deleteMeta(string $postTypeId, int $postId, string $metaKey): bool;
}
