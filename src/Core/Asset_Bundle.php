<?php //phpcs:disable Squiz.Commenting.FunctionComment.IncorrectTypeHint
/**
 * Bundle class file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

use XWP\Dependency\Manifest;
use XWP\Dependency\Resources\File;
use XWP\Dependency\Resources\Font;
use XWP\Dependency\Resources\Image;
use XWP\Dependency\Resources\Script;
use XWP\Dependency\Resources\Style;
use XWP\Helper\Traits\Array_Access;

/**
 * A bundle of assets.
 *
 * @implements \ArrayAccess<string,Style|Script|Image|Font>
 * @implements \Iterator<string,Style|Script|Image|Font>
 */
class XWP_Asset_Bundle implements \ArrayAccess, \Iterator, \Countable, \JsonSerializable {
    /**
     * Use the array access trait.
     *
     * @use Array_Access<string,Style|Script|Image|Font>
     */
    use Array_Access;

    /**
     * Assets in the bundle.
     *
     * @var array<'front'|'admin',array<int,key-of<self>>>
     */
    protected array $assets = array(
        'admin' => array(),
        'front' => array(),
    );

    /**
     * File name to file path map.
     *
     * @var array<string,string>
     */
    protected array $manifest = array();

    /**
     * Make a bundle from arguments.
     *
     * @param  array<string,mixed> $args Bundle arguments.
     * @param  bool                $load Optional. Load the bundle. Default true.
     * @return self
     */
    public static function make( array $args, bool $load = true ): self {
        $bundle = new self( ...$args );

        return $load ? $bundle->load() : $bundle;
    }

    /**
     * Constructor
     *
     * @param string                                                       $id       Bundle ID.
     * @param string                                                       $base_dir Bundle base directory.
     * @param string                                                       $base_uri Bundle base URI.
     * @param int                                                          $priority Bundle priority.
     * @param string|false|null                                            $version  Bundle version.
     * @param string|bool                                                  $manifest Asset manifest.
     * @param array<'front'|'admin',array<int,string|array<string,mixed>>> $assets   Bundle assets.
     * @param array<int,string>                                            $files    Bundle files.
     */
    public function __construct(
        /**
         * The bundle ID.
         *
         * @var string
         */
        protected string $id,
        /**
         * Bundle base directory.
         *
         * @var string
         */
        protected string $base_dir = '',
        /**
         * Bundle base URI.
         *
         * @var string
         */
        protected string $base_uri = '',
        /**
         * Bundle priority.
         *
         * @var int
         */
        protected int $priority = 10,
        /**
         * Bundle version.
         *
         * @var string|false|null
         */
        protected string|bool|null $version = null,
        string|bool $manifest = false,
        array $assets = array(),
        array $files = array(),
    ) {
        $this
            ->with_manifest( $manifest )
            ->with_assets( $assets )
            ->with_files( $files );
    }

    /**
     * Returns the value at the specified offset.
     *
     * Used by the ArrayAccess interface.
     *
     * @param  string $offset The offset to retrieve.
     * @return Style|Script|Image|Font|Image|Font
     */
    public function &offsetGet( $offset ): mixed {
        if ( ! $this->offsetExists( $offset ) && isset( $this->manifest[ $offset ] ) ) {
            $this[ $offset ] = $this->make_file( $offset );
        }

        return $this->arr_data[ $offset ];
	}

    /**
     * Set the bundle ID.
     *
     * @param  string $id The bundle ID.
     * @return self
     */
    public function with_id( string $id ): self {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the bundle base directory.
     *
     * @param  string $base_dir The bundle base directory.
     * @return self
     */
    public function with_base_dir( string $base_dir ): self {
        $this->base_dir = $base_dir;

        return $this;
    }

    /**
     * Set the bundle base URI.
     *
     * @param  string $base_uri The bundle base URI.
     * @return self
     */
    public function with_base_uri( string $base_uri ): self {
        $this->base_uri = $base_uri;

        return $this;
    }

    /**
     * Set the bundle priority.
     *
     * @param  int $priority The bundle priority.
     * @return self
     */
    public function with_priority( int $priority ): self {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Set the bundle version.
     *
     * @param  string|false|null $version The bundle version.
     * @return self
     */
    public function with_version( string|bool|null $version ): self {
        $this->version = $version;

        return $this;
    }

    /**
     * Set the bundle manifest.
     *
     * @param  string|bool $file The manifest file.
     * @return self
     */
    public function with_manifest( string|bool $file ): self {
        if ( $this->base_dir() ) {
            $this->manifest = Manifest::load( $file, $this->base_dir(), $this->id(), $this->version() );
        }

        return $this;
    }

    /**
     * Load assets.
     *
     * @param  array<'front'|'admin',array<int,string|array<string,mixed>>> $assets The assets to load.
     * @return self
     */
    public function with_assets( array $assets ): self {
        foreach ( $assets as $ctx => $assets ) {
            $this->with_asset_group( $ctx, $assets );
        }

        return $this;
    }

    /**
     * Load an asset group.
     *
     * @param  'admin'|'front'                       $ctx    The context of the asset group.
     * @param  array<int,string|array<string,mixed>> $assets The assets to load.
     * @return self
     */
    public function with_asset_group( string $ctx, array $assets ): self {
        foreach ( $assets as $asset ) {
            $asset = \is_string( $asset ) ? array( 'src' => $asset ) : $asset;

            $asset['ctx'] = $ctx;

            $this->with_asset( $this->make_asset( $asset ) );
        }

        return $this;
    }

    /**
     * Add an asset to the bundle.
     *
     * @param  Script|Style $asset The asset to add.
     * @return self
     */
    public function with_asset( Script|Style $asset ): self {
        $this[ $asset->src() ]           = $asset;
        $this->assets[ $asset->ctx() ][] = $asset->src();

        return $this;
    }

    /**
     * Add files to the bundle.
     *
     * @param  array<int,string> $files Files to add.
     * @return self
     */
    public function with_files( array $files ): self {
        foreach ( $files as $file ) {
            $this->with_file( $this->make_file( $file ) );
        }

        return $this;
    }

    /**
     * Add a file to the bundle.
     *
     * @param  Image|Font $file The file to add.
     * @return self
     */
    public function with_file( Image|Font $file ): self {
        $this[ $file->src() ] = $file;

        return $this;
    }

    /**
     * Get the bundle ID.
     *
     * @return string
     */
    public function id(): string {
        return $this->id;
    }

    /**
     * Get the bundle base directory.
     *
     * @return string
     */
    public function base_dir(): string {
        return $this->base_dir;
    }

    /**
     * Get the bundle base URI.
     *
     * @return string
     */
    public function base_uri(): string {
        return $this->base_uri;
    }

    /**
     * Get the bundle priority.
     *
     * @return int
     */
    public function priority(): int {
        return $this->priority;
    }

    /**
     * Get the bundle version.
     *
     * @return string|false|null
     */
    public function version(): string|false|null {
        return $this->version ?? $GLOBALS['wp_version'];
    }

    /**
     * Make a file.
     *
     * @param  string $src The source of the file.
     * @return Image|Font
     */
    public function make_file( string $src ): Image|Font {
        $ext = \pathinfo( $src, \PATHINFO_EXTENSION );

        $cname = match ( $ext ) {
            'jpg', 'png', 'gif',
            'ico', 'svg', 'jpeg',
            'webp', 'avif', 'apng' => Image::class,
            'ttf', 'woff', 'woff2' => Font::class,
            default                => Image::class,
        };

        return new $cname( $this, $src, $this->manifest[ $src ] );
    }

    /**
     * Make an asset.
     *
     * @param  array<string,mixed> $asset Dependency name or arguments.
     * @return Script|Style
     */
    public function make_asset( array $asset ): Script|Style {
        $asset['dst']  ??= $this->manifest[ $asset['src'] ] ?? $asset['src'];
        $asset['deps'] ??= array();

        $cname = match ( \pathinfo( $asset['src'], \PATHINFO_EXTENSION ) ) {
            'js'    => Script::class,
            'css'   => Style::class,
            default => Script::class,
        };

        return new $cname( $this, ...$asset );
    }

    /**
     * Get the assets for a context.
     *
     * @param  'admin'|'front' $ctx The context to get assets for.
     * @return array<string,Script|Style>
     */
    public function get_assets( string $ctx ): array {
        // @phpstan-ignore return.type
        return \xwp_array_slice_assoc( $this->arr_data, ...$this->assets[ $ctx ] );
    }

    /**
     * Load the bundle.
     *
     * @return self
     */
    public function load(): self {
        \XWP_Asset_Loader::add_bundle( $this );

        return $this;
    }

    /**
     * Find assets by extension.
     *
     * @param  string $ext The extension to find.
     * @return array<string,Style|Script|Image|Font>
     */
    public function find( string $ext ): array {
        $found = array();

        foreach ( array_keys( $this->manifest ) as $src ) {
            if ( pathinfo( $src, PATHINFO_EXTENSION ) !== $ext ) {
                continue;
            }

            $found[ $src ] = $this[ $src ];
        }

        return $found;
    }

    /**
     * Get the assets for a given type.
     *
     * @template T of Style|Script|Image|Font
     * @param  class-string<T> $type The type of asset to get.
     * @return array<string,T>
     */
    public function collect( string $type ): array {
        $found = array();

        foreach ( array_keys( $this->manifest ) as $src ) {
            if ( ! ( $this[ $src ] instanceof $type ) ) {
                continue;
            }

            $found[ $src ] = $this[ $src ];
        }

        return $found;
    }

    /**
     * Get an asset by field.
     *
     * @param  string $id The ID of the asset to get.
     * @param  string $field The field to search by.
     * @return Style|Script|Image|Font|null
     */
    public function get( string $id, string $field = 'handle' ): Style|Script|Image|Font|null {
        foreach ( array_keys( $this->manifest ) as $src ) {
            if ( $id === $this[ $src ]->{"{$field}"}() ) {
                return $this[ $src ];
            }
        }

        return null;
    }
}
