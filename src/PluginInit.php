<?php

namespace PredicCptMetadata;

use PredicCptMetadata\Admin\AdminSettingsPage;
use PredicCptMetadata\Database\CleanLeftoverTableData;
use PredicCptMetadata\Database\CreateTable;
use PredicCptMetadata\Database\DebugTable;
use PredicCptMetadata\Database\MetaTable;
use PredicCptMetadata\Database\Models\ModelCustomPostTypeMeta;
use PredicCptMetadata\Helpers\AdminNotice;
use PredicCptMetadata\Helpers\Filters;
use PredicCptMetadata\Traits\SingletonTrait;

if (! defined('ABSPATH')) {
    exit('Direct script access denied.');
}

/**
 * Load all theme dependencies
 * @package PredicCptMetadata
 */
class PluginInit
{
    use SingletonTrait;

    private function __construct()
    {
    }

    /**
     * Set plugin required functionality
     */
    public function setInstances()
    {
        /**
         * - Register meta tables and check if some is missing to set the Admin notice.
         * - Registered post types from other plugins.
         * - Add filters support for CRUD operations for other plugins.
         */
        add_action('init', function () {
            // Model actions to be used in other plugins - CRUD operations
            $model = ModelCustomPostTypeMeta::getInstance();

            // Register meta tables, check if some is missing to set the Admin notice, add delete action hook
            $postTypeIds = Filters::getInstance()->getExternalPostTypeObjects();

            if (empty($postTypeIds)) {
                return;
            }

            foreach ($postTypeIds as $postTypeId) {
                // Add action hook to remove data from custom table on CPT delete
                add_action(
                    'after_delete_post',
                    /**
                     * Fires after a post is deleted, at the conclusion of wp_delete_post().
                     *
                     * @param int $postId Post ID.
                     * @param \WP_Post $post Post object.
                     *
                     * @throws \Exception
                     */
                    function ($postId, $post) use ($postTypeIds, $postTypeId, $model) {
                        // Allow deleting only for registered post types to have support for this plugin
                        if (! in_array($post->post_type, $postTypeIds) || ! post_type_exists($postTypeId)) {
                            return;
                        }

                        $result = $model->deleteAllForPostId($postTypeId, $postId);

                        if (! $result) {
                            throw new \Exception(
                                sprintf(
                                    'Deleting data from CPT metadata custom tables failed. Data not deleted for postTypeId: %s, postId: %s',
                                    $postTypeId,
                                    $postId
                                ),
                                500
                            );
                        }
                    },
                    999,
                    2
                );

                // Register custom meta table and check if exists in DB
                $metaTable = new MetaTable($postTypeId);
                $metaTable->registerMetadataTable();
                if (is_admin()) {
                    if ($metaTable->tableExists()) {
                        continue;
                    }

                    // Show admin notice if we don't have tables in db
                    $notice = new AdminNotice();
                    $notice->setTypeError()
                        ->add(
                            sprintf(
                                'Error: A very important DB table, %s, is missing. <b>%s</b>',
                                sanitize_text_field($postTypeId),
                                sprintf(
                                    '<a href="%s">Please visit this page to fix this issue!!!</a>',
                                    esc_url(AdminSettingsPage::getInstance()->getAdminPageUrl())
                                )
                            )
                        );
                }
            }
            unset($metaTable);
            unset($postTypeIds);

            // Action to update meta
            add_action(
                'predic_cpt_metadata_post_type_update_meta',
                function ($postTypeId, $postId, $metaKey, $data) use ($model) {
                    $result = $model->updateMeta($postTypeId, $postId, $metaKey, $data);

                    if ($result) {
                        return;
                    }

                    throw new \Exception(
                        sprintf(
                            'Action hook: predic_cpt_metadata_post_type_update_meta error. Data not updated for postTypeId: %s, postId: %s, metaKey: %s',
                            $postTypeId,
                            $postId,
                            $metaKey
                        ),
                        409
                    );
                },
                1,
                4
            );

            // Action to delete meta
            add_action(
                'predic_cpt_metadata_post_type_delete_meta',
                function ($postTypeId, $postId, $metaKey) use ($model) {
                    $result = $model->deleteMeta($postTypeId, $postId, $metaKey);

                    if ($result) {
                        return;
                    }

                    throw new \Exception(
                        sprintf(
                            'Action hook: predic_cpt_metadata_post_type_delete_meta error. Data not deleted for postTypeId: %s, postId: %s, metaKey: %s',
                            $postTypeId,
                            $postId,
                            $metaKey
                        ),
                        409
                    );
                },
                1,
                3
            );

            add_filter(
                'predic_cpt_metadata_post_type_get_meta',
                function ($postTypeId, $postId, $metaKey) use ($model) {
                    return $model->getMeta($postTypeId, $postId, $metaKey);
                },
                1,
                3
            );

            add_filter(
                'predic_cpt_metadata_post_type_get_all_meta',
                function ($postTypeId, $postId) use ($model) {
                    return $model->getAll($postTypeId, $postId);
                },
                1,
                2
            );

            add_filter(
                'predic_cpt_metadata_post_type_get_all_meta_for_meta_key',
                function ($postTypeId, $metaKey) use ($model) {
                    return $model->getAllForMetaKey($postTypeId, $metaKey);
                },
                1,
                2
            );

            add_filter(
                'predic_cpt_metadata_post_type_get_all_meta_by_ids',
                function ($postTypeId, $ids) use ($model) {
                    return $model->getAllByIds($postTypeId, $ids);
                },
                1,
                2
            );
        }, 99);

        if (is_admin() && ! wp_doing_ajax() && ! wp_doing_cron()) {
            AdminSettingsPage::getInstance()->registerPage();
            $createTable = new CreateTable();
            $createTable->initHooks();
            $debugTable = new DebugTable();
            $debugTable->initHooks();
            $cleanLeftoverTableData = new CleanLeftoverTableData();
            $cleanLeftoverTableData->initHooks();
        }
    }
}
