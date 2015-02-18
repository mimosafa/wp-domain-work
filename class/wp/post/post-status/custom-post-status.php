<?php

namespace WP_Domain_Work\WP\post\post_status;

class custom_post_status {
	use \WP_Domain_Work\Utility\Singleton;

	private static $displays = [];

	private static $defaults = [
		'label'                     => '',
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => null
	];

	protected function __construct() {
		$this->init();
	}

	protected function init() {
		if ( is_admin() ) {
			add_action( 'admin_footer', [ $this, 'custom_status_display_in_admin' ] );
		}
	}

	/**
	 * did_action( 'init' ) === true な前提なので、即座にregister しています
	 * - 最初は'wp' action にフックしていたけど、edit.php で絞込表示ができなかったので…
	 */
	public static function set( $status, $args = [] ) {
		if ( ! is_string( $status ) || ! $status ) {
			return false;
		}
		$_CPS = self::getInstance();
		$_CPS->register_post_status( $status, $args );
	}

	private function register_post_status( $status, Array $args ) {
		$args = wp_parse_args( $args, self::$defaults );
		if ( $args['show_in_admin_status_list'] ) {
			self::$displays[] = [ $status, $args['label'] ];
		}
		register_post_status( $status, $args );
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
		global $post;
		$statusNow = $post->post_status;
?>
<script type='text/javascript'>
  jQuery(document).ready(function($){
    var customStatuses = <?php echo json_encode( self::$displays ); ?>,
        statusNow  = '<?php echo esc_js( $statusNow ); ?>', labelNow  = '',
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
