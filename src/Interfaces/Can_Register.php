<?php
/**
 * Can_Register interface file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

namespace XWP\Dependency\Interfaces;

/**
 * Interface describing assets that can be registered.
 */
interface Can_Register {
    /**
     * Get the handle of the asset.
     *
     * @return string
     */
    public function handle(): string;

    /**
     * Get the dependencies of the asset.
     *
     * @return array<int,string>
     */
    public function deps(): array;

    /**
     * Get the version of the asset.
     *
     * @return string|false|null
     */
    public function version(): string|false|null;

    /**
     * Register the asset.
     *
     * @return bool True if the asset is registered successfully, false otherwise.
     */
    public function register(): bool;

    /**
     * Deregister the asset.
     *
     * @return bool True if the asset is deregistered successfully, false otherwise.
     */
    public function deregister(): bool;
}
