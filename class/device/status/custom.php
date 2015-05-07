<?php
namespace WPDW\Device\Status;

class custom {
	use \WPDW\Util\Singleton;

	/**
	 * @var array
	 */
	private $labels = [];

	/**
	 * Filter definition
	 * 
	 * @var array
	 */
	public static $definition = [
		'name'        => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'description' => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
		'action'      => \FILTER_SANITIZE_FULL_SPECIAL_CHARS,
	];

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
		$self = self::getInstance();
		if ( $status = filter_var( $status, \FILTER_VALIDATE_REGEXP, [ 'options' => [ 'regexp' => '/[a-z0-9_]+/' ] ] ) ) {
			if ( $labels = filter_var_array( $labels, self::$definition ) ) {
				$self->labels[$status] = array_merge(
					array_fill_keys( array_keys( self::$definition ), $labels['name'] ),
					$labels
				);
			}
		}
	}

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	protected function __construct() {
		add_filter( 'display_post_states', [ $this, 'display_post_states' ], 10, 2 );
		add_action( 'post_submitbox_misc_actions', [ $this, 'post_submitbox' ] );
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
			$queried_status = isset( $_REQUEST['post_status'] ) ? $_REQUEST['post_status'] : '';
			$post_status = $post->post_status;
			if ( array_key_exists( $post_status, $this->labels ) && $post_status !== $queried_status )
				$post_states[$post_status] = $this->labels[$post_status]['name'];
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
		$statusNow = $post->post_status;
?>
<script type='text/javascript'>
  jQuery(document).ready(function($){
    var customs = <?php echo json_encode( $this->labels ); ?>,
        statusNow = '<?php echo esc_js( $statusNow ); ?>', labelNow  = '';
    $.each(customs, function(i, arr) {
      var opt = $('<option />', { value: i, text: arr['name'] });
      if (i===statusNow) { opt.attr('selected'); labelNow += arr['name']; }
      $('select#post_status').append(opt);
    });
    if (labelNow) { $('#post-status-display').text(labelNow); }
  });
</script>
<?php
	}

}
