<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing, Squiz.PHP.DisallowMultipleAssignments.Found

namespace XWP\Dependency\Resources;

use XWP\Dependency\Bundle;
use XWP\Dependency\Interfaces\Can_Enqueue;
use XWP\Dependency\Interfaces\Can_Register;
use XWP\Dependency\Interfaces\Has_Context;

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
     * @param Bundle                   $bundle The bundle object.
     * @param string                   $src    The source of the asset.
     * @param string|null              $dst   The name of the asset.
     * @param 'admin'|'front'          $ctx    The context of the asset.
     * @param 'auto'|'manual'          $mode   The mode of the asset.
     * @param array<int,string>        $deps   The dependencies of the asset.
     * @param array<string,mixed>|null $args   The arguments of the asset.
     * @param string|null              $handle The handle of the asset.
     */
    public function __construct(
        Bundle &$bundle,
        string $src,
        ?string $dst = null,
        protected string $ctx = 'front',
        protected string $mode = 'auto',
        protected array $deps = array(),
        protected ?array $args = null,
        protected ?string $handle = null,
    ) {
        parent::__construct( $bundle, $src, $dst );
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

        return $this->bundle->id() . '-' . $this->name();
    }

    /**
     * Get the arguments of the asset.
     *
     * @return array<string, mixed>
     */
    public function args(): array {
        return \array_merge(
            $this->default_args(),
            $this->args ?? array(),
        );
    }

    public function register(): bool {
        if ( ! $this->can( 'register' ) ) {
            return false;
        }

        return $this->is_registered = $this->callback( 'register', $this->register_args() );
    }

    public function deregister(): bool {
        if ( ! $this->is_registered ) {
            return false;
        }

        $this->callback( 'deregister', array( 'handle' => $this->handle() ) );
        $this->is_registered = false;

        return true;
    }

    /**
     * Get the arguments for registering the asset.
     *
     * @return array<string, mixed>
     */
    protected function register_args(): array {
        return \array_merge(
            array(
                'deps'   => $this->deps(),
                'handle' => $this->handle(),
                'src'    => $this->uri(),
                'ver'    => $this->version(),
            ),
            $this->args(),
        );
    }

    public function enqueue( string $mode = 'auto' ): bool {
        if ( $mode !== $this->mode ) {
            return false;
        }

        if ( ! $this->callback( 'enqueue', array( 'handle' => $this->handle() ) ) ) {
            return false;
        }

        $this->callback( 'add_inline', $this->inline_args() );

        return $this->is_enqueued = true;
	}

    public function dequeue(): bool {
        if ( ! $this->is_enqueued ) {
            return false;
        }

        $this->callback( 'dequeue', array( 'handle' => $this->handle() ) );
        $this->is_enqueued = false;

        return true;
    }

    /**
     * Get the arguments for adding inline data to the asset.
     *
     * @return array<string, mixed>
     */
    protected function inline_args(): array {
        $args = array(
            'data'     => null,
            'handle'   => $this->handle(),
            'position' => 'after',
        );

        $args = \apply_filters( "inline_params_{$this->handle()}", $args, $this );

        return null !== $args['data'] ? $args : array();
    }

    /**
     * Call the callback function for the given action and arguments.
     *
     * @param  'register'|'enqueue'|'add_inline'|'deregister'|'dequeue' $action The action to perform.
     * @param  array<string,mixed>                                      $args   The arguments for the callback function.
     * @return bool                         True if the callback function is called successfully, false otherwise.
     */
    protected function callback( string $action, array $args, ): bool {
        if ( ! $args ) {
            return false;
        }

        /**
         * The callback function for the action.
         *
         * @var callable-string $callback
         */
        $callback = "wp_{$action}_{$this->type()}";

        return $callback( ...$args ) ?? true;
    }

    protected function can( string $action ): bool {
        $action = "can_{$action}_{$this->type()}";
        return \apply_filters( $action, true, $this->handle(), $this );
    }

    /**
     * Get the type of the asset.
     *
     * @return 'script'|'style'
     */
    abstract protected function type(): string;

    /**
     * Get the default enqueue/register arguments for the asset.
     *
     * @return array<string, mixed> The default arguments for enqueue/register.
     */
    abstract protected function default_args(): array;
}
