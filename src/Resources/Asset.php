<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing, Squiz.PHP.DisallowMultipleAssignments.Found

namespace XWP\Dependency\Resources;

use XWP\Dependency\Interfaces\Can_Enqueue;
use XWP\Dependency\Interfaces\Can_Register;
use XWP\Dependency\Interfaces\Has_Context;
use XWP_Asset_Bundle;

/**
 * Base class for assets.
 */
abstract class Asset extends File implements Has_Context, Can_Enqueue, Can_Register {
    /**
     * Whether the asset is registered.
     *
     * @var bool
     */
    protected bool $is_registered = false;

    /**
     * Whether the asset is enqueued.
     *
     * @var bool
     */
    protected bool $is_enqueued = false;

    /**
     * Asset constructor.
     *
     * @param XWP_Asset_Bundle         $bundle The bundle object.
     * @param string                   $src    The source of the asset.
     * @param string|null              $dst   The name of the asset.
     * @param string|null              $id    The ID of the asset.
     * @param 'admin'|'front'          $ctx    The context of the asset.
     * @param 'auto'|'manual'          $mode   The mode of the asset.
     * @param array<int,string>        $deps   The dependencies of the asset.
     * @param array<string,mixed>|null $args   The arguments of the asset.
     * @param string|null              $handle The handle of the asset.
     */
    public function __construct(
        XWP_Asset_Bundle &$bundle,
        string $src,
        ?string $dst = null,
        ?string $id = null,
        protected string $ctx = 'front',
        protected string $mode = 'auto',
        protected array $deps = array(),
        protected ?array $args = null,
        protected ?string $handle = null,
    ) {
        parent::__construct( $bundle, $src, $dst, $id );
    }

    public function ctx(): string {
        return $this->ctx;
    }

    public function mode(): string {
        return $this->mode;
    }

    public function deps(): array {
        return $this->deps;
    }

    public function version(): string|false|null {
        return $this->bundle->version();
    }

    public function handle(): string {
        if ( isset( $this->handle ) ) {
            return $this->handle;
        }

        return $this->bundle->id() . '-' . $this->id();
    }

    public function process( string $mode = 'auto' ): bool {
        $this->register();
        $this->localize();
        $this->enqueue( $mode );
        $this->add_inline();

        return $this->is_registered && $this->is_enqueued;
    }

    public function register(): bool {
        return $this->is_registered = $this->run_cb( 'register' );
    }

    public function localize(): bool {
        return $this->run_cb( 'localize' );
    }

    public function enqueue( string $mode = 'auto' ): bool {
        return $this->is_enqueued = $this->is_registered && $mode === $this->mode && $this->run_cb( 'enqueue' );
    }

    public function add_inline(): bool {
        return $this->run_cb( 'add_inline' );
    }

    protected function run_cb( string $action ): bool {
        /**
         * The callback function for the action.
         *
         * @var callable-string $cbfn
         */
        $cbfn = "wp_{$action}_{$this->type()}";
        $args = $this->{"{$action}_args"}();

        if ( ! \function_exists( $cbfn ) || ! $this->check_cb( $action ) || false === $args ) {
            return false;
        }

        return (bool) ( $cbfn( $this->handle(), ...$args, ) ?? 1 );
    }

    protected function check_cb( string $action ): bool {
        if ( 'register' !== $action && 'enqueue' !== $action ) {
            return true;
        }

        $action = "{$this->bundle->id()}_can_{$action}_{$this->type()}";

        return \apply_filters( $action, true, $this->id(), $this );
    }

    /**
     * Get the arguments for registering the asset.
     *
     * @return array<string, mixed>
     */
    protected function register_args(): array {
        return \array_merge(
            array(
                'deps' => $this->deps(),
                'src'  => $this->uri(),
                'ver'  => $this->version(),
            ),
            $this->args ?? array(),
        );
    }

    /**
     * Get the arguments for localizing the asset.
     *
     * @return array<string, mixed>|false
     */
    protected function localize_args(): array|bool {
        $args = \apply_filters( "localize_{$this->type()}_args_{$this->handle()}", array(), $this );

        if ( ! isset( $args['l10n'], $args['object_name'] ) ) {
            return false;
        }

        return $args;
    }

    /**
     * Get the arguments for enqueuing the asset.
     *
     * @return array<string, mixed>
     */
    protected function enqueue_args(): array {
        return array();
    }

    /**
     * Get the arguments for adding inline data to the asset.
     *
     * @return array<string, mixed>|false
     */
    protected function add_inline_args(): array|bool {
        $args = \apply_filters( "inline_{$this->type()}_args_{$this->handle()}", array(), $this );

        if ( ! isset( $args['data'] ) ) {
            return false;
        }

        return $args;
    }

    public function deregister(): bool {
        $cb = "wp_deregister_{$this->type()}";

        $cb( $this->handle() );
        return true;
    }

    public function dequeue(): bool {
        $cb = "wp_dequeue_{$this->type()}";

        $cb( $this->handle() );
        return true;
    }

    /**
     * Get the type of the asset.
     *
     * @return 'script'|'style'
     */
    abstract protected function type(): string;
}
