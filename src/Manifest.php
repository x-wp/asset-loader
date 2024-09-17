<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found


namespace XWP\Dependency;

use Automattic\Jetpack\Constants;

/**
 * Helper class to load a manifest file.
 */
class Manifest {
    /**
     * Load a manifest file
     *
     * @param  string|bool  $file Name of the manifest file.
     * @param  string       $dir  Directory where the manifest file is located.
     * @param  string       $id   The ID of the manifest.
     * @param  string|false $ver  The version of the manifest.
     * @return array<string,string>
     */
    public static function load( string|bool $file, string $dir, string $id, string|bool $ver ): array {
        if ( false === $file ) {
            return array();
        }

        $file = true === $file ? 'assets' : $file;
        $data = static::get_cache( $id, $ver );

        if ( $data ) {
            return $data;
        }

        return static::set_cache( $id, $ver, static::read_file( $dir, $file ) );
    }

    /**
     * Get the cached manifest
     *
     * @param  string       $id  The ID of the manifest.
     * @param  string|false $ver The version of the manifest.
     * @return array<string,string>|null
     */
    public static function get_cache( string $id, string|bool $ver ): ?array {
        if ( ! static::cache_enabled( $ver ) ) {
            return null;
        }

        return \get_transient( static::get_cache_key( $id, $ver ) ) ?: null;
    }

    /**
     * Set the cached manifest
     *
     * @param  string               $id   The ID of the manifest.
     * @param  string|false         $ver  The version of the manifest.
     * @param  array<string,string> $data The data to cache.
     * @return array<string,string>
     */
    public static function set_cache( string $id, string|bool $ver, array $data ): array {
        if ( static::cache_enabled( $ver ) ) {
            \set_transient( static::get_cache_key( $id, $ver ), $data, \HOUR_IN_SECONDS );
        }

        return $data;
    }

    /**
     * Get the cache key for a manifest
     *
     * @param  string       $id  The ID of the manifest.
     * @param  string|false $ver The version of the manifest.
     * @return string
     */
    public static function get_cache_key( string $id, string|bool $ver ): string {
        $base = "xwp_{$id}_assets";

        return \is_string( $ver ) ? "{$base}_{$ver}" : $base;
    }

    /**
     * Check if cache is enabled
     *
     * @param  string|false $ver Version of the manifest.
     * @return bool
     */
    public static function cache_enabled( string|bool $ver ): bool {
        return ! \str_starts_with( (string) $ver, '0.0.0' ) && ! Constants::is_true( 'XWP_LOADER_DEBUG' );
    }

    /**
     * Try to read a manifest file
     *
     * @param  string $dir  Directory where the manifest file is located.
     * @param  string $file Name of the manifest file.
     * @return array<string,string>
     *
     * @throws \Throwable If the manifest file cannot be read.
     */
    public static function read_file( string $dir, string $file ): array {
        $file = \trailingslashit( $dir ) . \pathinfo( $file, PATHINFO_FILENAME );
        $args = array( 'associative' => true );
        try {
            return match ( true ) {
                \file_exists( "{$file}.php" )  => require "{$file}.php",
                \file_exists( "{$file}.json" ) => \wp_json_file_decode( "{$file}.json", $args ),
                default                        => array(),
            } ?? array();
        } catch ( \Throwable ) {
            return array();
        }
    }
}
