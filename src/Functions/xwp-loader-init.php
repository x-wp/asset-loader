<?php
/**
 * Asset Loader initialization.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

if ( ! function_exists( 'xwp_asset_loader_init' ) && function_exists( 'add_action' ) ) :
    /**
     * Initialize the asset loader.
     */
    function xwp_asset_loader_init(): void {
        XWP_Asset_Loader::instance();
    }

    add_action( 'init', 'xwp_asset_loader_init', 10 );

    if ( did_action( 'init' ) && ! doing_action( 'init' ) && ! XWP_Asset_Loader::initialized() ) {
        xwp_asset_loader_init();
    }

endif;
