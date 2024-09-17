<?php
/**
 * Has_Context interface file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

namespace XWP\Dependency\Interfaces;

/**
 * Interface describing assets that have context.
 */
interface Has_Context {
    /**
     * Get the context of the asset.
     *
     * @return 'admin'|'front'
     */
    public function ctx(): string;
}
