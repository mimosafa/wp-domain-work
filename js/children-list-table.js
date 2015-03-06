/* @see https://github.com/WordPress/WordPress/blob/4.1-branch/wp-admin/js/postbox.js */

/* global ajaxurl */

(function($) {

	var $sortableList = $('.sortable-children-list > tbody');

	if ($sortableList.length) {

		$sortableList.sortable( {
			axis: 'y',
			containment: 'parent',
			forceHelperSize: true,
			handle: 'td.column-handle',
			items: 'tr',
			opacity: .75,
			tolerance: 'pointer',
			stop: function(e, ui) {
				var $items = ui.item.parent().children();
				reorder($items);
			}
		} );

		function reorder( $items ) {
			$items.each( function(i) {
				if (i % 2 === 0)
					$(this).addClass('alternate');
				else
					$(this).removeClass('alternate');
				var $input = $(this).children('input'),
				    name   = $input.data('name');
				$input.val(i);
				if (i !== $input.data('menu-order'))
					$input.attr('name', name);
				else
					$input.removeAttr('name');
			} );
		}

	}

})(jQuery);
