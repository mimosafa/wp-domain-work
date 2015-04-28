<?php
namespace WPDW\Device;

trait query {
	use Module\Initializer, Module\Methods;

	private function __construct( Array $args ) {
		if ( $this->isDefined( 'query_args' ) ) {
			//_var_dump( $this->query_args );
		}
	}
}
