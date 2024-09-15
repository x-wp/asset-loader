<?php
/**
 * Can_Enqueue interface file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

namespace XWP\Dependency\Interfaces;

/**
 * Interface describing assets that can be enqueued.
 */
interface Can_Enqueue {
    /**
     * Get enqueue mode of the asset.
     *
     * @return 'auto'|'manual'
     */
    public function mode(): string;

    /**
     * Enqueue the asset.
     *
     * @param  string $mode The mode of the asset.
     * @return bool True if the asset is enqueued successfully, false otherwise.
     */
    public function enqueue( string $mode = 'auto' ): bool;

    /**
     * Dequeue the asset.
     *
     * @return bool True if the asset is dequeued successfully, false otherwise.
     */
    public function dequeue(): bool;
}
