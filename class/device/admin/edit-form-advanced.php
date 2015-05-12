<?php
namespace WPDW\Device\Admin;

class edit_form_advanced {

	/**
	 * Edit form id prefix
	 */
	const FORM_ID_PREFIX = 'wp-domain-work-edit-form-';

	/**
	 * @var array
	 */
	private $edit_forms = [];

	/**
	 * @var  WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var WPDW\Device\Admin\template
	 */
	private $template;

	/**
	 * Default arguments, also function as array sorter.
	 * @var array
	 */
	private static $_defaults = [
		/**
		 * Yet!
		 */
		'context'  => 'after_editor',
	];

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * @uses   WPDW\_property_object()
	 * @see    wp-domain-work/inc/functions.php
	 *
	 * @param  string $domain
	 */
	public function __construct( $domain ) {
		if ( ! $domain = filter_var( $domain ) )
			return;
		$this->property = \WPDW\_property_object( $domain );
		$this->template = new template( $domain );

		//
	}

	/**
	 * @access public
	 */
	public function add( Array $args ) {
		//
	}

}
