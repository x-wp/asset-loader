<?php //phpcs:disable Squiz.Commenting.FunctionComment.Missing
namespace XWP\Dependency\Resources;

use XWP\Dependency\Interfaces\Manifest_Interface;
use XWP\Dependency\Traits\Array_Access;

class Manifest implements Manifest_Interface {
    use Array_Access;

    /**
     * Assets in the manifest.
     *
     * @var array<string, string>
     */
    protected array $assets = array();

    /**
     * Array keys.
     *
     * Used for iteration.
     *
     * @var array<string>
     */
    protected array $keys = array();

    /**
     * Current position.
     *
     * @var int
     */
    protected int $position = 0;

    /**
     * Constructor.
     *
     * @param  array $data Data to store.
     */
    public function __construct( array $data ) {
        $this->assets = $data;
        $this->keys   = \array_keys( $data );
    }
}
