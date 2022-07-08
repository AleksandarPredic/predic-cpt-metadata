<?php

namespace PredicCptMetadata\Contracts;

interface AdminSettingsPageInterface
{
    /**
     * Register admin menu page
     */
    public function registerPage();

    /**
     * Render admin menu page content
     */
    public function render();

    /**
     * Return admin page url
     * @return string
     */
    public function getAdminPageUrl();
}
