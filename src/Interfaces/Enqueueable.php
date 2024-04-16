<?php
namespace XWP\Dependency\Interfaces;

use XWP\Contracts\Hook\Context;
use XWP\Dependency\Enums\EnqueueMode;

interface Enqueueable {
    /**
     * Determines if an entity can be enqueued in the current context.
     *
     * @param  Context $ctx Execution context.
     * @return bool
     */
    public function can_enqueue( ?Context $ctx = null ): bool;

    /**
     * Enqueues the entity.
     *
     * @param  EnqueueMode $mode Enqueue mode.
     * @return void
     */
    public function enqueue( EnqueueMode $mode = EnqueueMode::Manual ): bool;
}
