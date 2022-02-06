<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if( class_exists('\Elementor\Skin_Base') && !class_exists('ReyCore_Text_Dynamic_Skin') ):
	/**
	 * Dynamic Text
	 *
	 * @since 1.0.0
	 */
	class ReyCore_Text_Dynamic_Skin extends \Elementor\Skin_Base {

		public function __construct( \Elementor\Widget_Base $parent ) {
			parent::__construct( $parent );
			add_filter( 'elementor/widget/print_template', array( $this, 'skin_print_template' ), 10, 2 );
		}

		public function get_id() {
			return 'dynamic_text';
		}

		public function get_title() {
			return __( 'Dynamic Text', 'rey-core' );
		}

		public function dynamic_text() {
			return '{{ ... }}';
		}

		protected function render_text() {

			$settings = $this->parent->get_settings_for_display();

			if( get_post_type() !== ReyCore_GlobalSections::POST_TYPE ){

				switch( $settings['rey_dynamic_source'] ):
					case 'excerpt':
						return wp_kses_post( get_the_excerpt() );
						break;

					case 'desc':
						return wp_kses_post( get_the_archive_description() );
						break;

					default:
						return wp_kses_post( reycore__get_page_title() );
				endswitch;
			}

			return $this->dynamic_text();
		}

		public function render() {

			$this->parent->add_render_attribute( 'editor', 'class', [ 'elementor-text-editor', 'elementor-clearfix' ] );
			?>
			<div <?php echo $this->parent->get_render_attribute_string( 'editor' ); ?>><?php echo $this->render_text(); ?></div>
			<?php
		}

		public function content_template() {
			?>
			<#
			if( 'dynamic_text' === settings._skin ){
				var text = '<?php echo $this->dynamic_text(); ?>';
			}
			else {
				var text = settings.editor;

				view.addInlineEditingAttributes( 'editor', 'advanced' );
			}

			view.addRenderAttribute( 'editor', 'class', [ 'elementor-text-editor', 'elementor-clearfix' ] );

			#>
			<div {{{ view.getRenderAttributeString( 'editor' ) }}}>{{{ text }}}</div>
			<?php
		}

		public function skin_print_template( $content, $heading ) {
			if( 'text-editor' == $heading->get_name() ) {
				ob_start();
				$this->content_template();
				$content = ob_get_clean();
			}
			return $content;
		}
	}
endif;
