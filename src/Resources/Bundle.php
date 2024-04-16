<?php //phpcs:disable  SlevomatCodingStandard.Operators.SpreadOperatorSpacing
namespace XWP\Dependency\Resources;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\EnqueueMode;
use XWP\Dependency\Interfaces\Asset_Interface;
use XWP\Dependency\Interfaces\Bundle_Interface;
use XWP\Dependency\Interfaces\Manifest_Interface;

class Bundle implements Bundle_Interface {
    public const CACHE_NONE      = 65535;
    public const CACHE_TRANSIENT = 1;
    public const CACHE_JSON      = 2;
    public const CACHE_OBJECT    = 4;
    public const CACHE_APCU      = 8;
    public const CACHE_FILE      = 16;

    public const DEFAULT_PRIORITY = 50;
    public const DEFAULT_VERSION  = '0.0.0-dev';

    /**
     * Maps `src=>handle`
     *
     * @var array<string, string>
     */
    private readonly array $srcmap;

    /**
     * Create a new bundle.
     *
     * @param  array<string, Asset_Interface> $assets    Bundle assets.
     * @param  string                         $namespace Bundle namespace.
     * @param  string                         $base_uri  Base URI for assets.
     * @param  string                         $base_dir  Base directory for assets.
     * @param  Manifest_Interface             $manifest  Asset manifest.
     * @param  Context                        $context   Execution context.
     * @param  int                            $priority  Bundle priority.
     * @param  string                         $version   Bundle version.
     * @param  int                            $cache     Cache bitmask.
     */
    public function __construct(
        /**
         * Bundle assets.
         *
         * @var array<string, Asset_Interface>
         */
        public readonly array $assets,
        /**
         * Bundle namespace.
         *
         * @var string
         */
        public readonly string $namespace,
        /**
         * Base URI for assets.
         *
         * @var string
         */
        public readonly string $base_uri,
        /**
         * Base directory for assets.
         *
         * @var string
         */
        public readonly string $base_dir,
        /**
         * Asset manifest.
         *
         * @var int
         */
        public readonly Manifest_Interface $manifest,
        /**
         * Execution context.
         *
         * @var Context|null
         */
        public readonly ?Context $context,
        /**
         * Bundle priority.
         *
         * @var int
         */
        public readonly int $priority = self::DEFAULT_PRIORITY,
        /**
         * Bundle version.
         *
         * @var string
         */
        public readonly string $version = self::DEFAULT_VERSION,
        /**
         * Cache method.
         *
         * @var int
         */
        public readonly int $cache = self::CACHE_FILE,
    ) {
        $this->srcmap = $this->set_map();
    }

    /**
     * Maps the source to the handle.
     *
     * @return array<string, string>
     */
    private function set_map() {
        $map = array();

        foreach ( $this->assets as $handle => $asset ) {
            $map[ $this->manifest[ $asset->src ] ?? $asset->src ] = $handle;
        }

        return $map;
    }

    public function can_enqueue( ?Context $ctx = null ): bool {
        return $ctx->is_valid( $this->context->value );
    }

    public function enqueue( EnqueueMode $mode = EnqueueMode::Automatic ): bool {
        foreach ( $this->assets as $asset ) {
            $asset->register( $this->base_uri );

            if ( ! $asset->can_enqueue() ) {
                continue;
            }

            $asset->enqueue( $mode );
        }

        return true;
    }

    public function get_asset( string $key ): ?Asset_Interface {
        return $this->assets[ $key ] ?? $this->assets[ $this->srcmap[ $key ] ] ?? null;
    }
}
