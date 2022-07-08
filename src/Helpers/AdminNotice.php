<?php

namespace PredicCptMetadata\Helpers;

use PredicCptMetadata\Contracts\AdminNoticesInterface;

/**
 * Class AdminNotice
 * @package PredicCptMetadata\Helpers
 */
class AdminNotice implements AdminNoticesInterface
{
    private string $type;

    /**
     * @inheritDoc
     */
    public function setTypeSuccess(): AdminNotice
    {
        $this->type = 'notice-success';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTypeWarning(): AdminNotice
    {
        $this->type = 'notice-warning';

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setTypeError(): AdminNotice
    {
        $this->type = 'notice-error ';

        return $this;
    }

    /**
     * Add text to display in the notice
     * @param string $text
     */
    public function add($text): void
    {
        add_action(
            'admin_notices',
            function () use ($text) {
                printf(
                    '<div class="notice %1$s"><p>%2$s</p></div>',
                    esc_attr($this->type),
                    wp_kses_data($text)
                );
            }
        );
    }
}
