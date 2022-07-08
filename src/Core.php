<?php

namespace PredicCptMetadata;

use PredicCptMetadata\Helpers\PluginData;
use PredicCptMetadata\Traits\SingletonTrait;

class Core
{
    use SingletonTrait;

    /**
     * Plugin database version.
     * Change this if you need to apply the modifications to the DB tables on the plugin activation
     */
    public const PLUGIN_DB_VERSION = '1.0.0';

    /**
     * Action hook to use for adding missing DB tables
     */
    public const MISSING_TABLE_ACTION_HOOK = 'predic_cpt_metadata_add_missing_db_table_for_post_type';

    /**
     * Action hook to use for debugging data on admin page
     */
    public const DEBUG_DATA_BY_POST_TYPE_BY_POST_ID_ACTION_HOOK = 'predic_cpt_metadata_debug_data_for_post_type_by_post_id';

    /**
     * Action hook to use for removing leftover data on admin page
     */
    public const CLEAN_LEFTOVER_DATA_BY_POST_TYPE_ACTION_HOOK = 'predic_cpt_metadata_clean_leftover_data_for_post_type';

    /**
     * Plugin debug on/off
     * @var bool
     */
    public const DEBUG_LOG_ENABLED = PREDIC_CPT_METADATA_DEBUG_ENABLED;

    private PluginData $pluginData;
    private string $pluginSlug;

    private function __construct()
    {
        $this->pluginData = PluginData::getInstance();
        $this->pluginSlug = $this->pluginData->getSlug();
    }

    /**
     * Return plugin slug
     * @return string
     */
    public function getPluginSlug(): string
    {
        return $this->pluginSlug;
    }
}
