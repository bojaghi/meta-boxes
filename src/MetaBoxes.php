<?php
declare(strict_types=1);

namespace Bojaghi\MetaBoxes;

use Bojaghi\Contract\Module;
use Bojaghi\Helper\Helper;

class MetaBoxes implements Module
{
    const PRIORITY_HIGH    = 'high';
    const PRIORITY_CORE    = 'core';
    const PRIORITY_DEFAULT = 'default';
    const PRIORITY_LOW     = 'low';

    const CONTEXT_ADVANCED = 'advanced';
    const CONTEXT_NORMAL   = 'normal';
    const CONTEXT_SIDE     = 'side';

    /** @var array<string, string|array|callable> */
    private array $realCallbacks = [];

    /**
     * @param array|string $args Setup array
     */
    public function __construct(array|string $args = [])
    {
        $args = wp_parse_args(Helper::loadConfig($args), static::getDefaultConfig());

        // Add meta boxes
        if (!empty($args['add'])) {
            foreach ((array)$args['add'] as $item) {
                $item = wp_parse_args($item, static::getDefaultAddConfig());

                if ($item['id'] && $item['screen'] && $item['callback']) {
                    // Assign real callbacks here
                    $this->realCallbacks[$item['id']] = $item['callback'];

                    add_meta_box(
                        id: $item['id'],
                        title: $item['title'],
                        callback: [$this, 'dispatch'], // Callback is replaced here.
                        screen: $item['screen'],
                        context: $item['context'],
                        priority: $item['priority'],
                        callback_args: $item['callback_args'],
                    );
                }
            }
        }

        // Remove meta boxes
        if (!empty($args['remove'])) {
            foreach ((array)$args['remove'] as $item) {
                $item = wp_parse_args($item, static::getDefaultRemoveConfig());

                if ($item['id'] && $item['screen'] && $item['context']) {
                    remove_meta_box(
                        id: $item['id'],
                        screen: $item['screen'],
                        context: $item['context'],
                    );
                }
            }
        }
    }

    public static function getDefaultConfig(): array
    {
        return [
            'add'    => [],
            'remove' => [],
        ];
    }

    public static function getDefaultAddConfig(): array
    {
        return [
            'id'            => '',                       // Required
            'title'         => '',                       // Required
            'callback'      => '',                       // Required
            'screen'        => null,                     // Optional
            'context'       => static::CONTEXT_ADVANCED, // Optional
            'priority'      => static::PRIORITY_DEFAULT, // Optional
            'callback_args' => null,                     // Optional
        ];
    }

    public static function getDefaultRemoveConfig(): array
    {
        return [
            'id'      => '',       // Required
            'screen'  => null,     // Required
            'context' => 'normal', // Required
        ];
    }

    public function dispatch($object, array $setup): void
    {
        if (!isset($setup['id'], $this->realCallbacks[$setup['id']])) {
            return;
        }

        $callback = $this->realCallbacks[$setup['id']];

        if ($callback) {
            call_user_func_array($callback, [$object, $setup]);
        }
    }
}
