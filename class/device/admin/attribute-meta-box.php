<?php
namespace WPDW\Device\Admin;

class attribute_meta_box {

	/**
	 * @var string
	 */
	private $domain;
	private $post_type;
	private $title;

	/**
	 * @var WP_Domain\{$domain}\property
	 */
	private $property;

	/**
	 * @var array
	 */
	private $attributes;

	/**
	 * @var array
	 */
	private static $def = [
		'title' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'attributes' => [ 'filter' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS, 'flags' => \FILTER_REQUIRE_ARRAY ],
	];

	public function __construct( $domain, Array $args ) {
		if ( ! filter_var( $domain ) )
			return;
		$this->domain = $domain;
		$this->post_type = array_flip( \WPDW\Options::get_domains_alias() )[$this->domain];
		$class = 'WP_Domain\\' . $this->domain . '\\property';
		$this->property = class_exists( $class ) ? $class::getInstance() : null;
		$args = filter_var_array( $args, self::$def );
		if ( $this->attributes = array_filter( $args['attributes'], [ $this, 'attributes_filter' ] ) ) {
			$this->title = $args['title'] ?: get_post_type_object( $this->post_type )->labels->name . __( 'Attributes' );
			$this->init();
		}
	}

	private function attributes_filter( $attr ) {
		if ( $this->property && isset( $this->property->$attr ) )
			return true;
		else if ( get_post_type_object( $this->post_type )->hierarchical )
			return true;
		return false;
	}

	private function init() {
		add_action( 'add_meta_boxes_' . $this->post_type, [ $this, 'add_meta_box' ] );
	}

	public function add_meta_box() {
		if ( post_type_supports( $this->post_type, 'page-attributes' ) )
			remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
		add_meta_box( $this->domain . 'attributediv', $this->title, [ $this, 'attribute_meta_box' ], $this->post_type, 'side', 'core' );
	}

	public function attribute_meta_box() {
		echo '<pre>';
		var_dump( $this );
		echo '</pre>';
	}

}
