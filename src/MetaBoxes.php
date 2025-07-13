<?php
declare(strict_types=1);

namespace Bojaghi\MetaBoxes;

use Bojaghi\Contract\Container;
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

    private ?Container $continy = null;

    /** @var array<string, string|array|callable> */
    private array $realCallbacks = [];

    /**
     * @param array $args Setup array
     */
    public function __construct(private array $args = [])
    {
        if ($this->args && !did_action('do_meta_boxes') && !has_action('do_meta_boxes', [$this, 'callback'])) {
            $priority = (int)($args['priority'] ?? '10');
            add_action('do_meta_boxes', [$this, 'callback'], $priority);
        }
    }

    public function callback(): void
    {
        $args = wp_parse_args(Helper::loadConfig($this->args), static::getDefaultConfig());

        // Assign continy here.
        if ($args['continy'] && in_array(Container::class, class_implements($args['continy']))) {
            $this->continy = $args['continy'];
        }

        // Purge initial value.
        $this->args = [];

        // Add meta boxes
        if (!empty($args['add'])) {
            foreach ((array)$args['add'] as $item) {
                $item = wp_parse_args(
                    $item,
                    [
                        'id'            => '',                       // Required
                        'title'         => '',                       // Required
                        'callback'      => '',                       // Required
                        'screen'        => null,                     // Optional
                        'context'       => static::CONTEXT_ADVANCED, // Optional
                        'priority'      => static::PRIORITY_DEFAULT, // Optional
                        'callback_args' => null,                     // Optional
                    ],
                );

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
                $item = wp_parse_args(
                    $item,
                    [
                        'id'      => '',       // Required
                        'screen'  => null,     // Required
                        'context' => 'normal', // Required
                    ],
                );

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
            'add'      => [],
            'remove'   => [],
            'continy'  => null,
            'priority' => 10,
        ];
    }

    public function dispatch($object, array $setup): void
    {
        if (!isset($setup['id'], $this->realCallbacks[$setup['id']])) {
            return;
        }

        $callback = $this->realCallbacks[$setup['id']];

        if ($this->continy) {
            $callback = $this->continy->parseCallback($callback);
        }

        if ($callback) {
            call_user_func_array($callback, [$object, $setup]);
        }
    }
}
