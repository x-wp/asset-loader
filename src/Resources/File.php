<?php //phpcs:disable Universal.Operators.DisallowShortTernary.Found
/**
 * File class file.
 *
 * @package eXtended WordPress
 * @subpackage Asset Loader
 */

namespace XWP\Dependency\Resources;

use XWP_Asset_Bundle;

/**
 * Base file resource.
 */
class File {
    /**
     * File extension
     *
     * @var string
     */
    protected string $ext;

    /**
     * File dest
     *
     * @var string
     */
    protected string $dst;

    /**
     * File name
     *
     * @var string
     */
    protected string $id;

    /**
     * Constructor
     *
     * @param XWP_Asset_Bundle $bundle Bundle instance.
     * @param string           $src    File source.
     * @param ?string          $dst   File name.
     * @param ?string          $id    File ID.
     */
    public function __construct(
        protected XWP_Asset_Bundle &$bundle,
        protected string $src,
        ?string $dst = null,
        ?string $id = null,
    ) {
        $this->dst = $dst ?? $src;
        $this->ext = \pathinfo( $this->src, \PATHINFO_EXTENSION );
        $this->id  = $id ?? \pathinfo( $this->src, \PATHINFO_FILENAME );
    }

    /**
     * Get the WP_Filesystem instance.
     *
     * @return \WP_Filesystem_Base|null
     */
    protected function wpfs(): ?\WP_Filesystem_Base {
        return \wp_load_filesystem() ?: null;
    }

    /**
     * Get the file extension
     *
     * @return string
     */
    public function ext(): string {
        return $this->ext;
    }

    /**
     * Get the file source
     *
     * @return string
     */
    public function src(): string {
        return $this->src;
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function dst(): string {
        return $this->dst;
    }

    /**
     * Get the file name
     *
     * @return string
     */
    public function id(): string {
        return $this->id;
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function path() {
        return $this->bundle->base_dir() . '/' . $this->dst();
    }

    /**
     * Get the file URI
     *
     * @return string
     */
    public function uri(): string {
        return $this->bundle->base_uri() . '/' . $this->dst();
    }

    /**
     * Get the file contents
     *
     * @return string
     */
    public function data(): string {
        return $this->wpfs()?->get_contents( $this->path() ) ?: '';
    }

    /**
     * Get the file size
     *
     * @return int
     */
    public function size(): int {
        return $this->wpfs()?->size( $this->path() ) ?: 0;
    }
}
