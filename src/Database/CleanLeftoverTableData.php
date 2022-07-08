<?php

namespace PredicCptMetadata\Database;

use PredicCptMetadata\Core;
use PredicCptMetadata\Database\Models\ModelCustomPostTypeMeta;

/**
 * Class used in the admin page
 * We will use this to remove all data for deleted posts as the data may still be in our custom DB tables.
 * That data can cause that it looks like we still have this posts.
 *
 * When the post is deleted we remove the data for this post automatically, so if you ever feel that some data was not
 * removed just run this class, and it will verify this or remove the leftover data.
 */
class CleanLeftoverTableData
{
    private const ACTION_HOOK = Core::CLEAN_LEFTOVER_DATA_BY_POST_TYPE_ACTION_HOOK;

    private ModelCustomPostTypeMeta $model;

    public function __construct()
    {
        $this->model = ModelCustomPostTypeMeta::getInstance();
    }

    public function initHooks()
    {
        add_action('admin_post_' . self::ACTION_HOOK, [$this, 'clean']);
    }

    /**
     * Clean leftover data for non-existing posts in CPT.
     *
     * We basically get all post ids in custom table, get all existing post ids for that CPT and compare if we have
     * some extra records to remove in our custom db tables.
     */
    public function clean()
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

        if (! isset($_REQUEST['post_type_id']) || empty($_REQUEST['post_type_id'])) {
            wp_die(new \WP_Error(
                406,
                'Request incomplete!'
            ));
        }

        $postTypeId = sanitize_text_field($_REQUEST['post_type_id']);

        $customTablepostIds = $this->model->getAllPostIds(
            $_REQUEST['post_type_id']
        );

        $query = new \WP_Query(
            [
                'post_type' => $postTypeId,
                'fields' => 'ids',
                'cache_results'  => false,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'posts_per_page' => -1,
                'nopaging' => true,
            ]
        );


        // Get existing posts ids
        $existingPostIds = [];
        if ($query->have_posts()) {
            $existingPostIds = $query->get_posts();
        }

        // Find which post doesn't exist anymore
        $idsToRemove = array_diff(
            $customTablepostIds,
            $existingPostIds
        );

        // Start dev styled output
        echo '<pre>';

        printf('<h2>Clean leftover data results for post type %s</h2>', $postTypeId);

        $styleGreen = 'style="background-color: green; color: white; padding: 5px;"';
        $styleRed = 'style="background-color: red; color: white; padding: 5px;"';
        if (empty($idsToRemove)) {
            printf(
                '<p %s>Nothing to clean! There is no leftover data!</p>',
                $styleGreen
            );
        }

        // Remove data for non existing posts
        foreach ($idsToRemove as $ID) {
            echo $this->model->deleteAllForPostId($postTypeId, $ID)
                ? sprintf(
                    '<p %2$s>Successfully removed data for post id: %1$d</p>',
                    $ID,
                    $styleGreen
                )
                : sprintf(
                    '<p %2$s>Failed to remove data for post id: %1$d</p>',
                    $ID,
                    $styleRed
                );
        }

        echo '<pre>';
        die();
    }
}
