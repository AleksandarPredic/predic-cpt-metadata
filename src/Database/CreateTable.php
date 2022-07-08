<?php

namespace PredicCptMetadata\Database;

use PredicCptMetadata\Admin\AdminSettingsPage;
use PredicCptMetadata\Core;

/**
 * Class used in the admin page.
 * It that will create a new table on request and use MetaTable API for that
 */
class CreateTable
{
    private const ACTION_HOOK = Core::MISSING_TABLE_ACTION_HOOK;

    private AdminSettingsPage $adminSettingsPage;

    public function __construct()
    {
        $this->adminSettingsPage = AdminSettingsPage::getInstance();
    }

    public function initHooks()
    {
        add_action('admin_post_' . self::ACTION_HOOK, [$this, 'create']);
    }

    /**
     * Create table when the user clicks "Fix it" button on the settings page
     */
    public function create()
    {
        if (
            ! current_user_can('administrator')
            || ! wp_verify_nonce($_REQUEST['security'], self::ACTION_HOOK)
        ) {
            wp_die(new \WP_Error(
                503,
                sprintf(
                    'You are not allowed to do this! Go back to the %s',
                    sprintf(
                        '<a href="%s">Settings page!</a>',
                        esc_url($this->adminSettingsPage->getAdminPageUrl())
                    )
                )
            ));
        }

        if (! isset($_REQUEST['postTypeId']) || empty($_REQUEST['postTypeId'])) {
            wp_die(new \WP_Error(
                406,
                'Request incomplete!'
            ));
        }

        try {
            $metaTable = new MetaTable($_REQUEST['postTypeId']);
            $metaTable->resetCurrentDBVersion();
            $result = array_values($metaTable->createTable());

            wp_redirect(
                add_query_arg(
                    [
                        'predic_cpt_metadata_msg' => urlencode($result[0])
                    ],
                    $this->adminSettingsPage->getAdminPageUrl()
                )
            );
            exit;
        } catch (\Exception $e) {
            wp_die(new \WP_Error(
                $e->getCode(),
                $e->getMessage()
            ));
        }
    }
}
