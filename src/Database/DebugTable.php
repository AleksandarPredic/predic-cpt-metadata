<?php

namespace PredicCptMetadata\Database;

use PredicCptMetadata\Core;
use PredicCptMetadata\Database\Models\ModelCustomPostTypeMeta;

/**
 * Class used in the admin page at the debug section.
 * We will use this to get all the values from table and display it on this page.
 * This way we can easily check if some data exists to more easily debug problems.
 */
class DebugTable
{
    private const ACTION_HOOK = Core::DEBUG_DATA_BY_POST_TYPE_BY_POST_ID_ACTION_HOOK;

    private ModelCustomPostTypeMeta $model;

    public function __construct()
    {
        $this->model = ModelCustomPostTypeMeta::getInstance();
    }

    public function initHooks()
    {
        add_action('admin_post_' . self::ACTION_HOOK, [$this, 'debug']);
    }

    /**
     * Get all meta for post ID
     */
    public function debug()
    {
        if (
            ! current_user_can('administrator')
            || ! wp_verify_nonce($_REQUEST['security'], self::ACTION_HOOK)
        ) {
            wp_die(new \WP_Error(
                503,
                'You are not allowed to do this!'
            ));
        }

        if (! isset($_REQUEST['post_id']) || empty($_REQUEST['post_id'])) {
            wp_die(new \WP_Error(
                406,
                'Request incomplete!'
            ));
        }

        if (! isset($_REQUEST['post_type_id']) || empty($_REQUEST['post_type_id'])) {
            wp_die(new \WP_Error(
                406,
                'Request incomplete!'
            ));
        }

        echo '<pre>';
        var_dump(
            $this->model->getAll(
                $_REQUEST['post_type_id'],
                intval($_REQUEST['post_id'])
            )
        );
        die();
    }
}
