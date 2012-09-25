<?php
//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ){
    exit ();
}

// delete option
delete_option('default_header_images');
delete_option('wm_multiple_headers')

/*
 * delete individual post meta
 */
$posts_with_meta = new WP_Query(array(
    'post_status' => 'any',
    'posts_per_page' => -1,
    'meta_key' => '_header_images'
));
while ( $posts_with_meta->have_posts() ) : $posts_with_meta->the_post();
    delete_post_meta(get_the_ID(), '_header_images');
endwhile; wp_reset_postdata();