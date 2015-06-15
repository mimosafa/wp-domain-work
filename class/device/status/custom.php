<?php
namespace WPDW\Device\Status;

class custom {
	use \WPDW\Util\Singleton;

	/**
	 * @var array
	 */
	private $labels = [];

	private static $default = [
		'name' => '%s',
		'description' => '%s',
		'action' => 'Save as %s'
	];

	/**
	 * Filter definition
	 * 
	 * @var array
	 */
	public static function get_filter_definition() {
		static $definition;
		if ( ! $definition )
			$definition = array_map( function() {
				return \FILTER_SANITIZE_FULL_SPECIAL_CHARS;
			}, self::$default );
		return $definition;
	}

	/**
	 * Add custom post status
	 *
	 * @access public
	 * 
	 * @param  string $status
	 * @param  array  $labels {
	 *     @type string $name
	 *     @type string $description
	 *     @type string $action
	 * }
	 * @return (void)
	 */
	public static function add( $status, Array $labels ) {
		static $options = [ 'options' => [ 'regexp' => '/[a-z0-9_]+/' ] ];
		if ( $status = filter_var( $status, \FILTER_VALIDATE_REGEXP, $options ) ) {
			$self = self::getInstance();
			if ( $labels = filter_var_array( $labels, self::get_filter_definition() ) )
				$self->labels[$status] = $labels;
		}
	}

	public static function get_defaults( $label ) {
		$callback = function ( $str ) use ( $label ) {
			return sprintf( __( $str ), $label );
		};
		return array_map( $callback, self::$default );
	}

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox' ] );
		add_action( 'admin_footer-post.php',     [ $this, 'overwrite_saveDraft' ], 100 );
		add_action( 'admin_footer-post-new.php', [ $this, 'overwrite_saveDraft' ], 100 );
	}

	/**
	 * @access public
	 *
	 * @see    https://github.com/WordPress/WordPress/blob/4.2-branch/wp-admin/includes/template.php#L1713
	 *
	 * @param  array  $post_states
	 * @param  string $post
	 * @return array
	 */
	public function display_post_states( $post_states, $post ) {
		if ( $this->labels ) {
			static $queried_status;
			if ( ! isset( $queried_status ) )
				$queried_status = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : '';
			static $built_ins = [
				'publish', 'future', 'draft', 'pending',
				'private', 'trash', 'auto-draft', 'inherit'
			];
			$status = $post->post_status;
			if ( ! in_array( $status, $built_ins, true ) && array_key_exists( $status, $this->labels ) && $status !== $queried_status )
				$post_states[$status] = $this->labels[$status]['name'];
		}
		return $post_states;
	}

	/**
	 * JavaScript for displaing custom statuses in submit box
	 *
	 * @access public
	 *
	 * @see    http://codex.wordpress.org/Function_Reference/register_post_status
	 * @see    https://github.com/WordPress/WordPress/blob/4.2-branch/wp-admin/includes/meta-boxes.php#L217
	 * 
	 * @return (void)
	 */
	public function post_submitbox() {
		global $post;
		$customs   = json_encode( $this->labels );
		$statusNow = esc_js( $post->post_status );
		echo <<<EOF
<script type='text/javascript'>
	jQuery( document ).ready( function( $ ) {
		var customs = {$customs},
		    statusNow = '{$statusNow}',
		    \$opts = $();
		$.each( customs, function( i, arr ) {
			var opt = $( '<option />', { value: i, text: arr['name'] } );
			if ( i === statusNow ) {
				opt.attr( 'selected', 'selected' );
				$( '#post-status-display' ).text( arr['name'] );
				$( '#save-post' ).val( arr['action'] );
			}
			\$opts = \$opts.add( opt );
		} );
		$( 'select#post_status' ).append( \$opts );
	});
</script>
EOF;
	}

	/**
	 * @access public
	 */
	public function overwrite_saveDraft() {
		$actions = json_encode( array_filter( array_map( function( $arr ) {
			return array_key_exists( 'action', $arr ) ? $arr['action'] : null;
		}, $this->labels ) ) );
		echo <<<EOF
<script type='text/javascript'>
	window.postL10n = window.postL10n || {};
	jQuery( document ).ready( function( $ ) {
		var \$postStatus = $( '#post_status' ),
		    actions = {$actions},
		    defaultTxt = postL10n.saveDraft;
		\$postStatus.on( 'change', function() {
			var val = $(this).val();
			if ( actions[val] !== undefined )
				postL10n.saveDraft = actions[val];
			else
				postL10n.saveDraft = defaultTxt;
		} );
	} );
</script>
EOF;
	}

}
