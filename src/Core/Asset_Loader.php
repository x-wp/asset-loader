<?php
/**
 * XWP_Asset_Loader class file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

use XWP\Helper\Traits\Singleton;

/**
 * Asset loader.
 *
 * @method static self    add_bundle( XWP_Asset_Bundle $bundle )        Add a bundle.
 * @method static self    load_bundle( XWP_Asset_Bundle|array $bundle ) Load a bundle.
 * @method static XWP_Asset_Bundle  make_bundle( array $args )          Create a bundle.
 * @method static ?XWP_Asset_Bundle get_bundle(string $id)              Get a bundle by ID.
 */
final class XWP_Asset_Loader {
    use Singleton;

    /**
     * Asset bundles. Keyed by bundle id.
     *
     * @var array<string, XWP_Asset_Bundle>
     */
    private array $bundles = array();

    /**
     * Call a static method on the instance.
     *
     * @param  string       $name The name of the method to call.
     * @param  array<mixed> $args The arguments to pass to the method.
     * @return mixed
     *
     * @throws \BadMethodCallException If the method does not exist.
     */
    public static function __callStatic( string $name, array $args = array() ): mixed {
        if ( ! method_exists( self::class, "call_$name" ) ) {
            throw new \BadMethodCallException( esc_html( "Method $name does not exist" ) );
        }

        return self::instance()->{"call_$name"}( ...$args );
    }

    /**
     * Check if the asset loader is initialized.
     *
     * @return bool
     */
    public static function initialized(): bool {
        return null !== self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'init', array( $this, 'init' ), 1000, 0 );
    }

    /**
     * Initialize the asset loader.
     */
    public function init(): void {
        [ $hook, $context ] = $this->get_env();

        add_action( $hook, fn() => $this->run( $hook, $context ), -1, 0 );
    }

    /**
     * Get the environment.
     *
     * @return array{0: string, 1: 'admin'|'front'}
     */
    private function get_env(): array {
        $hook    = is_admin() ? 'admin_enqueue_scripts' : 'wp_enqueue_scripts';
        $context = is_admin() ? 'admin' : 'front';

        return array( $hook, $context );
    }

    /**
     * Run the asset loader.
     *
     * @param string          $hook The hook to run on.
     * @param 'admin'|'front' $context The context to run in.
     */
    private function run( string $hook, string $context ): void {
        foreach ( $this->bundles as $bundle ) {
            \add_action(
                $hook,
                static function () use ( $bundle, $context ) {
                    foreach ( $bundle->get_assets( $context ) as $asset ) {
                        $asset->process( 'auto' );
                    }
                },
                $bundle->priority(),
                0,
            );
        }
    }

    /**
     * Load a bundle.
     *
     * @param  XWP_Asset_Bundle $bundle The bundle to load.
     * @return self
     */
    private function call_add_bundle( XWP_Asset_Bundle $bundle ): self {
        $this->bundles[ $bundle->id() ] = $bundle;

        return $this;
    }

    /**
     * Load a bundle, or create a bundle from arguments and load it.
     *
     * @param  array<string,mixed>|XWP_Asset_Bundle $bundle Bundle instance or bundle arguments.
     * @return self
     */
    private function call_load_bundle( array|XWP_Asset_Bundle $bundle ): self {
        if ( ! ( $bundle instanceof XWP_Asset_Bundle ) ) {
            $bundle = self::make_bundle( $bundle );
        }

        return self::add_bundle( $bundle );
    }

    /**
     * Make a bundle from arguments.
     *
     * @param  array<string,mixed> $args Bundle arguments.
     * @return XWP_Asset_Bundle
     */
    private function call_make_bundle( array $args ): XWP_Asset_Bundle {
        return new XWP_Asset_Bundle( ...$args );
    }

    /**
     * Get a bundle by ID.
     *
     * @param  string $id Bundle ID.
     * @return XWP_Asset_Bundle|null
     */
    private function call_get_bundle( string $id ): ?XWP_Asset_Bundle {
        return $this->bundles[ $id ] ?? null;
    }
}
