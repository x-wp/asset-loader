<?php
/**
 * Asset class
 *
 * @package eXtended WordPress
 */

namespace XWP\Dependency\Resources;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\EnqueueMode;
use XWP\Dependency\Interfaces\Asset_Interface;

/**
 * Base class for assets
 */
abstract class Asset extends File implements Asset_Interface {
    /**
     * Asset handle
     *
     * @var string
     */
    public readonly string $handle;

    /**
     * Resource configuration
     *
     * @var array
     */
    public readonly array $config;

    /**
     * Context of the asset
     *
     * @var Context
     */
    public readonly Context $context;

    /**
     * Enqueue mode
     *
     * @var EnqueueMode
     */
    public readonly EnqueueMode $mode;


    /**
     * Version of the asset
     *
     * @var string
     */
    public readonly ?string $version;

    /**
     * Dependencies of the asset
     *
     * @var array
     */
    public readonly array $deps;

    /**
     * List of completed actions
     *
     * @var array
     */
    protected array $actions = array();

    /**
     * Constructor
     *
     * @param  string      $src       URL to the asset.
     * @param  string      $namespace Namespace of the asset.
     * @param  string|null $handle    Handle of the asset.
     * @param  Context     $context   Context of the asset.
     * @param  EnqueueMode $mode      Enqueue mode of the asset.
     * @param  string|null $version   Version of the asset.
     * @param  array       $deps      Dependencies of the asset.
     * @param  array       $config    Configuration of the asset.
     */
    public function __construct(
        string $src,
        string $namespace,
        ?string $handle,
        Context $context,
        EnqueueMode $mode,
        ?string $version,
        array $deps,
        array $config,
    ) {
        parent::__construct( $src, $namespace );

        $this->handle  = $handle ?? $this->set_handle( $src, $namespace );
        $this->context = $context;
        $this->mode    = $mode;
        $this->version = $version;
        $this->deps    = $deps;
        $this->config  = \wp_parse_args( $config, $this->default_config() );
    }

    /**
     * Get default configuration
     *
     * @return array Default configuration
     */
    abstract protected function default_config(): array;

    /**
     * Set asset handle
     *
     * @param  string $src       URL to the asset.
     * @param  string $namespace Namespace of the asset.
     * @return string Asset handle
     */
    protected function set_handle( string $src, string $namespace, ): string {
        $parts   = \explode( '-', $namespace );
        $parts[] = \basename( $src );
        return \sanitize_title( \implode( '-', \array_unique( $parts ) ) );
    }

    /**
     * Get resource arguments
     *
     * @param  string $base_uri Base URL of the resource.
     * @return array Resource arguments
     */
    public function get_resource_args( string $base_uri ): array {
        return array(
            'deps'   => $this->deps,
            'handle' => $this->handle,
            'src'    => \trailingslashit( $base_uri ) . $this->src,
            'ver'    => $this->version,
        );
    }

    /**
     * Can a action be performed on the asset
     *
     * @param  string      $what Action to perform.
     * @param  string|null $when Optional. When to perform the action.
     * @return bool
     */
    public function can( string $what, ?string $when = null ): bool {
        if ( ! ( $this->config[ $what ] ?? false ) || ( $this->actions[ $what ] ?? false ) ) {
            return false;
        }

        if ( null === $when ) {
            return true;
        }

        return $when === $this->config[ $what ];
    }

    /**
     * Perform actions on the asset
     *
     * @param string|null $when Optional. When to perform the action.
     */
    public function do_actions( string $when = null ) {
        foreach ( \array_keys( $this->config ) as $what ) {
            if ( ! $this->can( $what, $when ) ) {
                continue;
            }

            $this->$what();
            $this->actions[ $what ] = true;
        }
    }

    public function can_enqueue( Context $ctx = null ): bool {
        return \apply_filters( "can_enqueue_{$this->handle}", true, $this, $ctx );
    }
}
