<?php

namespace XWP\Dependency\Resources;

class Style extends Asset {
    protected function type(): string {
        return 'style';
    }

    protected function default_args(): array {
        return array(
			'media' => 'all',
		);
    }

    protected function inline_args(): array {
        return \xwp_array_slice_assoc( parent::inline_args(), 'handle', 'data' );
    }
}
