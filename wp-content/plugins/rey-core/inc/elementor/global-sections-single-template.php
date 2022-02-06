<?php
get_header();

$classes = [
	'rey-pbTemplate--gs'
];

$type = reycore__acf_get_field('gs_type');

$classes[] = 'rey-pbTemplate--gs-' . $type ;

if( in_array($type, ['header', 'footer'], true) ){
	$classes[] = 'rey-pbTemplate--gs-hf';
} ?>

<div class="rey-pbTemplate  rey-pbTemplate--gs-<?php echo implode(' ', $classes) ?>">
    <div class="rey-pbTemplate-inner">
        <?php
        while ( have_posts() ) : the_post();
			reycore__get_template_part( 'template-parts/page/content' );
        endwhile;
        ?>
    </div>
</div>
<!-- .rey-pbTemplate -->


<?php

	do_action('reycore/global_section_template/after_content');


get_footer();
