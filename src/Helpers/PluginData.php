<?php

namespace PredicCptMetadata\Helpers;

use PredicCptMetadata\Traits\SingletonTrait;

class PluginData
{
    use SingletonTrait;

    private const PLUGIN_FILE = PREDIC_CPT_METADATA_PLUGIN_FILE;

    private string $version;
    private string $textDomain;

    public function __construct()
    {
        if( !function_exists('get_plugin_data') ){
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $data = get_plugin_data(static::PLUGIN_FILE);
        $this->version = $data['Version'];
        $this->textDomain = $data['TextDomain'];
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getSlug()
    {
        return $this->textDomain;
    }
}
