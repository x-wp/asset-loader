<?php //phpcs:disable WordPress.NamingConventions
namespace XWP\Dependency\Traits;

/**
 * Allows a class to be accessed as an array.
 *
 * @template TKey
 * @template-covariant TValue
 * @template-implements Traversable<TKey, TValue>
 */
trait Array_Access {
    /**
     * Traversible data array.
     *
     * @var array<int|string, mixed>
     */
    protected array $data = array();

    /**
     * Array of keys for the data array.
     *
     * @var array<string|int>
     */
    protected array $data_keys = array();

    /**
     * Current iterator position.
     *
     * @var int
     */
    protected int $arr_position = 0;

    /**
     * Counts the number of items in the data array.
     *
     * Used by the Countable interface.
     *
     * @return int<0, max>
     */
    public function count(): int {
        return \count( $this->data_keys );
    }

    /**
     * Returns the current item in the data array.
     *
     * Used by the Iterator interface.
     *
     * @return TValue Can return any type.
     */
    public function current(): mixed {
        return $this->assets[ $this->data_keys[ $this->arr_position ] ];
    }

    /**
     * Returns the key of the current item in the data array.
     *
     * Used by the Iterator interface.
     *
     * @return TKey|null TKey on success, or null on failure.
     */
    public function key(): mixed {
        return $this->data_keys[ $this->arr_position ];
    }

    /**
     * Moves the iterator to the next item in the data array.
     *
     * Used by the Iterator interface.
     *
     * @return void
     */
    public function next(): void {
        ++$this->arr_position;
    }

    /**
     * Resets the iterator to the first item in the data array.
     *
     * Used by the Iterator interface.
     *
     * @return void
     */
    public function rewind(): void {
        $this->arr_position = 0;
    }

    /**
     * Checks if the current iterator position is valid.
     *
     * Used by the Iterator interface.
     *
     * @return bool
     */
    public function valid(): bool {
        return isset( $this->data_keys[ $this->arr_position ] );
    }

    /**
     * Assigns a value to the specified offset.
     *
     * Used by the ArrayAccess interface.
     *
     * @param TKey   $offset The offset to assign the value to.
     * @param TValue $value The value to set.
     * @return void
     */
    public function offsetSet( $offset, $value ): void {
        if ( \is_null( $offset ) ) {
            $this->data[]      = $value;
            $this->data_keys[] = \array_key_last( $this->data );

            return;
        }

        $this->data[ $offset ] = $value;

        if ( \in_array( $offset, $this->data_keys, true ) ) {
            return;
        }

        $this->data_keys[] = $offset;
    }

    /**
     * Returns the value at the specified offset.
     *
     * Used by the ArrayAccess interface.
     *
     * @param TKey $offset The offset to retrieve.
     * @return TValue Can return any type.
     */
    public function &offsetGet( $offset ): mixed {
        $this->data[ $offset ] ??= array();

        return $this->data[ $offset ];
	}

    /**
     * Checks if the specified offset exists.
     *
     * Used by the ArrayAccess interface.
     *
     * @param TKey $offset The offset to check.
     * @return bool
     */
    public function offsetExists( $offset ): bool {
        return isset( $this->data[ $offset ] );
    }

    /**
     * Unsets the value at the specified offset.
     *
     * Used by the ArrayAccess interface.
     *
     * @param TKey $offset The offset to unset.
     * @return void
     */
    public function offsetUnset( $offset ): void {
        unset( $this->data[ $offset ] );
        unset( $this->data_keys[ \array_search( $offset, $this->data_keys, true ) ] );

        $this->data_keys = \array_values( $this->data_keys );
    }
}
