<?php
/**
 * Rey_WC_Product_Cat_List_Walker class
 *
 * @package WooCommerce/Classes/Walkers
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Product cat list walker class.
 */
class Rey_WC_Product_Cat_List_Walker extends Walker {

	/**
	 * What the class handles.
	 *
	 * @var string
	 */
	public $tree_type = 'product_cat';

	/**
	 * DB fields to use.
	 *
	 * @var array
	 */
	public $db_fields = array(
		'parent' => 'parent',
		'id'     => 'term_id',
		'slug'   => 'slug',
	);

	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker::start_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of category. Used for tab indentation.
	 * @param array  $args Will only append content if style argument value is 'list'.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' !== $args['style'] ) {
			return;
		}

		$css_class = 'children';

		if( isset($args['submenu_class']) ){
			$css_class = $args['submenu_class'];
		}

		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent<ul class='$css_class'>\n";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker::end_lvl()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int    $depth Depth of category. Used for tab indentation.
	 * @param array  $args Will only append content if style argument value is 'list'.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {
		if ( 'list' !== $args['style'] ) {
			return;
		}

		$indent  = str_repeat( "\t", $depth );
		$output .= "$indent</ul>\n";
	}

	/**
	 * Start the element output.
	 *
	 * @see Walker::start_el()
	 * @since 2.1.0
	 *
	 * @param string  $output            Passed by reference. Used to append additional content.
	 * @param object  $cat               Category.
	 * @param int     $depth             Depth of category in reference to parents.
	 * @param array   $args              Arguments.
	 * @param integer $current_object_id Current object ID.
	 */
	public function start_el( &$output, $cat, $depth = 0, $args = array(), $current_object_id = 0 ) {
		$cat_id = intval( $cat->term_id );
		$li_attribute = $before_text = $after_text = '';

		if( isset($args['alphabetic_menu']) && $args['alphabetic_menu'] ){
			$li_attribute = sprintf('data-letter="%s"', mb_substr($cat->name, 0, 1, 'UTF-8') );
		}

		$class_pattern = 'rey-cw';

		if( isset($args['class_pattern']) ){
			$class_pattern = $args['class_pattern'];
		}

		$li_classes[] = $class_pattern . '-item';
		$li_classes[] = $class_pattern . '-item-' . $cat_id;

		if ( $args['current_category'] === $cat_id ) {
			$li_classes[] = $class_pattern . '-item-current';
		}

		if ( $args['has_children'] && $args['hierarchical'] && ( empty( $args['max_depth'] ) || $args['max_depth'] > $depth + 1 ) ) {
			$li_classes[] = $class_pattern . '-item-parent';
		}

		if ( $args['current_category_ancestors'] && $args['current_category'] && in_array( $cat_id, $args['current_category_ancestors'], true ) ) {
			$li_classes[] = $class_pattern . '-item-current-parent';
		}

		$output .= sprintf('<li class="%s" %s>', implode(' ', $li_classes), $li_attribute);

		// show accordion list icon
		if( $args['has_children'] && $args['hierarchical'] && isset($args['accordion_list']) && $args['accordion_list'] ){
			$output .= '<button class="__toggle">'. reycore__get_svg_icon__core(['id'=>'reycore-icon-arrow']) .'</button>';
		}

		$after_text .= $args['show_count'] ? sprintf('<span class="__count">%s</span>', $cat->count) : '';

		// show checkboxes
		if( isset($args['show_checkboxes']) && $args['show_checkboxes'] ){
			$before_text .= '<span class="__checkbox"></span>';
		}

		$output .= sprintf(
			'<a href="%1$s">%3$s %2$s %4$s</a>',
			get_term_link( $cat_id, $args['taxonomy'] ),
			apply_filters( 'list_product_cats', $cat->name, $cat ),
			$before_text,
			$after_text
		);

	}

	/**
	 * Ends the element output, if needed.
	 *
	 * @see Walker::end_el()
	 * @since 2.1.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $cat    Category.
	 * @param int    $depth  Depth of category. Not used.
	 * @param array  $args   Only uses 'list' for whether should append to output.
	 */
	public function end_el( &$output, $cat, $depth = 0, $args = array() ) {
		$output .= "</li>\n";
	}

	/**
	 * Traverse elements to create list from elements.
	 *
	 * Display one element if the element doesn't have any children otherwise,
	 * display the element and its children. Will only traverse up to the max.
	 * depth and no ignore elements under that depth. It is possible to set the.
	 * max depth to include all depths, see walk() method.
	 *
	 * This method shouldn't be called directly, use the walk() method instead.
	 *
	 * @since 2.5.0
	 *
	 * @param object $element           Data object.
	 * @param array  $children_elements List of elements to continue traversing.
	 * @param int    $max_depth         Max depth to traverse.
	 * @param int    $depth             Depth of current element.
	 * @param array  $args              Arguments.
	 * @param string $output            Passed by reference. Used to append additional content.
	 * @return null Null on failure with no changes to parameters.
	 */
	public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
		if ( ! $element || ( 0 === $element->count && ! empty( $args[0]['hide_empty'] ) ) ) {
			return;
		}
		parent::display_element( $element, $children_elements, $max_depth, ($depth ? $depth : 0), $args, $output );
	}
}
