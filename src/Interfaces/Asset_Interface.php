<?php //phpcs:disable SlevomatCodingStandard.Classes.SuperfluousInterfaceNaming.SuperfluousSuffix
namespace XWP\Dependency\Interfaces;

use XWP\Dependency\Enums\EnqueueMode;

/**
 * Describes an asset.
 *
 * @property-read EnqueueMode $mode Enqueue mode.
 * @property-read string      $src  Asset source.
 */
interface Asset_Interface extends Enqueueable {
    public const ASSET_TYPE = null;

    public function register( string $base_uri = '' ): void;
}
