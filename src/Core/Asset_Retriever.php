<?php
/**
 * Retriever trait file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

/**
 * Retriever trait.
 */
trait XWP_Asset_Retriever {
    /**
     * Bundle ID.
     *
     * @var string
     */
    private string $bundle_id;

    /**
     * Load the bundle configuration.
     *
     * @param  string $path Path to the bundle configuration file.
     * @return void
     */
    protected function load_bundle_config( string $path ): void {
        $config          = include $path;
        $this->bundle_id = $config['id'];

        XWP_Asset_Loader::load_bundle( $config );
    }

    /**
     * Add a bundle to the asset loader.
     *
     * @param  XWP_Asset_Bundle $bundle Bundle instance.
     * @return void
     */
    protected function add_bundle( XWP_Asset_Bundle $bundle ): void {
        XWP_Asset_Loader::add_bundle( $bundle );
    }

    /**
     * Get the bundle instance.
     *
     * @return XWP_Asset_Bundle Bundle instance.
     */
    private function bundle(): XWP_Asset_Bundle {
        return XWP_Asset_Loader::get_bundle( $this->bundle_id );
    }

    /**
     * Get the cache-busted file path.
     *
     * @param  string $asset Asset path.
     * @return string
     */
    public function asset_path( string $asset ): string {
        return $this->bundle()[ $asset ]->path();
    }

    /**
     * Get the cache-busted file URI.
     *
     * @param  string $asset Asset URI.
     * @return string
     */
    public function asset_uri( string $asset ): string {
        return $this->bundle()[ $asset ]->uri();
    }

    /**
     * Get the base64 encoded file URI.
     *
     * @param  string $asset Asset path.
     * @return string
     */
    public function asset_base64_uri( string $asset ): string {
        return $this->bundle()[ $asset ]->base64_uri();
    }

    /**
     * Get the cache-busted file data.
     *
     * @param  string $asset Asset path.
     * @return string
     */
    public function asset_data( string $asset ): string {
        return $this->bundle()[ $asset ]->data();
    }

    /**
     * Get the base64 encoded file data.
     *
     * @param  string $asset Asset path.
     * @return string
     */
    public function asset_base64_data( string $asset ): string {
        return $this->bundle()[ $asset ]->base64_data();
    }
}
