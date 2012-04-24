<?php
/*
Plugin Name: Multiple Header Images
Description: Allows user to select multiple header images to associate with a post.
Author: Wired Media
Version: 0.1
Author URI: http://www.wiredmedia.co.uk
*/

namespace MultipleHeaderImages;

require_once dirname(__FILE__) . '/admin.php';
require_once dirname(__FILE__) . '/functions.php';

/* add meta boxes for selecting multiple header images
/*-----------------------------------------------------------------------------------*/
class Plugin {
  public function __construct() {}

  /**
	 * Retrieve Header Images.
	 *
	 * @param string $post_id Optional. Post ID.
	 */
  public function get_header_images($post_id = null, $size = null){
    if (is_404()) {
      $images = self::get_default_header_images();
    } else {
      if (!$post_id) {
        $post_id = get_the_ID();
      }

      $post_ids = array_merge(array($post_id), get_post_ancestors($post_id));

      while (!$images && $post_ids) {
        $images = self::get_header_images_post_meta(array_shift($post_ids));
      }
    }

    // if no header images attached to this post get the default header images
    if (empty($images)) {
      $images = self::get_default_header_images();
    }

    foreach($images as $key => $url){
      // if size is specified return the specific size
      if ($size){
        $url = str_replace( '.' . pathinfo($url, PATHINFO_EXTENSION), '-' . $size . '.' . pathinfo($url, PATHINFO_EXTENSION), $url );
        /*
         * sizes may not exist if original images are smaller then the desired image wordpress wont create that image size (won't scale up)
         * fall back to original size
         */
        /*
        $domain = $_SERVER['SERVER_NAME'];
        $custom_size_img = str_replace( $domain, ' ', $custom_size_img );
        echo( $custom_size_img  );
        if( file_exists( $custom_size_img ) ){
          $url = $custom_size_img;
        }
        */
      }

    	$images[$key] = esc_url_raw($url);
    }

		return $images;
  }

  public function get_default_header_images() {
    $images = json_decode(get_option('default-header-images'));

    return $images ? $images : array(get_header_image()); // load default images or fallback to regular header image
  }

  /**
   * Check if post has header images attached.
   *
   * @param string $post_id Optional. Post ID.
   * @return bool Whether post has an image attached.
   */
  public static function has_header_images($post_id = null) {
    $post_id = $post_id ? $post_id : get_the_ID();

    return $post_id ? self::get_header_images_post_meta($post_id) : false;
  }

  /**
	 * Retrieve Header Images meta.
	 *
	 * @param string $post_id. Post ID.
	 */
  private function get_header_images_post_meta($post_id) {
    return json_decode(get_post_meta($post_id, 'multiple-header-images', true));
  }
}