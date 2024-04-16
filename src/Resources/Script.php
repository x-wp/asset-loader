<?php //phpcs:disable SlevomatCodingStandard.Functions.RequireSingleLineCall, Squiz.Commenting.FunctionComment
namespace XWP\Dependency\Resources;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\Asset_Type;
use XWP\Dependency\Enums\EnqueueMode;
use XWP\Dependency\Resources\Bundle;

class Script extends Asset {
    public const ASSET_TYPE = Asset_Type::Script;

    /**
     * Additional enqueue arguments
     *
     * @var array
     */
    public readonly array $args;

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
        array $args = array(),
    ) {
        parent::__construct( $src, $namespace, $handle, $context, $mode, $version, $deps, $config );

        $this->args = $args;
    }

    protected function default_config(): array {
        return array(
            'configure' => false,
            'localize'  => false,
        );
    }

    public function get_resource_args( string $base_uri ): array {
        return \array_merge(
            parent::get_resource_args( $base_uri ),
            array(
				'args' => array(
					'in_footer' => $this->args['in_footer'] ?? true,
					'strategy'  => $this->args['strategy'] ?? 'defer',
				),
			),
        );
    }

    /**
     * Localizes the script
     */
    public function localize(): void {
        $name = \str_replace( ' ', '', \ucwords( \str_replace( array( '-', '_' ), ' ', $this->handle ) ) );
        $args = array(
            'handle'      => $this->handle,
            'l10n'        => array(),
            'object_name' => $name,
        );

        $args = \apply_filters( "localize_params_{$this->handle}", $args, $this );

        \wp_localize_script( ...$args );
    }

    /**
     * Configures the script
     */
    public function configure(): void {
        $args = array(
            'data'     => '',
            'handle'   => $this->handle,
            'position' => 'after',
        );

        $args = \apply_filters( "configure_params_{$this->handle}", $args, $this );

        \wp_add_inline_script( ...$args );
    }

    public function register( string $base_uri = '' ): void {
        \wp_register_script( ...$this->get_resource_args( $base_uri ) );

        $this->do_actions( 'register' );
    }

    public function enqueue( EnqueueMode $mode = EnqueueMode::Manual ): bool {
        if ( $mode !== $this->mode ) {
            return false;
        }

        $this->do_actions( 'enqueue' );

        \wp_enqueue_script( $this->handle );

        return true;
    }
}
