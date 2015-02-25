<?php

namespace WP_Domain_Work\WP\post\post_status;

/**
 *
 */
class custom_post_status {
	use \WP_Domain_Work\Utility\Singleton;

	/**
	 * custom statuses array (use if didn't do action 'init')
	 * @var array
	 */
	private static $statuses = [];

	private static $displays = [];

	/**
	 * register_post_status default arguments
	 * @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-includes/post.php#L983
	 * @var array
	 */
	private static $defaults = [
		'label'                     => false,
		'label_count'               => false,
		'exclude_from_search'       => null,
		'public'                    => null,
		'internal'                  => null,
		'protected'                 => null,
		'private'                   => null,
		'publicly_queryable'        => null,
		'show_in_admin_status_list' => null,
		'show_in_admin_all_list'    => null,
	];

	protected function __construct() {
		$this->init();
	}

	protected function init() {
		if ( ! did_action( 'init' ) ) {
			add_action( 'init', [ $this, '_register_post_statuses'], 11 );
		}
		if ( is_admin() ) {
			add_action( 'load-edit.php', [ $this, 'list_table' ] );
			add_action( 'admin_footer', [ $this, 'custom_status_display_in_admin' ] );
		}
	}

	public static function set( $status, $args = [] ) {
		if ( ! is_string( $status ) || ! $status ) {
			return false;
		}
		$_CPS = self::getInstance();
		$_CPS->_post_status( $status, $args );
	}

	/**
	 * did_action( 'init' ) === true な前提なので、即座にregister しています
	 * - 最初は'wp' action にフックしていたけど、edit.php で絞込表示ができなかったので…
	 */
	private function _post_status( $status, Array $args ) {
		$args = wp_parse_args( $args, self::$defaults );
		if ( $args['show_in_admin_status_list'] ) {
			self::$displays[] = [ $status, $args['label'] ];
		}
		if ( did_action( 'init' ) ) {
			register_post_status( $status, $args );
		}
		self::$statuses[$status] = $args;
	}

	public function _register_post_statuses() {
		if ( ! self::$statuses ) {
			return;
		}
		foreach ( self::$statuses as $status => $args ) {
			register_post_status( $status, $args );
		}
	}

	public function list_table() {
		add_filter( 'display_post_states', [ $this, 'display_post_states'], 10, 2 );
	}

	public function display_post_states( $post_states, $post ) {
		$queried_status = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : '';
		$post_status = $post->post_status;
		if ( array_key_exists( $post_status, self::$statuses ) && $post_status !== $queried_status ) {
			$post_states[$post_status] = self::$statuses[$post_status]['label'];
		}
		return $post_states;
	}

	public function custom_status_display_in_admin() {
		if ( ! self::$displays ) {
			return;
		}
		add_action( 'admin_footer-post.php', [ $this, 'meta_boxes' ] );
		add_action( 'admin_footer-edit.php', [ $this, 'inline_edit_status' ] );
	}

	public function meta_boxes() {
		global $post;
		$statusNow = $post->post_status;
?>
<script type='text/javascript'>
  jQuery(document).ready(function($){
    var customStatuses = <?php echo json_encode( self::$displays ); ?>,
        statusNow = '<?php echo esc_js( $statusNow ); ?>', labelNow  = '';
    $.each(customStatuses, function(i, arr) {
      var opt = $('<option />', { value: arr[0], text: arr[1] });
      if (arr[0]===statusNow) { opt.attr('selected'); labelNow += arr[1]; }
      $('select#post_status').append(opt);
    });
    if (labelNow) { $('#post-status-display').text(labelNow); }
  });
</script>
<?php
	}

	public function inline_edit_status() {
?>
<script type='text/javascript'>
  jQuery(document).ready(function($){
    var customStatuses = <?php echo json_encode( self::$displays ); ?>,
        inlineEdit = $('.inline-edit-status select[name="_status"]');
    $.each(customStatuses, function(i, arr) {
      var opt = $('<option />', { value: arr[0], text: arr[1] });
      inlineEdit.append(opt);
    });
  });
</script>
<?php
	}

}
