<?php

namespace Bojaghi\MetaBoxes\Tests;

use Bojaghi\MetaBoxes\MetaBoxes;
use WP_UnitTestCase;

class TestMetaBoxes extends WP_UnitTestCase
{
    public function test_meta_boxes(): void
    {
        global $wp_meta_boxes;

        set_current_screen('test');
        $screen = get_current_screen();

        $this->assertEquals('test', $screen->id);

        $invoked = false;

        $boxes = new MetaBoxes(
            [
                'add' => [
                    [
                        'id'       => 'test-metabox-1',
                        'title'    => 'Test Metabox #1',
                        'callback' => function () use (&$invoked) { $invoked = true; },
                        'screen'   => $screen,
                        'context'  => MetaBoxes::CONTEXT_NORMAL,
                    ],
                    [
                        'id'       => 'test-metabox-2',
                        'title'    => 'Test Metabox #2',
                        'callback' => function () { },
                        'screen'   => $screen,
                        'context'  => MetaBoxes::CONTEXT_NORMAL,
                    ],
                ],
            ],
        );

        // Check if two meta boxes are properly registered.
        $this->assertNotEmpty($wp_meta_boxes['test'][MetaBoxes::CONTEXT_NORMAL][MetaBoxes::PRIORITY_DEFAULT]);

        $addBoxes = $wp_meta_boxes['test'][MetaBoxes::CONTEXT_NORMAL][MetaBoxes::PRIORITY_DEFAULT];
        $this->assertCount(2, $addBoxes);
        $this->assertArrayHasKey('test-metabox-1', $addBoxes);
        $this->assertEquals('test-metabox-1', $addBoxes['test-metabox-1']['id']);
        $this->assertArrayHasKey('test-metabox-2', $addBoxes);
        $this->assertEquals('test-metabox-2', $addBoxes['test-metabox-2']['id']);

        // Test dispatch call
        $boxes->dispatch(null, ['id' => 'test-metabox-1']);
        // Check if the function is invoked
        $this->assertTrue($invoked);

        new MetaBoxes(
            [
                'remove' => [
                    [
                        'id'      => 'test-metabox-2',
                        'screen'  => $screen,
                        'context' => MetaBoxes::CONTEXT_NORMAL,
                    ],
                ],
            ],
        );

        // Check if test-metabox-2 is unregistered.
        $removeBoxes = $wp_meta_boxes['test'][MetaBoxes::CONTEXT_NORMAL][MetaBoxes::PRIORITY_DEFAULT];
        // remove_meta_box does not kill items, it just sets the value as 'false'.
        $this->assertCount(2, $removeBoxes);
        $this->assertEquals('test-metabox-1', $removeBoxes['test-metabox-1']['id']);
        $this->assertFalse($removeBoxes['test-metabox-2']); // 'false'
    }
}
