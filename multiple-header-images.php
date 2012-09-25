<?php
/*
Plugin Name: Multiple Header Images
Description: Allows user to select multiple header images to associate with a post.
Author: Wired Media
Version: 0.2
Author URI: http://www.wiredmedia.co.uk
*/

namespace MultipleHeaderImages;

require_once dirname(__FILE__) . '/admin.php';
require_once dirname(__FILE__) . '/api.php';
require_once dirname(__FILE__) . '/upgrade.php';


/* add meta boxes for selecting multiple header images
/*-----------------------------------------------------------------------------------*/
class Plugin {

  public function __construct() {
    add_action( 'admin_init', array(&$this, 'check_wp_version'));
    add_action( 'admin_init', array(&$this, 'check_php_version'));
    add_action( 'init', array(&$this, 'upgrade_plugin'));
  }

  public function upgrade_plugin(){
    new Upgrade();
  }

  public function check_wp_version() {
    if ( version_compare(get_bloginfo('version'), "3.4", "<" ) ) {
      $plugin = plugin_basename( __FILE__ );
      if( is_plugin_active($plugin) ) {
        deactivate_plugins($plugin);
        add_action('admin_notices', function(){
          $plugin_data = get_plugin_data( __FILE__, false );
          echo '<div class="error">
            <p>Sorry <strong>'. $plugin_data['Name'] .'</strong> requires WordPress 3.4 or higher! please upgrade to the latest version of WordPress. The plugin was not activated.</p>
          </div>';
        });
      }
    }
  } // END: check_wp_version()

  public function check_php_version(){
    if( version_compare(PHP_VERSION, '5.3', '<') ) {
      $plugin = plugin_basename( __FILE__ );
      if( is_plugin_active($plugin) ) {
        deactivate_plugins($plugin);
        add_action('admin_notices', function(){
          $plugin_data = get_plugin_data( __FILE__, false );
          echo '<div class="error">
            <p>Sorry <strong>'. $plugin_data['Name'] .'</strong> requires PHP 5.2 or higher! your PHP version is '. PHP_VERSION .'. The plugin was not activated.</p>
          </div>';
        });
      }
    }
  } // END: check_php_version()

  /**
	 * Retrieve Header Images.
	 *
	 * @param string $post_id Optional. Post ID.
	 */
  public function get_header_images($post_id = null, $size = 'full'){

    if (is_404()) {
      $images = self::get_default_header_images();
    } else {
      if (!$post_id) {
        $post_id = get_the_ID();
      }

      $post_ids = array_merge(array($post_id), get_post_ancestors($post_id));
      $images = false;

      while (!$images && $post_ids) {
        $images = self::get_header_images_post_meta(array_shift($post_ids));
      }
    }

    // if no header images attached to this post get the default header images
    if (empty($images)) {
      $images = self::get_default_header_images();
    }

    $image_data = array();

    foreach($images as $image){
      $image_data[] = wp_get_attachment_image_src($image, $size);
    }

		return $image_data;
  }

  public function get_default_header_images() {
    $images = json_decode(get_option('default_header_images'));

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
    return json_decode(get_post_meta($post_id, '_header_images', true));
  }
}

new Plugin;