<?php //phpcs:disable SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming.SuperfluousSuffix
namespace XWP\Dependency\Interfaces;

use XWP\Contracts\Hook\Context;

/**
 * Bundle represents a collection of assets.
 *
 * @property-read int                            $priority Bundle priority.
 * @property-read array<string, Asset_Interface> $assets   Bundle assets.
 */
interface Bundle_Interface extends Enqueueable {
    /**
     * Gets an asset by its handle.
     *
     * @param  string $handle Asset handle.
     * @return Asset_Interface|null
     */
    public function get_asset( string $handle ): ?Asset_Interface;
}
