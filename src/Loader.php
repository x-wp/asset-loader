<?php
namespace XWP\Dependency;

use XWP\Contracts\Hook\Accessible_Hook_Methods;
use XWP\Contracts\Hook\Context;
use XWP\Contracts\Hook\Invoke;
use XWP\Dependency\Interfaces\Asset_Interface;
use XWP\Dependency\Interfaces\Bundle_Interface;
use XWP\Dependency\Resources\Factory;
use XWP\Hook\Decorators\Action;
use XWP\Hook\Invoker;

class Loader {
    use Accessible_Hook_Methods;

    protected static ?Loader $instance = null;

    protected readonly Invoker $invoker;

    protected Context $context;

    /**
     * Registered bundles.
     *
     * Has an 'unknown' key for assets without a bundle, or a namespace.
     *
     * @var array<string,Bundle_Interface>
     */
    protected array $bundles = array(
        'unknown' => null,
    );

    /**
     * Bundles to be enqueued.
     *
     * @var array<string, Bundle_Interface>
     */
    protected array $queue = array();

    public static function instance() {
        return static::$instance ??= new static();
    }

    public function __serialize(): array {
        throw new \Exception( 'Cannot serialize ' . self::class );
    }

    public function __clone() {
        throw new \Exception( 'Cannot clone ' . self::class );
    }

    protected function __construct() {
        $this->invoker = Invoker::instance()->load_handler( $this );
    }


    #[Action(
        tag: 'plugins_loaded',
        priority: \PHP_INT_MAX,
        context: Action::CTX_ADMIN | Action::CTX_FRONTEND,
    )]
    protected function determine_context(): void {
        $this->context = $this->invoker->context;
    }

    #[Action( tag: 'init', priority: 0, context: Action::CTX_ADMIN | Action::CTX_FRONTEND )]
    protected function collect_bundles(): void {
        $bundles = \array_filter( $this->bundles );
        $queue   = array();

        foreach ( $bundles as $bundle ) {
            if ( ! $bundle->can_enqueue( $this->context ) ) {
                continue;
            }

            $queue[ $bundle->namespace ] = $bundle;
		}

        $this->queue = \wp_list_sort( $queue, 'priority', 'ASC', true );
    }

    /**
     * Checks if there are any bundles to enqueue in this execution context.
     *
     * @return bool
     */
    public function can_invoke_enqueue_bundles(): bool {
        return \count( $this->queue ) > 0;
    }

    /**
     * Enqueues all bundles in the queue.
     *
     * @param  Action $hook Hook instance.
     */
    #[Action(
        tag: 'admin_enqueue_scripts',
        priority: -1,
        context: Action::CTX_ADMIN,
        invoke: Invoke::Indirectly,
    )]
    #[Action(
        tag: 'wp_enqueue_scripts',
        priority: -1,
        context: Action::CTX_FRONTEND,
        invoke: Invoke::Indirectly,
    )]
    protected function enqueue_bundles( Action $hook ): void {
        foreach ( $this->queue as $bundle ) {
            \add_action( $hook->tag, $bundle->enqueue( ... ), $bundle->priority, 0 );
        }
    }

    public function load_bundle( Bundle_Interface $bundle ): static {
        $this->bundles[ $bundle->namespace ] = $bundle;
        return $this;
    }

    public function load_config( array $config ): static {
        $this->load_bundle( Factory::create_bundle( $config ) );
        return $this;
    }

    public function load_asset( Asset_Interface $asset ): static {
        return $this;
    }

    /**
     * Register a namespace with a configuration.
     *
     * @param  string $namespace Namespace to register.
     * @param  array  $config    Configuration for the namespace.
     * @return static
     *
     * @deprecated 1.0.0 Use load_bundle() or load_config() instead.
     */
    public function register_namespace( string $namespace, array $config ): static {
        $config['namespace'] = $namespace;
        $config['legacy']    = true;

        return $this->load_config( $config );
    }

    public function get_bundle( string $namespace ): ?Bundle_Interface {
        return $this->bundles[ $namespace ] ?? null;
    }

    public function enqueue( string $bundle, string $handle ): static {
        $this->get_bundle( $bundle )->get_asset( $handle )->enqueue();

        return $this;
    }
}
