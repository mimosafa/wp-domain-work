<?php
namespace WPDW\Device;

trait query {
	use \WPDW\Util\Singleton, Module\Methods;

	private function __construct() {
		if ( $this->isDefined( 'query_args' ) ) {
			//_var_dump( $this->query_args );
		}
	}
}
