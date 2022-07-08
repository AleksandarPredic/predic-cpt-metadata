<?php

namespace PredicCptMetadata\Database;

use PredicCptMetadata\Contracts\LoggerInterface;
use PredicCptMetadata\Core;
use PredicCptMetadata\Helpers\LoggerHelper;
use PredicCptMetadata\Helpers\PluginData;
use PredicCptMetadata\Traits\MetaDBFunctions;

/**
 * Main class internal API to use for creating tables
 */
class MetaTable
{
    use MetaDBFunctions;

    /**
     * DB tables version to compare on the plugin activation
     *
     * @var string
     */
    private string $version;

    /**
     * Table row name that we return from the class that extends this.
     * Basically, this will be a CPT id
     * Example: casino
     *
     * @var string
     */
    private string $postTypeObject;

    /**
     * Table name without the $wbdb prefix but prefixed with our plugin prefix.
     * Which will give us unique object name with our plugin prefix
     * Example: {plugin_slug}_{CPT}meta
     *
     * @var string
     */
    private string $objectName;

    /**
     * WP Database table name with the $wbdb prefix and the suffix "meta" at the end
     * We add the suffix to comply with the WP meta db tables naming convention
     *
     * @see https://pippinsplugins.com/extending-wordpress-metadata-api/
     *
     * Example: wp_18_{plugin_slug}_{CPT}meta
     * @var string
     */
    private string $tableWpdbName;

    /**
     * Option key to store db version so we can compare it when the plugin is activated
     * Example: predic_cpt_metadata_{CPT}_db_version
     *
     * @var string
     */
    private string $optionDBVersion;

    private PluginData $pluginData;
    private LoggerInterface $logger;

    /**
     * @param int $postTypeId Id of the custom post type.
     */
    public function __construct($postTypeId)
    {
        $this->pluginData = PluginData::getInstance();
        $this->logger = new LoggerHelper();
        $this->postTypeObject = sanitize_text_field($postTypeId);
        $this->version = Core::PLUGIN_DB_VERSION;
        $this->postTypeObject = str_replace('-', '_', $this->postTypeObject);
        $this->objectName = $this->getObjectName($this->postTypeObject, $this->pluginData->getSlug());
        $this->tableWpdbName = $this->prefixWpdbTableName($this->objectName);

        /**
         * Use option key for every table even as it will have the same version,
         * but will not execute after the first one update the option with the common version
         */
        $this->optionDBVersion = $this->objectName . '_db_version';
    }

    /**
     * Create the table
     *
     * @access  public
     * @since   1.0
     *
     * @return array Strings containing the results of the various update queries.
     */
    public function createTable(): array
    {
        if ($this->version === $this->getCurrentDBVersion()) {
            return [ esc_html__('No changes in the DB version. No actions done!', 'predic-cpt-metadata') ];
        }

        global $wpdb;

        $wpdb_collate = $wpdb->collate;
        $object_id = "{$this->objectName}_id";

        $sql = "CREATE TABLE {$this->tableWpdbName} (
		meta_id bigint(20) NOT NULL auto_increment,
		{$object_id} bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY (meta_id),
		KEY {$object_id}_id ({$object_id}),
		KEY meta_key (meta_key)
		) ENGINE=InnoDB COLLATE {$wpdb_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        try {
            $result = dbDelta($sql);

            update_option($this->optionDBVersion, $this->version);
        } catch (\Exception $e) {
            $this->logger->log(
                sprintf(
                    'Error: createTable, within CPT metadata plugin, failed. Message: %s',
                    $e->getMessage()
                ),
                $e->getCode()
            );

            throw $e;
        }

        return $result;
    }

    /**
     * Register the meta table to the #wpdb. Extending the metadata API
     */
    public function registerMetadataTable()
    {
        global $wpdb;

        $property = $this->objectName . 'meta';
        $wpdb->$property = $this->tableWpdbName;
        $wpdb->tables[] = $property;
    }

    public function tableExists(): bool
    {
        global $wpdb;

        return $wpdb->get_var("SHOW TABLES LIKE '$this->tableWpdbName'") === $this->tableWpdbName;
    }

    public function getCurrentDBVersion()
    {
        return get_option($this->optionDBVersion);
    }

    public function resetCurrentDBVersion(): bool
    {
        return delete_option($this->optionDBVersion);
    }
}
