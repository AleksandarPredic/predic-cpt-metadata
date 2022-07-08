<?php

namespace PredicCptMetadata\Admin;

use PredicCptMetadata\Contracts\AdminSettingsPageInterface;
use PredicCptMetadata\Core;
use PredicCptMetadata\Database\MetaTable;
use PredicCptMetadata\Helpers\Filters;
use PredicCptMetadata\Traits\SingletonTrait;

class AdminSettingsPage implements AdminSettingsPageInterface
{
    use SingletonTrait;

    private const MISSING_TABLE_ACTION_HOOK = Core::MISSING_TABLE_ACTION_HOOK;
    private const DEBUG_DATA_ACTION_HOOK = Core::DEBUG_DATA_BY_POST_TYPE_BY_POST_ID_ACTION_HOOK;
    private const CLEAN_LEFTOVER_DATA_BY_POST_TYPE_HOOK = Core::CLEAN_LEFTOVER_DATA_BY_POST_TYPE_ACTION_HOOK;

    private $menuSlug;
    private Filters $filters;

    private function __construct()
    {
        $this->filters = Filters::getInstance();
        $this->menuSlug = sprintf('%s-settings-page', Core::getInstance()->getPluginSlug());
    }

    public function registerPage()
    {
        $title = esc_html__('CPT metadata settings', 'predic-cpt-metadata');
        add_menu_page(
            $title,
            $title,
            'administrator',
            $this->menuSlug,
            [$this, 'render'],
            null,
            40
        );
    }

    public function render()
    {
        $postTypeIds = $this->filters->getExternalPostTypeObjects();
        $pageTitle = '<h1>All registered DB tables</h1>';

        if (empty($postTypeIds)) {
            echo <<<HTML
                {$pageTitle}
                <div class="notice notice-info"><p>No registered post types!</p></div>
            HTML;

            return;
        }

        $tableRows = '';
        foreach ($postTypeIds as $postTypeId) {
            $metaTable = new MetaTable($postTypeId);

            $adminActionUrl = add_query_arg(
                [
                    'action' => static::MISSING_TABLE_ACTION_HOOK,
                    'postTypeId' => $postTypeId,
                    'security' => wp_create_nonce(static::MISSING_TABLE_ACTION_HOOK)
                ],
                admin_url('admin-post.php')
            );

            $button =
                $metaTable->tableExists()
                    ? sprintf(
                          '<span class="dashicons dashicons-saved" style="color: green; font-size: 30px;"></span>%s%s',
                        $this->addDebugForm($postTypeId),
                        $this->addCleanLeftoverDataForm($postTypeId)
                      )
                    : sprintf(
                        '<a href="%s" class="button button-primary" style="background-color: red; border-color: red;">Fix issue!</a>',
                        esc_url_raw($adminActionUrl)
                    );
            $tableRows .= <<<HTML
                <tr>
                    <th scope="row"><label>&nbsp;&nbsp;&nbsp;{$postTypeId}</label></th>
                    <td>{$button}</td>
                </tr>
            HTML;
        }
        unset($metaTable);

        $notice = '';
        if (isset($_REQUEST['predic_cpt_metadata_msg']) && ! empty($_REQUEST['predic_cpt_metadata_msg'])) {
            $message = strip_tags($_REQUEST['predic_cpt_metadata_msg']);
            $notice = '<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>';
        }

        echo <<<HTML
            {$pageTitle}
            {$notice}
            <p>
                If you see a red button, click it.
                If the button doesn't turn into the green checkmark, please contact the devs.
            </p>
            <table class="form-table striped widefat">
                <tbody>{$tableRows}</tbody>
            </table>
        HTML;
    }

    /**
     * Return admin page url
     * @return string
     */
    public function getAdminPageUrl()
    {
        return admin_url('admin.php?page=' . $this->menuSlug);
    }

    /**
     * Add debug for to test data on admin page
     *
     * @param string $postTypeId
     *
     * @return string
     */
    private function addDebugForm($postTypeId)
    {
        $adminUrl = admin_url('admin-post.php');
        $action = static::DEBUG_DATA_ACTION_HOOK;
        $nonce = wp_create_nonce($action);

        return <<<HTML
                <tr>
                    <th scope="row"><label>&nbsp;&nbsp;&nbsp;Debug data</label></th>
                    <td>
                        <form action="{$adminUrl}" method="get" target="_blank">
                            <input type="hidden" name="action" value="{$action}" />
                            <input type="text" name="post_id" value="" placeholder="post ID"/>
                            <input type="hidden" name="post_type_id" value="{$postTypeId}" />
                            <input type="hidden" name="security" value="{$nonce}" />
                            <button type="submit" class="button button-secondary">Debug</button>
                        </form>
                    </td>
                </tr>
        HTML;
    }

    /**
     * Add clean leftover data form on admin page, per supported post type
     *
     * @param string $postTypeId
     *
     * @return string
     */
    private function addCleanLeftoverDataForm($postTypeId)
    {
        $adminUrl = admin_url('admin-post.php');
        $action = static::CLEAN_LEFTOVER_DATA_BY_POST_TYPE_HOOK;
        $nonce = wp_create_nonce($action);

        return <<<HTML
                <tr>
                    <th scope="row"><label>&nbsp;&nbsp;&nbsp;Clean leftover data</label></th>
                    <td>
                        <form action="{$adminUrl}" method="get" target="_blank">
                            <input type="hidden" name="action" value="{$action}" />
                            <input type="hidden" name="post_type_id" value="{$postTypeId}" />
                            <input type="hidden" name="security" value="{$nonce}" />
                            <button type="submit" class="button button-primary" onclick="return confirm('Are you sure?')">Clean</button>
                        </form>
                        <p>Important: This will remove all data form custom DB tables for any post ID that doesn't exists anymore!</p>
                    </td>
                </tr>
        HTML;
    }
}
