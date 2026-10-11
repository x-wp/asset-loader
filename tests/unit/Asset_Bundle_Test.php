<?php
/**
 * XWP_Asset_Bundle unit tests.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

use PHPUnit\Framework\TestCase;
use XWP\Dependency\Resources\Script;
use XWP\Dependency\Resources\Style;

/**
 * Tests for the asset bundle.
 */
final class Asset_Bundle_Test extends TestCase {
    /**
     * Assets of an admin-only bundle, in registration order.
     *
     * @var array<int,string>
     */
    private const ADMIN_ASSETS = array(
        'js/awesome-notifications/awesome-notifications.js',
        'css/vendor/awesome-notifications.css',
        'js/admin/woosync.js',
        'css/admin/woosync.css',
    );

    /**
     * Assets of a front-only bundle, in registration order.
     *
     * @var array<int,string>
     */
    private const FRONT_ASSETS = array(
        'js/front/shop.js',
        'css/front/shop.css',
    );

    /**
     * Make an unloaded bundle (loading needs WordPress hooks).
     *
     * @param  array<'front'|'admin',array<int,string>> $assets Assets keyed by context.
     * @return XWP_Asset_Bundle
     */
    private function make_bundle( array $assets ): XWP_Asset_Bundle {
        return XWP_Asset_Bundle::make(
            array(
                'id'     => 'woosync',
                'assets' => $assets,
            ),
            false,
        );
    }

    /**
     * An admin-only bundle must not expose anything on the front context.
     */
    public function test_front_context_of_admin_only_bundle_returns_no_assets(): void {
        $bundle = $this->make_bundle( array( 'admin' => self::ADMIN_ASSETS ) );

        $this->assertSame( array(), $bundle->get_assets( 'front' ) );
    }

    /**
     * The admin context returns exactly the admin assets, keyed by src, in order.
     */
    public function test_admin_context_of_admin_only_bundle_returns_exactly_its_assets(): void {
        $bundle = $this->make_bundle( array( 'admin' => self::ADMIN_ASSETS ) );

        $assets = $bundle->get_assets( 'admin' );

        $this->assertSame( self::ADMIN_ASSETS, array_keys( $assets ) );

        foreach ( $assets as $src => $asset ) {
            $this->assertSame( $src, $asset->src() );
            $this->assertSame( 'admin', $asset->ctx() );
            $this->assertInstanceOf( str_ends_with( $src, '.css' ) ? Style::class : Script::class, $asset );
        }
    }

    /**
     * A context the bundle never heard of yields no assets.
     */
    public function test_unknown_context_returns_no_assets(): void {
        $bundle = $this->make_bundle( array( 'admin' => self::ADMIN_ASSETS ) );

        $this->assertSame( array(), $bundle->get_assets( 'sidebar' ) );
    }

    /**
     * With both contexts populated, each context returns only its own assets.
     */
    public function test_contexts_of_mixed_bundle_do_not_leak_into_each_other(): void {
        $bundle = $this->make_bundle(
            array(
                'admin' => self::ADMIN_ASSETS,
                'front' => self::FRONT_ASSETS,
            ),
        );

        $this->assertSame( self::FRONT_ASSETS, array_keys( $bundle->get_assets( 'front' ) ) );
        $this->assertSame( self::ADMIN_ASSETS, array_keys( $bundle->get_assets( 'admin' ) ) );
    }
}
