<?php

namespace PredicCptMetadata\Traits;

trait MetaDBFunctions
{
    /**
     * Get DB object name {plugin_slug}_{post_type_id} converted to all underscores
     * @param string $postTypeId
     * @param string $pluginSlug
     *
     * @return string
     */
    private function getObjectName($postTypeId, $pluginSlug)
    {
        return sanitize_text_field(
            sprintf(
                '%s_%s',
                str_replace('-', '_', $pluginSlug),
                str_replace('-', '_', $postTypeId)
            )
        );
    }

    /**
     * Prefix the table name with the $wbdb prefix and the "meta" suffix to comply with the WP meta tables conventions
     *
     * @param string $objectName
     *
     * @return string
     */
    private function prefixWpdbTableName($objectName): string
    {
        global $wpdb;

        return $wpdb->prefix . $objectName . 'meta';
    }

    /**
     * Get object ID. We will just append _id to the object name which will give us the DB table column name
     * @param string $objectName
     *
     * @return string
     */
    private function getObjectId($objectName): string
    {
        return "{$objectName}_id";
    }
}
