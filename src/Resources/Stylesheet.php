<?php //phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall, Squiz.Commenting.FunctionComment
namespace XWP\Dependency\Resources;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\Asset_Type;
use XWP\Dependency\Enums\EnqueueMode;

class Stylesheet extends Asset {
    public const ASSET_TYPE = Asset_Type::Stylesheet;

    /**
     * Constructor
     *
     * @param  array $args      Additional enqueue arguments.
     */
    public function __construct(
        string $src,
        string $namespace,
        ?string $handle = null,
        Context $context = Context::Global,
        EnqueueMode $mode = EnqueueMode::Automatic,
        ?string $version = Bundle::DEFAULT_VERSION,
        array $deps = array(),
        array $config = array(),
        string $media = 'all',
    ) {
        parent::__construct( $src, $namespace, $handle, $context, $mode, $version, $deps, $config );

        $this->media = $media;
    }

    public readonly string $media;

    protected function default_config(): array {
        return array();
    }

    public function get_resource_args( string $base_uri ): array {
        return \array_merge(
            parent::get_resource_args( $base_uri ),
            array(
                'media' => $this->media,
            ),
        );
    }

    public function register( string $base_uri = '' ): void {
        \wp_register_style( ...$this->get_resource_args( $base_uri ) );

        $this->do_actions( 'register' );
    }

    public function enqueue( EnqueueMode $mode = EnqueueMode::Manual ): bool {
        if ( $mode !== $this->mode ) {
            return false;
        }

        \wp_enqueue_style( $this->handle );

        $this->do_actions( 'enqueue' );

        return true;
    }
}
