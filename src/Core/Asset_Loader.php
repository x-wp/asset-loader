<?php
/**
 * XWP_Asset_Loader class file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

use XWP\Dependency\Bundle;
use XWP\Helper\Traits\Singleton;

/**
 * Asset loader.
 *
 * @method static self    add_bundle( Bundle $bundle )        Add a bundle.
 * @method static self    load_bundle( Bundle|array $bundle ) Load a bundle.
 * @method static Bundle  make_bundle( array $args )          Create a bundle.
 * @method static ?Bundle get_bundle(string $id)              Get a bundle by ID.
 */
final class XWP_Asset_Loader {
    use Singleton;

    /**
     * Asset bundles. Keyed by bundle id.
     *
     * @var array<string, Bundle>
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
     * Load a bundle.
     *
     * @param  Bundle $bundle The bundle to load.
     * @return self
     */
    private function call_add_bundle( Bundle $bundle ): self {
        $this->bundles[ $bundle->id() ] = $bundle;

        return $this;
    }

    /**
     * Load a bundle, or create a bundle from arguments and load it.
     *
     * @param  array<string,mixed>|Bundle $bundle Bundle instance or bundle arguments.
     * @return self
     */
    private function call_load_bundle( array|Bundle $bundle ): self {
        if ( ! ( $bundle instanceof Bundle ) ) {
            $bundle = self::make_bundle( $bundle );
        }

        return self::add_bundle( $bundle );
    }

    /**
     * Make a bundle from arguments.
     *
     * @param  array<string,mixed> $args Bundle arguments.
     * @return Bundle
     */
    private function call_make_bundle( array $args ): Bundle {
        return new Bundle( ...$args );
    }

    /**
     * Get a bundle by ID.
     *
     * @param  string $id Bundle ID.
     * @return Bundle|null
     */
    private function call_get_bundle( string $id ): ?Bundle {
        return $this->bundles[ $id ] ?? null;
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
                        $asset->register();
                        $asset->enqueue();
                    }
                },
                $bundle->priority(),
                0,
            );
        }
    }
}
