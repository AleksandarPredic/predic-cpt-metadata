<?php

namespace PredicCptMetadata\Database\Models;

use PredicCptMetadata\Helpers\PluginData;
use PredicCptMetadata\Traits\MetaDBFunctions;
use PredicCptMetadata\Traits\SingletonTrait;

class ModelCustomPostTypeMeta
{
    use MetaDBFunctions;
    use SingletonTrait;

    private string $pluginSlug;

    private function __construct()
    {
        $this->pluginSlug = PluginData::getInstance()->getSlug();
    }

    /**
     * Get all meta from the database and return array
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     *
     * @return array
     */
    public function getAll($postTypeId, $postId): array
    {
        global $wpdb;

        $objectName = $this->getObjectName($postTypeId, $this->pluginSlug);
        $objectId = $this->getObjectId($objectName);
        $tableWpdbName = $this->prefixWpdbTableName($objectName);

        return $wpdb->get_results(
            "SELECT {$objectId}, meta_key, meta_value FROM {$tableWpdbName} WHERE {$objectId} = {$postId}",
            ARRAY_A
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
    public function getMeta($postTypeId, $postId, $metaKey, $single = true)
    {
        return get_metadata(
            $this->getObjectName($postTypeId, $this->pluginSlug),
            $postId,
            $metaKey,
            $single
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
    public function getAllForMetaKey($postTypeId, $metaKey)
    {
        global $wpdb;

        $objectName = $this->getObjectName($postTypeId, $this->pluginSlug);
        $objectId = $this->getObjectId($objectName);
        $tableWpdbName = $this->prefixWpdbTableName($objectName);

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$objectId} as post_id, meta_value FROM {$tableWpdbName} WHERE meta_key = %s;",
                $metaKey
            ),
            ARRAY_A
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
    public function getAllByIds($postTypeId, $ids)
    {
        global $wpdb;

        $idsImploded = implode(',', $ids);
        $objectName = $this->getObjectName($postTypeId, $this->pluginSlug);
        $objectId = $this->getObjectId($objectName);
        $tableWpdbName = $this->prefixWpdbTableName($objectName);

        return $wpdb->get_results(
        // TODO: Maybe group all by post_id in the SQL query in the future.
            "SELECT {$objectId} as post_id, meta_key, meta_value FROM {$tableWpdbName} WHERE {$objectId} IN ({$idsImploded});",
            ARRAY_A
        );
    }

    /**
     * Return all post ids used in the custom table.
     *
     * @param string $postTypeId Post type id as string, the one you used to register the CPT
     *
     * @return array Array of post ids or an empty array.
     */
    public function getAllPostIds($postTypeId)
    {
        global $wpdb;

        $objectName = $this->getObjectName($postTypeId, $this->pluginSlug);
        $objectId = $this->getObjectId($objectName);
        $tableWpdbName = $this->prefixWpdbTableName($objectName);

        $results = $wpdb->get_results(
            "SELECT {$objectId} as post_id FROM {$tableWpdbName} GROUP BY {$objectId};",
            ARRAY_A
        );

        if (empty($results)) {
            return [];
        }

        return array_map(
            function ($item) {
                return intval($item->post_id);
            },
            $results
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
     * @return int|bool The new meta field ID if a field with the given key didn't exist and was
     *                  therefore added, true on successful update, false on failure.
     */
    public function updateMeta($postTypeId, $postId, $metaKey, $data)
    {
        return update_metadata(
            $this->getObjectName($postTypeId, $this->pluginSlug),
            $postId,
            $metaKey,
            $data
        );
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
    public function deleteMeta($postTypeId, $postId, $metaKey)
    {
        return delete_metadata(
            $this->getObjectName($postTypeId, $this->pluginSlug),
            $postId,
            $metaKey
        );
    }

    /**
     * Remove all data from custom table for post id
     *
     * @param string $postTypeId Id of the custom post type you want the data for.
     * @param int $postId Post ID
     *
     * @return bool
     */
    public function deleteAllForPostId($postTypeId, $postId): bool
    {
        global $wpdb;

        $objectName = $this->getObjectName($postTypeId, $this->pluginSlug);
        $objectId = $this->getObjectId($objectName);
        $tableWpdbName = $this->prefixWpdbTableName($objectName);

        $rowsAffected = $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$tableWpdbName} WHERE {$objectId} = %d;",
                $postId
            )
        );

        return $rowsAffected > 0;
    }
}
