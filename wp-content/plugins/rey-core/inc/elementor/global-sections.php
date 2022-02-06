<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if(!class_exists('ReyCore_GlobalSections')):
	/**
	 * Global Sections Manager
	 *
	 * @since 1.0.0
	 */
	class ReyCore_GlobalSections
	{
		const POST_TYPE = 'rey-global-sections';
		const POST_TYPE_TAXONOMY = 'rey-global-sections-cat';
		const GS_TRANSIENT = '_reycore_global_sections';
		const GS_ITEM_TRANSIENT = '_reycore_gs_';

		/**
		 * Holds the reference to the instance of this class
		 * @var ReyCore_GlobalSections
		 */
		private static $_instance = null;

		/**
		 * Holds sections ID's to force load their css into WP Head
		 */
		private static $sections_css = [];

		private $has_taxonomy = false;

		public static $global_sections = [];

		/**
		 * ReyCore_GlobalSections constructor.
		 */
		private function __construct()
		{

			if( defined( 'ELEMENTOR_VERSION' ) && class_exists('\Elementor\Plugin') ) {

				add_action( 'init', [$this, 'post_type'] );
				add_action( 'admin_menu', [$this, 'register_admin_menu'], 50 );
				add_action( 'admin_init', [$this, 'admin_filters'] );
				add_filter( "manage_". self::POST_TYPE ."_posts_columns", [$this, 'add_columns'] );
				add_action( "manage_". self::POST_TYPE ."_posts_custom_column" , [$this, 'manage_column'], 10, 2 );
				add_action( 'add_meta_boxes', [$this, 'add_meta_box'] );
				add_action( 'hidden_meta_boxes', [$this, 'hide_meta_box_attributes'], 10, 2 );
				add_filter( 'reycore/options/header_layout_options', [$this, 'header_layout_options'] );
				add_filter( 'reycore/options/footer_layout_options', [$this, 'footer_layout_options'] );
				add_shortcode('rey_global_section', [$this, 'insert_shortcode']);
				add_action( 'wp', [$this, 'add_site_sections'] );
				add_action( 'wp', [$this, 'remove_header_footer'] );
				add_action( 'wp', [$this, 'elementor_canvas'] );
				add_filter( 'template_include', [$this, 'add_post_type_template'] );
				add_action( 'wp_enqueue_scripts', [$this, 'load_sections_css'], 500 );
				add_action( 'admin_init', [$this, 'add_duplicate_post_support'] );
				add_action( 'reycore/global_section_template/after_content', [$this, 'add_gs_notices']);
				add_action( 'acf/init', [ $this, 'add_taxonomy_setting' ] );
				add_action( 'restrict_manage_posts', [ $this, 'add_filter_by_category' ] );
				add_filter( 'parent_file', [$this, 'set_current_menu'] );
				add_filter( 'body_class', [$this, 'body_classes'] );
				add_action( 'admin_init', [$this, 'filter_html_tag'] );
				add_action( 'wp_ajax_rey_gs_set_type', [ $this, 'set_type_when_empty' ] );
				add_action( 'save_post', [$this, 'clear_gs_transient'], 1 );
				add_action( 'delete_post', [$this, 'clear_gs_transient'], 1 );
				add_action( 'elementor/element/wp-post/section_page_style/after_section_start', [$this, 'notice_gs_body_style_options'], 10);
				add_filter( 'elementor/frontend/admin_bar/settings', [$this, 'filter_edit_with_elementor'], 20);
				add_filter( 'wp_link_query_args', [ $this, 'filter_wp_link_query_args' ], 20 );
				add_filter( 'theme_mod_social__enable', [$this, 'disable_stuff'], 20);
				add_filter( 'display_post_states', [ $this, 'add_post_states' ], 20, 2 );
				add_filter( 'post_row_actions', [ $this, 'post_row_actions' ], 10, 2 );
			}
		}

		public function post_type() {

			$this->check_tax_enabled();

			$labels = array(
				'name'                  => _x( 'Global Sections', 'Post Type General Name', 'rey-core' ),
				'singular_name'         => _x( 'Global Section', 'Post Type Singular Name', 'rey-core' ),
				'menu_name'             => __( 'Global Sections', 'rey-core' ),
				'name_admin_bar'        => __( 'Global Section', 'rey-core' ),
				'archives'              => __( 'List Archives', 'rey-core' ),
				'parent_item_colon'     => __( 'Parent List:', 'rey-core' ),
				'all_items'             => __( 'All Global Sections', 'rey-core' ),
				'add_new_item'          => __( 'Add New Global Section', 'rey-core' ),
				'add_new'               => __( 'Add New', 'rey-core' ),
				'new_item'              => __( 'New Global Section', 'rey-core' ),
				'edit_item'             => __( 'Edit Global Section', 'rey-core' ),
				'update_item'           => __( 'Update Global Section', 'rey-core' ),
				'view_item'             => __( 'View Global Section', 'rey-core' ),
				'search_items'          => __( 'Search Global Section', 'rey-core' ),
				'not_found'             => __( 'Not found', 'rey-core' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'rey-core' )
			);

			$args = array(
				'labels'              => $labels,
				'public'              => true,
				'rewrite'             => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'show_in_admin_bar'   => true,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'hierarchical'        => false,
				'supports'            => [ 'title', 'elementor' ],
			);

			if( $this->has_taxonomy ) {
				$args['taxonomies'][] = self::POST_TYPE_TAXONOMY;
			}

			register_post_type( self::POST_TYPE, $args );

			if( $this->has_taxonomy ) {
				register_taxonomy(
					self::POST_TYPE_TAXONOMY,
					self::POST_TYPE,
					[
						'public'              => false,
						'rewrite'             => false,
						'hierarchical'        => true,
						'show_ui'             => true,
						'show_in_nav_menus'   => false,
						'show_in_admin_bar'   => true,
						'exclude_from_search' => true,
						'show_admin_column'   => true,
						'labels'              => [
							'name'          => _x( 'Global Section Categories', 'Global Sections', 'rey-core' ),
							'singular_name' => _x( 'Global Section Category', 'Global Sections', 'rey-core' ),
							'all_items'     => _x( 'All Global Section Categories', 'Global Sections', 'rey-core' ),
						],

					]
				);
			}
		}

		/**
		 * Checks if taxonomy is enabled.
		 *
		 * @since 1.1.1
		 */
		public function check_tax_enabled(){
			if( reycore__acf_get_field('gs_tax_enable_button', REY_CORE_THEME_NAME) ){
				$this->has_taxonomy = true;
			}
		}

		/**
		 * Get global section types
		 */
		public static function get_global_section_types(){
			return apply_filters('reycore/global_sections/types', [
				'generic'  => __( 'Generic Section', 'rey-core' ),
				'header'  => __( 'Header', 'rey-core' ),
				'footer'  => __( 'Footer', 'rey-core' ),
				'cover'  => __( 'Page Cover', 'rey-core' ),
				'megamenu'  => __( 'Mega Menu', 'rey-core' ),
			]);
		}

		/**
		 * Sets the current menu item.
		 *
		 * @since 1.1.1
		 */
		function set_current_menu( $parent_file ) {

			global $submenu_file, $current_screen, $pagenow;

			if( ! $this->has_taxonomy ) {
				return $parent_file;
			}

			if( ! ($dashboard_id = reycore__get_dashboard_page_id()) ){
				return $parent_file;
			}

			if ( $current_screen->post_type == self::POST_TYPE ) {
				if ( $pagenow == 'post.php' ) {
					$submenu_file = 'edit.php?post_type=' . $current_screen->post_type;
				}
				if ( $pagenow == 'edit-tags.php' ) {
					$submenu_file = 'edit-tags.php?taxonomy='. self::POST_TYPE_TAXONOMY .'&post_type=' . $current_screen->post_type;
				}
				$parent_file = $dashboard_id;
			}

			return $parent_file;
		}


		/**
		 * Register the admin menu.
		 *
		 * @since  1.0.0
		 */
		public function register_admin_menu() {
			if( $dashboard_id = reycore__get_dashboard_page_id() ){
				add_submenu_page(
					$dashboard_id,
					__( 'Global Sections', 'rey-core' ),
					__( 'Global Sections', 'rey-core' ),
					'edit_pages',
					'edit.php?post_type=' . self::POST_TYPE
				);

				if( $this->has_taxonomy ) {
					add_submenu_page(
						$dashboard_id,
						__( 'Global S. Categories', 'rey-core' ),
						__( 'Global S. Categories', 'rey-core' ),
						'edit_pages',
						sprintf( 'edit-tags.php?taxonomy=%s&post_type=%s', self::POST_TYPE_TAXONOMY, self::POST_TYPE ),
						null
					);
				}
			}
		}

		/**
		 * Adds taxonomy enable/disable setting in Rey settings
		 *
		 * @since 1.1.1
		 */
		public function add_taxonomy_setting(){

			acf_add_local_field(array(
				'key'           => 'field_gs_tax_enable_button',
				'name'          => 'gs_tax_enable_button',
				'label'         => esc_html__('Enable Taxonomies for Global Sections', 'rey-core'),
				'type'          => 'true_false',
				'instructions'  => esc_html__('Enable or disable Category taxonomy for Global Sections.', 'rey-core'),
				'default_value' => false,
				'ui'            => 1,
				'parent'        => 'group_5c990a758cfda',
				'menu_order'    => 300,
			));
		}

		/**
		 * Add filter list in GS list
		 *
		 * @since 1.0.0
		 */
		public function add_filter_by_category( $post_type ) {

			if( ! $this->has_taxonomy ) {
				return;
			}

			if ( self::POST_TYPE !== $post_type ) {
				return;
			}

			$all_items = get_taxonomy( self::POST_TYPE_TAXONOMY )->labels->all_items;

			$dropdown_options = array(
				'show_option_all' => $all_items,
				'hide_empty' => 0,
				'hierarchical' => 1,
				'show_count' => 0,
				'orderby' => 'name',
				'value_field' => 'slug',
				'taxonomy' => self::POST_TYPE_TAXONOMY,
				'name' => self::POST_TYPE_TAXONOMY,
				'selected' => empty( $_GET[ self::POST_TYPE_TAXONOMY ] ) ? '' : $_GET[ self::POST_TYPE_TAXONOMY ],
			);

			echo '<label class="screen-reader-text" for="cat">' . _x( 'Filter by category', 'Global Sections', 'rey-core' ) . '</label>';
			wp_dropdown_categories( $dropdown_options );
		}

		/**
		 * Add Columns
		 *
		 * @since 1.0.0
		 **/
		public function add_columns( $columns )
		{
			$columns['reycore_type_column'] = __( 'Type', 'rey-core' );
			// $columns['reycore_elcanv_column'] = __( 'Elementor Canvas', 'rey-core' );
			$columns['reycore_shortcode_column'] = __( 'Shortcode', 'rey-core' );
			return $columns;
		}

		/**
		 * Add Columns
		 *
		 * @since 1.0.0
		 **/
		public function manage_column( $column, $post_id ) {

			switch ( $column ) {

				case 'reycore_shortcode_column' : ?>
					<div class="rey-codeBlock">
						<button class="button button-small js-rey-copy-code" type="button" data-text="<?php esc_html_e(' - COPIED!', 'rey-core') ?>"><?php esc_html_e('COPY SHORTCODE', 'rey-core') ?></button>
						<pre>[rey_global_section id="<?php echo $post_id; ?>"]</pre>
					</div>
					<?php
				break;

				case 'reycore_type_column' :
					if( reycore__acf_get_field('gs_type') ):
						printf('<strong>%s</strong>', ucwords( reycore__acf_get_field('gs_type') ));
					endif;
				break;

				// case 'reycore_elcanv_column' :
				// 	if( reycore__acf_get_field('enable_canvas') ):
				// 		echo reycore__acf_get_field('canvas_position');
				// 	else:
				// 		echo esc_html__('No', 'rey-core');
				// 	endif;
				// break;
			}

		}

		function admin_filters(){
			add_action( 'restrict_manage_posts', [$this, 'admin__add_filter_list'], 20 );
			add_action( 'pre_get_posts', [$this, 'admin__filter_the_list'] );
		}


		function admin__add_filter_list( $post_type, $args = [] ){

			if( $post_type !== self::POST_TYPE ) {
				return;
			}

			$types = self::get_global_section_types();
			$active = isset($_GET['rey_gs_type']) ? reycore__clean($_GET['rey_gs_type']) : '';

			printf('<select name="%s">', 'rey_gs_type');
			printf('<option value="">%s</option>', esc_html__('Select Type', 'rey-core'));
			printf('<option value="-1" %2$s>%1$s</option>', esc_html__('Unset', 'rey-core'), selected($active, '-1', false));
			foreach ($types as $key => $type) {
				printf('<option value="%1$s" %3$s>%2$s</option>', $key, $type, selected($active, $key, false));
			}
			echo '</select>';
		}

		function admin__filter_the_list( $query ){
			global $pagenow;

			if ( ! ($query->is_admin && $pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === self::POST_TYPE) ) {
				return $query;
			}

			if( ! (isset($_GET['rey_gs_type']) && $active_type = reycore__clean($_GET['rey_gs_type'])) ){
				return $query;
			}

			$meta_query = [
				'relation' => 'AND',
			];

			$query_data = [
				'key' => 'gs_type',
				'value' => $active_type,
				'compare' => 'IN'
			];

			if( $active_type === '-1' ){
				$query_data['compare'] = 'NOT EXISTS';
			}

			if( ! $query->meta_query ){
				$query->meta_query = (object) [
					'queries' => []
				];
			}

			$query->meta_query->queries[] = $query_data;

			foreach ( $query->meta_query->queries as $q ) {
				$meta_query[] = $q;
			}

			$query->set('meta_query', $meta_query);

		}


		/**
		 * Add Meta Box
		 *
		 * @since 1.0.0
		 **/
		public function add_meta_box(){
			add_meta_box(
				'rey-gs-shortcode-box',
				'Global Sections Usage',
				[$this, 'shortcode_box'],
				self::POST_TYPE,
				'side',
				'default'
			);
		}

		/**
		 * Hide Attributes meta box for global sections
		 *
		 * @since 1.0.0
		 */
		public function hide_meta_box_attributes( $hidden, $screen) {

			if( get_post_type() === self::POST_TYPE ) {
				$hidden[] = 'pageparentdiv';
			}
			return $hidden;
		}

		/**
		 * Add ShortCode Meta Box
		 *
		 * @since 1.0.0
		 **/
		public function shortcode_box( $post ){
			?>

			<div class="rey-codeBlock">
				<h4 style="margin-bottom:12px;"><?php esc_html_e('Shortcode', 'rey-core') ?></h4>
				<button class="button button-small js-rey-copy-code" type="button" data-text="<?php esc_html_e(' - COPIED!', 'rey-core') ?>"><?php esc_html_e('COPY SHORTCODE', 'rey-core') ?></button>
				<pre>[rey_global_section id="<?php echo $post->ID; ?>"]</pre>
			</div>

			<div class="rey-codeBlock">
				<h4 style="margin-bottom:12px;"><?php esc_html_e('Php Code', 'rey-core') ?></h4>
				<button class="button button-small js-rey-copy-code" type="button" data-text="<?php esc_html_e(' - COPIED!', 'rey-core') ?>"><?php esc_html_e('COPY PHP CODE', 'rey-core') ?></button>
				<pre>&lt;?php echo do_shortcode('[rey_global_section id=&quot;<?php echo $post->ID; ?>&quot;]'); ?&gt;</pre>
			</div>
			<?php
		}

		public static function gs_transient_name($post_id){
			return self::GS_ITEM_TRANSIENT . $post_id;
		}

		/**
		 * Do Section
		 *
		 * @since 1.0.0
		 **/
		public static function do_section($post_id = null, $with_css = false)
		{
			if( is_null($post_id) ){
				return;
			}

			$post_id = absint( apply_filters('reycore/elementor/gs_id', $post_id, self::POST_TYPE) );

			if( !$post_id ){
				return;
			}

			$post_status = get_post_status( $post_id );

			if( $post_status !== 'publish' ) {

				if( apply_filters('reycore/elementor/global_sections/hide_notice', false) ){
					return;
				}

				return sprintf('<div class="rey-siteContainer"><p class="elementor-alert elementor-alert-warning ">%s</p></div>', sprintf( esc_html__('Global Section does not exist or is in draft mode (unpublished), or its ID (%s) invalid.', 'rey-core'), $post_id ) );
			}

			if( class_exists('\Elementor\Plugin') && is_callable( '\Elementor\Plugin::instance' ) ) {

				$ajax_or_customizer = is_customize_preview() || wp_doing_ajax();

				/* temp
				if( ! $ajax_or_customizer ){
					if( false !== ($gs_content = get_transient( self::gs_transient_name( $post_id ) )) ){
						return $gs_content;
					}
				} */

				$edit_markup_start = '';
				$edit_markup_end = '';

				if ( self::is_edit_mode() ) {
					$edit_markup_start = self::edit_markup_start( $post_id );
					$edit_markup_end = self::edit_markup_end();
				}

				$elementor_instance = Elementor\Plugin::instance();

				if( $ajax_or_customizer ){
					do_action('reycore/elementor/global_section/ajax_load', $post_id, $elementor_instance);
				}

				$GLOBALS['global_section_id'] = $post_id;

				$gs_markup = $edit_markup_start . $elementor_instance->frontend->get_builder_content_for_display( $post_id, $with_css ) . $edit_markup_end;

				unset($GLOBALS['global_section_id']);

				/* temp
				set_transient( self::gs_transient_name( $post_id ), $gs_markup, 7 * DAY_IN_SECONDS ); */

				return $gs_markup;
			}

			return false;
		}

		/**
		 * Global section edit markup start
		 *
		 * @since 1.0.0
		 **/
		private static function edit_markup_start( $post_id = 0 )
		{
			$html = '<div class="rey-gs-editSection">';
			$doc = \Elementor\Plugin::instance()->documents->get( $post_id );
			$html .= sprintf(
				'<a class="rey-gs-editSection__url js-rey-gs-editSection__url" href="%s" target="_blank" title="%s">%s %s</a>',
				esc_url( $doc ? $doc->get_edit_url() : '' ),
				esc_html__('Opens in a new Elementor window/tab', 'rey-core'),
				esc_html__('Edit global section: ', 'rey-core'),
				esc_html ( get_the_title( $post_id ) )
			);

			return $html;
		}

		/**
		 * Global section edit markup end
		 *
		 * @since 1.0.0
		 **/
		private static function edit_markup_end()
		{
			return '</div>';
		}

		/**
		 * Add ShortCode
		 *
		 * @since 1.0.0
		 **/
		function insert_shortcode($atts){

			if( isset($atts['id']) && !empty($atts['id']) ){
				return self::do_section( $atts['id'] );
			}

			elseif( isset($atts['slug']) && !empty($atts['slug']) ){
				if ( ($post = get_page_by_path( reycore__clean( $atts['slug'] ), OBJECT, self::POST_TYPE )) && isset($post->ID) ){
					return self::do_section( $post->ID );
				}
			}

			return false;
		}

		/**
		 * Get an array of posts.
		 *
		 * @static
		 * @access public
		 * @param array $args Define arguments for the get_posts function.
		 * @return array
		 */
		public static function get_posts( $args = [] ) {

			if( ! ($items = get_transient(self::GS_TRANSIENT)) ){

				$args = wp_parse_args($args, [
					'posts_per_page'   => 100,
					'orderby'          => 'date',
					'post_type'        => self::POST_TYPE,
					'post_status'      => 'publish',
				]);

				// Get the posts.
				$posts = get_posts( $args );

				// Properly format the array.
				$items = [];

				foreach ( $posts as $post ) {
					if( ( $gs_type = get_post_meta( $post->ID, 'gs_type', true ) ) ){
						$items[ $gs_type ][ $post->ID ] = $post->post_title;
					}
				}

				set_transient(self::GS_TRANSIENT, $items, WEEK_IN_SECONDS );
			}

			self::$global_sections = $items;

			return $items;
		}

		/**
		 * Get global sections list
		 *
		 * @since 1.0.0
		 **/
		public static function get_global_sections($type = 'generic', $default = [], $deprecated = false)
		{
			$posts = empty( self::$global_sections ) ? self::get_posts() : self::$global_sections;

			if( empty( $posts ) ){
				return $default;
			}

			if( is_array($type) ){
				$posts_to_return = [];
				foreach( $type as $t ){
					if( isset( $posts[$t] ) ){
						foreach( $posts[$t] as $id => $post ){
							$posts_to_return[$id] = $post;
						}
					}
				}
				return $default + $posts_to_return;
			}
			elseif( !empty($type) ) {
				if( isset( $posts[$type] ) ){
					return $default + $posts[$type];
				}
			}
			else {
				return $default + $posts;
			}

			return $default;
		}

		/**
		 * Update header list
		 *
		 * @since 1.0.0
		 **/
		public static function header_layout_options( $opts )
		{
			return self::get_global_sections('header', $opts);
		}

		/**
		 * Update footer list
		 *
		 * @since 1.0.0
		 **/
		public static function footer_layout_options( $opts )
		{
			return self::get_global_sections('footer', $opts);
		}

		/**
		 * Add sections into their respective hooks
		 * Remove sections if it's chosen in the page post meta options
		 *
		 * @since 1.0.0
		 */
		public function add_site_sections(){

			if( get_post_type() === self::POST_TYPE ) {
				return;
			}

			$sections = get_theme_mod('global_sections', false);

			// Individual Page Stuff
			if( class_exists('ACF') ):

				/**
				 * Remove sections where it's defined in a page
				 */
				if( is_array($sections) && !empty($sections) && $remove = reycore__acf_get_field( 'remove_global_sections' ) )  {

					if( is_array($remove) && !empty($remove) ){
						foreach ($remove as $r) {

							$get = explode('__', $r);
							// unset similarities
							foreach ($sections as $key => $value) {
								if( $get[0] == $value['id'] && $get[1] == $value['hook'] ) {
									unset($sections[$key]);
								}
							}
						}
					}
				}

				/**
				 * Add sections per page
				 */
				if( $global_sections = reycore__acf_get_field( 'global_sections' ) ) {
					foreach ($global_sections as $key => $value) {
						if( is_array( $sections ) ) {

							$sections[] = [
								'id' => $value['global_sections_id'],
								'hook' => $value['global_sections_hook'],
								// 'condition' => $value['global_sections_condition']
							];
						}
					}
				}
			endif;

			/**
			 * Display sections
			 */
			if (is_array($sections) && !empty($sections)) {
				foreach($sections as $k => $gs){

					if( isset($gs['id']) && !empty($gs['id']) && isset($gs['hook']) && !empty($gs['hook']) ) {

						// ignore some hooks
						if( ! in_array($gs['hook'], ['after_site_container', 'before_footer', 'after_footer', 'after_site_wrapper']) ){
							array_push( self::$sections_css, $gs['id']);
						}
						add_action( "rey/{$gs['hook']}", function() use ($gs){
							echo self::do_section( $gs['id'] );
						}, 10);
					}
				}
			}

			/**
			 * Add header
			 */
			if( $gs_header = reycore__get_option( 'header_layout_type', '' ) ) {

				if( $gs_header != 'default' && $gs_header != 'none' ) {

					$css_stylesheet = apply_filters('reycore/elementor/gs_id', $gs_header, self::POST_TYPE);

					// add CSS in head
					array_push( self::$sections_css, $css_stylesheet);

					add_action( "rey/header/content", function() use ($gs_header) {

						if( ! apply_filters('reycore/header/display', true) ){
							return;
						}

						echo self::do_section( $gs_header );

					}, 10);
				}
			}


			/**
			 * Add footer
			 */
			if( $gs_footer = reycore__get_option( 'footer_layout_type', '' ) ) {
				if( $gs_footer != 'default' && $gs_footer != 'none' ) {

					// add CSS in head
					array_push( self::$sections_css, $gs_footer);

					add_action( "rey/footer/content", function() use ($gs_footer) {

						if( ! apply_filters('reycore/footer/display', true) ){
							return;
						}

						echo self::do_section( $gs_footer );
					}, 10);
				}
			}

		}

		/**
		 * Add sections into elementor canvas
		 *
		 * @since 1.0.0
		 **/
		public function elementor_canvas()
		{
			if( is_page_template('elementor_canvas') && get_post_type() !== self::POST_TYPE ) {

				if( ! reycore__acf_get_field( 'enable_canvas' ) ){
					return;
				}

				// check for position
				if( $pos = reycore__acf_get_field( 'canvas_position' ) ) {
					// hook into elementor canvas
					add_action( "elementor/page_templates/canvas/{$pos}", function() use ($id){
						echo self::do_section( $id );
					}, 10 );
				}

			}
		}


		/**
		 * Force custom template for this global sections
		 *
		 * @since 1.0.0
		 */
		public function add_post_type_template( $template_path ) {

			if ( get_post_type() == self::POST_TYPE ) {
				if ( is_single() ) {
					// checks if the file exists in the theme first,
					// otherwise serve the file from the plugin
					if ( $theme_file = locate_template( array ( 'single-'. self::POST_TYPE .'.php' ) ) ) {
						$template_path = $theme_file;
					} else {
						$template_path = REY_CORE_DIR . 'inc/elementor/global-sections-single-template.php';
					}
				}
			}
			return $template_path;
		}

		/**
		 * Remove header & Footer in global sections post type
		 *
		 * @since 1.0.0
		 */
		public function remove_header_footer(){

			if ( get_post_type() == self::POST_TYPE ) {
				remove_all_actions( 'rey/header' );
				remove_all_actions( 'rey/footer' );
			}
		}

		/**
		 * Determine if edit mode
		 */
		private static function is_edit_mode(){

			if( is_user_logged_in() && class_exists('Elementor\Plugin') && is_callable( 'Elementor\Plugin::instance' ) ) {
				return \Elementor\Plugin::instance()->editor->is_edit_mode() || \Elementor\Plugin::instance()->preview->is_preview_mode();
			}
			return false;
		}

		/**
		 * Utility to differentiate arrays
		 * recursively
		 *
		 * @since 1.0.0
		 */
		private function array_diff_recursive($array1, $array2){
			$result = [];
			foreach($array1 as $key => $val) {
				if(array_key_exists($key, $array2)){
					if(is_array($val) || is_array($array2[$key])) {
						if (false === is_array($val) || false === is_array($array2[$key])) {
							$result[$key] = $val;
						} else {
							$result[$key] = self::array_diff_recursive($val, $array2[$key]);
							if (sizeof($result[$key]) === 0) {
								unset($result[$key]);
							}
						}
					}
				} else {
					$result[$key] = $val;
				}
			}
			return $result;
		}

		/**
		 * Load Sections custom CSS to prevent FOUC
		 *
		 * @since 1.0.0
		 */
		public static function load_sections_css() {

			$sections_css = apply_filters('reycore/global_sections/css', self::$sections_css);

			if( !empty( $sections_css ) ) {

				reyCoreAssets()->add_styles('reycore-elementor-frontend');

				$css_file = '';

				foreach( $sections_css as $section_id ){

					if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
						$css_file = new \Elementor\Core\Files\CSS\Post( $section_id );
					} elseif ( class_exists( '\Elementor\Post_CSS_File' ) ) {
						$css_file = new \Elementor\Post_CSS_File( $section_id );
					}

					if( !empty($css_file) ){
						$css_file->enqueue();
					}
				}
			}
		}

		/**
		 *
		 * Add Global section text notices to describe.
		 *
		 * @since 1.0.0
		 */
		public function add_gs_notices(){

			if( function_exists('rey__wp_kses') ){

				if( get_post_type() !== self::POST_TYPE ){
					return;
				}

				echo '<div class="rey-pbTemplate--gs-notice elementor-edit-area">';

					$gs_types = self::get_global_section_types();
					$gs_type = reycore__acf_get_field('gs_type', get_the_ID(), 'generic');
					$gs = isset($gs_types[$gs_type]) ? $gs_types[$gs_type] : $gs_type;

					printf( '<p>You\'re editing a <strong>%s</strong> Global Section. You can change its type by accessing <span class="rey-openPageSettings">Page Settings <i class="eicon-cog" aria-hidden="true"></i></span> (bottom left corner), or go to <a href="%s" target="_blank">Global Sections</a> .</p>',
						ucfirst($gs),
						admin_url('edit.php?post_type=rey-global-sections')
					);

				echo '</div>';
			}

		}

		/**
		 * Add Duplicate Post support for Global sections.
		 *
		 * @since 1.0.6
		 */
		function add_duplicate_post_support(){

			if(
				defined('DUPLICATE_POST_CURRENT_VERSION') &&
				$duplicate_post_types_enabled = get_option('duplicate_post_types_enabled', ['post', 'page']) )
			{
				if( ! in_array(self::POST_TYPE, $duplicate_post_types_enabled) ){
					$duplicate_post_types_enabled[] = self::POST_TYPE;
					update_option('duplicate_post_types_enabled', $duplicate_post_types_enabled);
				}
			}
		}

		function body_classes($classes){

			if( get_post_type() === self::POST_TYPE ):
				$gs_type = reycore__acf_get_field('gs_type', get_the_ID(), 'generic');
				$classes[] = 'gs-' . $gs_type;
			endif;

			return $classes;
		}

		function disable_stuff( $status ){

			if( get_post_type() === self::POST_TYPE ){
				return false;
			}

			return $status;
		}

		/**
		 * Add an attribute to html tag
		 *
		 * @since 1.0.0
		 **/
		function filter_html_tag()
		{
			add_filter('language_attributes', function($output){

				if( get_post_type() === self::POST_TYPE ){
					return $output . sprintf(' data-gs="%s"', reycore__acf_get_field('gs_type', get_the_ID(), 'generic'));
				}

				return $output;
			} );
		}

		function set_type_when_empty() {

			if ( ! check_ajax_referer( 'reycore-ajax-verification', 'security', false ) ) {
				wp_send_json_error( esc_html__('Invalid security nonce!', 'rey-core') );
			}

			if( !isset($_POST['type']) ){
				wp_send_json_error( esc_html__('No global section type set!', 'rey-core') );
			}

			if( !isset($_POST['post_id']) ){
				wp_send_json_error( esc_html__('No post ID set!', 'rey-core') );
			}

			if( ($type = reycore__clean($_POST['type'])) && ($post_id = absint($_POST['post_id'])) ){
				if( update_field('gs_type', $type, $post_id) ){
					wp_send_json_success();
				}
			}
		}

		function clear_gs_transient($post_id){
			if( get_post_type($post_id) === self::POST_TYPE ){
				delete_transient(self::GS_TRANSIENT);
			}
		}

		function notice_gs_body_style_options( $page ){

			if(
				($page_id = $page->get_id()) && $page_id != "" && ($post_type = get_post_type( $page_id )) &&
				($post_type === self::POST_TYPE || $post_type === 'revision')
			) {
				$page->add_control(
					'gs_nobody_notice',
					[
						'type' => \Elementor\Controls_Manager::RAW_HTML,
						'raw' => esc_html__( 'These styles won\'t be applied to the global section. Please don\'t use.', 'rey-core' ),
						'content_classes' => 'rey-raw-html',
					]
				);
			}
		}

		function filter_edit_with_elementor($settings){

			if( !empty($settings['elementor_edit_page']['children']) ){
				foreach ($settings['elementor_edit_page']['children'] as $id => $value) {
					if( self::POST_TYPE === get_post_type($id) ){

						$types = self::get_global_section_types();
						$title = esc_html__('Global Section', 'rey-core');

						if( ($type = reycore__acf_get_field('gs_type', $id)) && isset($types[$type]) ){
							$title = $types[$type];
						}

						$settings['elementor_edit_page']['children'][$id]['sub_title'] = $title;
					}
				}
			}

			return $settings;
		}

		/**
		 * Exclude GS from autocomplete feature for Elementor URL control.
		 * @since 1.9.1
		 */
		public function filter_wp_link_query_args( $query ) {

			$gs_cpt_key = array_search( self::POST_TYPE, $query['post_type'], true );

			if ( false !== $gs_cpt_key ) {
				unset( $query['post_type'][ $gs_cpt_key ] );
			}

			return $query;
		}

		public function add_post_states( $post_states, $post ) {

			if ( ! is_admin() ) {
				return $post_states;
			}

			global $current_screen;

			if( ! isset($current_screen->post_type) ){
				return;
			}

			if ( $current_screen->post_type !== self::POST_TYPE ) {
				return $post_states;
			}

			if( ($gs_header = get_theme_mod( 'header_layout_type', '' )) &&
				$gs_header != 'default' && $gs_header != 'none' && $post->ID === absint($gs_header) ) {
				$post_states['rey_active_header'] = __( 'Active Site Header', 'rey-core' );
			}

			else if( ($gs_footer = get_theme_mod( 'footer_layout_type', '' )) &&
				$gs_footer != 'default' && $gs_footer != 'none' && $post->ID === absint($gs_footer) ) {
				$post_states['rey_active_footer'] = __( 'Active Site Footer', 'rey-core' );
			}

			return $post_states;
		}

		public function post_row_actions( $actions, \WP_Post $post ) {

			global $current_screen;

			if ( ! $current_screen ) {
				return $actions;
			}

			if( ! ('edit' === $current_screen->base && self::POST_TYPE === $current_screen->post_type) ){
				return $actions;
			}

			$link = add_query_arg([
					'action'         => 'elementor_library_direct_actions',
					'library_action' => 'export_template',
					'source'         => 'local',
					'_nonce'         => wp_create_nonce( 'elementor_ajax' ),
					'template_id'    => $post->ID,
				],
				admin_url( 'admin-ajax.php' )
			);

			$actions['export-template'] = sprintf( '<a href="%1$s">%2$s</a>', $link, __( 'Export Global Section', 'rey-core' ) );

			return $actions;
		}

		/**
		 * Retrieve the reference to the instance of this class
		 * @return ReyCore_GlobalSections
		 */
		public static function getInstance()
		{
			if ( is_null( self::$_instance ) || ! ( self::$_instance instanceof self ) ) {
				self::$_instance = new self;
			}
			return self::$_instance;
		}
	}

	ReyCore_GlobalSections::getInstance();
endif;
