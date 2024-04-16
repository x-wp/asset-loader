<?php //phpcs:disable SlevomatCodingStandard.Operators.SpreadOperatorSpacing
/**
 * Resource_Factory class file.
 *
 * @package eXtended WordPress
 * @subpackage Dependency
 */

namespace XWP\Dependency\Resources;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\Asset_Type;
use XWP\Dependency\Interfaces\Bundle_Interface;
use XWP\Dependency\Interfaces\Manifest_Interface;

/**
 * Resource factory.
 */
class Factory {
    /**
     * Create a new bundle from a configuration array.
     *
     * @param  array $config Configuration array.
     * @return Bundle_Interface
     */
    public static function create_bundle( array $config ): Bundle_Interface {
        if ( ( $config['legacy'] ?? false ) ) {
            $config = static::remap_legacy_config( $config );
        }

        $config = \wp_parse_args(
            $config,
            array(
                'cache'    => Bundle::CACHE_NONE,
                'context'  => Context::Global,
                'manifest' => array(),
                'priority' => Bundle::DEFAULT_PRIORITY,
            ),
        );

        $config['manifest'] = self::create_manifest( $config['manifest'] );
        $config['assets']   = static::remap_assets( $config['assets'], $config );

        return new Bundle( ...$config );
    }

    /**
     * Remap legacy configuration to the new format.
     *
     * @param  array $config Configuration array.
     * @return array
     */
    protected static function remap_legacy_config( array $config ): array {
        $assets = $config['assets'];
        $remap  = array();

        foreach ( $assets as $context => $files ) {
            $remap[] = \array_map(
                static fn( $src ) => static::set_context( $src, Context::fromSlug( $context ) ),
                \array_merge( ...\array_values( $files ) ),
            );
        }
        $config['base_dir'] ??= $config['dist_path'];
        $config['base_uri'] ??= $config['dist_uri'];
        $config['assets']     = \array_merge( ...$remap );

        if ( ! isset( $config['manifest'] ) || true === $config['manifest'] ) {
            $config['manifest'] = $config['base_dir'];
        }

        unset( $config['dist_path'], $config['dist_uri'], $config['legacy'] );

        return $config;
    }

    /**
     * Remap assets to include the manifest.
     *
     * @param  array $assets Array of assets.
     * @param  array $config Configuration array.
     * @return array
     */
    protected static function remap_assets( array $assets, array $config ): array {
        $remap   = array();
        $default = array(
            'context'   => $config['context'],
            'namespace' => $config['namespace'],
        );

        foreach ( $assets as $asset ) {
            if ( \is_string( $asset ) ) {
                $asset = static::set_context( $asset, $config['context'] );
            }
            $src = $asset['src'];

            $asset['src']  = $config['manifest'][ $src ] ?? $src;
            $asset['type'] = static::get_asset_type( $asset['src'] );

            $asset = static::create_asset(
                \wp_parse_args( $asset, $default ),
            );

            $remap[ $asset->handle ] = $asset;

        }

        return $remap;
    }

    /**
     * Set the context for an asset.
     *
     * @param  string  $src Asset source.
     * @param  Context $ctx Asset context.
     * @return array
     */
    protected static function set_context( string $src, Context $ctx ): array {
        return array(
            'context' => $ctx,
            'src'     => $src,
        );
    }

    /**
     * Create a manifest object.
     *
     * @param  array|string|false|Manifest_Interface $manifest Manifest data.
     * @return Manifest_Interface
     */
    public static function create_manifest( array|string|false|Manifest_Interface $manifest ): Manifest_Interface {
        return match ( true ) {
            \is_array( $manifest ),
            false === $manifest       => new Manifest( $manifest ),
            \is_dir( $manifest )      => static::load_manifest_file(
                \trailingslashit( $manifest ) . 'manifest.json',
            ),
            \file_exists( $manifest ) => static::load_manifest_file( $manifest ),
            default                   => $manifest,
        };
    }

    /**
     * Load a manifest file.
     *
     * @param  string $path Path to the manifest file.
     * @return Manifest_Interface       Manifest object.
     */
    protected static function load_manifest_file( string $path ): Manifest_Interface {
        if ( ! \file_exists( $path ) ) {
            return new Manifest( array() );
        }

        $ext = \pathinfo( $path, PATHINFO_EXTENSION );

        $path_map = match ( $ext ) {
            'php'  => require $path,
            'json' => (array) \wp_json_file_decode( $path ),
            default => array(),
        };

        return new Manifest( $path_map );
    }

    /**
     * Create an asset from an array of arguments.
     *
     * @param  array           $args Asset arguments.
     * @param  Asset_Type|null $type Asset type.
     * @return Asset_Interface
     *
     * @throws \InvalidArgumentException If the asset type is not recognized.
     */
    public static function create_asset( array $args, ?Asset_Type $type = null ) {
        $type ??= $asset['type'] ?? static::get_asset_type( $args['src'] );

        unset( $args['type'] );

        return match ( $type ) {
            Asset_Type::Stylesheet => new Stylesheet( ...$args ),
            Asset_Type::Script     => new Script( ...$args ),
            default                => throw new \InvalidArgumentException(
                \esc_html( 'Could not create asset from type' ),
            ),
        };
    }

    /**
     * Get the asset type based on the file extension.
     *
     * @param string $src Asset source.
     * @return Asset_Type
     *
     * @throws \InvalidArgumentException If the asset type cannot be determined.
     */
    public static function get_asset_type( string $src ): Asset_Type {
        $ext = \strtolower( \pathinfo( $src, PATHINFO_EXTENSION ) );

        return match ( $ext ) {
            'jpg', 'jpeg',
            'png', 'gif',
            'svg', 'ico',
            'avif', 'webp',
            'bmp'            => Asset_Type::Image,
            'woff', 'woff2',
            'ttf', 'otf',
            'eot'            => Asset_Type::Font,
            'js'             => Asset_Type::Script,
            'css'            => Asset_Type::Stylesheet,
            default          => throw new \InvalidArgumentException(
                \esc_html( "Could not determine asset type from extension: $ext" ),
            ),
        };
    }
}
