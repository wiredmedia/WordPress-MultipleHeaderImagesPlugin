<?php
/**
 * @package Multiple Header Images
 * @version 1.0
 */
/*
Plugin Name: Multiple Header Images
Plugin URI: http://wordpress.org/extend/plugins/
Description: Allows user to select multiple header images to associate with a post
Author: Wiredmedia (Carl Hughes)
Version: 1.0
Author URI: http://wiredmedia.co.uk
*/

/* add meta boxes for selecting multiple header images
/*-----------------------------------------------------------------------------------*/
Class Multiple_Header_Images{

  public function __construct() {
    add_action( 'add_meta_boxes', array( &$this, 'add_meta_box' ) ); // add the meta box
    add_action( 'admin_enqueue_scripts', array(&$this, 'the_javascript') ); // register js
    add_action( 'admin_enqueue_scripts', array(&$this, 'the_css') );
  }

  /* meta box register */
  public function add_meta_box(){
    $types = array( 'post', 'page', 'offer_page', 'room_page', 'menu' );
    foreach( $types as $type ) {
      add_meta_box(
        'header_images'
        ,__( 'Select Header Images', 'wiredpress' )
        ,array( &$this, 'render_meta_box_content' )
        ,$type
        ,'side'
        ,'core'
      );
    }
  }// END: add_meta_box()

  /* meta box renderer */
  public function render_meta_box_content(){ ?>
    <?php
    global $post;
    ?>
    <div id="multiple_header_images_container" style="display:none">
      <div id="multiple-header-images" data-postid="<?php echo $post->ID ?>">

        <div class="mhi-available">
          <h3 class="media-title">Available images</h3>
          <ul id="multiple-header-images-available" class="mhi-list"></ul>
        </div>

        <div class="mhi-selected">
          <h4 class="media-title">Selected images</h4>
          <ul id="multiple-header-images-selected" class="mhi-list"></ul>
        </div>

        <div class="mhi-actions">
          <a href="#" id="mhi-save-images">Update<span class="feedback"></span></a>
        </div>

      </div><!-- #multiple-header-images -->
    </div>
    <a id="multiple-header-btn" class="thickbox" title="Select header images" href="#TB_inline?height=482&width=640&inlineId=multiple_header_images_container">Select header images</a>
    <?php
  }

  /* ajax response for header images */
  public function get_header_images( $data ){
    global $wpdb;
    $post_id = intval( $_POST['postid'] );

    $available_imgs = get_uploaded_header_images();
    $selected_imgs = json_decode(get_post_meta( $post_id, 'multiple-header-images', true));

    $collection = array();
    $collection['selected'] = array();
    $collection['available'] = array();

    if( is_array($available_imgs) ){
      foreach( $available_imgs as $header => $attrs ):
        $attrs['url'] = str_replace(get_bloginfo('url'), '', $attrs['url']);

        if( is_array($selected_imgs) ){
          if( !in_array($attrs['url'], $selected_imgs ) ) {
            // not in selected images
            $collection_type = 'available';
            $checked = '';
          }else{
            // image is selected
            $collection_type = 'selected';
            $checked = 'checked="checked"';
          }
        }else{
          //currently no selected images
          $collection_type = 'available';
          $checked = '';
        }
        $collection[$collection_type][] = '<li class="mhi-image"><img src="'. $attrs['url'] .'" width="100" /><input type="checkbox" class="mhi-checkbox" value="'. $attrs['url']  .'" name="header-image" '. $checked .'/></li>';
      endforeach;
    }

    echo json_encode( $collection );
    exit;
  }

  /* save images to db */
  public function save_images( $data ){
    global $wpdb;
    $post_id = intval( $_POST['postid'] );
    $images = ( isset($_POST['images']) ) ? $_POST['images'] : null;

    if( $images ){
      update_post_meta($post_id, 'multiple-header-images', json_encode($images));
    } else {
      delete_post_meta($post_id, 'multiple-header-images');
    }

    exit;
  }

  /* load js in footer */
  public function the_javascript(){
    wp_register_script( 'multiple-header-images', WP_PLUGIN_URL . '/multiple-header-images/functions.js', array('jquery'), '', true );
    wp_enqueue_script( 'multiple-header-images' );
  }

  /* load the styles */
  public function the_css(){
    $myStyleUrl = plugins_url('styles.css', __FILE__); // Respects SSL, Style.css is relative to the current file
    $myStyleFile = WP_PLUGIN_DIR . '/multiple-header-images/styles.css';
    if ( file_exists($myStyleFile) ) {
      wp_register_style('multiple-header-images', $myStyleUrl);
      wp_enqueue_style( 'multiple-header-images');
    }
  }

  /**
	 * Check if post has header images attached.
	 *
	 * @param string $post_id Optional. Post ID.
	 * @return bool Whether post has an image attached.
	 */
	public static function has_header_images($post_id = null) {

		$post_id = (null === $post_id) ? get_the_ID() : $post_id;

		if (!$post_id) {
			return false;
		}

		return self::get_the_header_images_meta( $post_id );
	}

  /**
	 * Display Header Images.
	 *
	 * @param string $post_id Optional. Post ID.
	 */
	public static function the_header_images( $width ='', $height = '', $post_id = null ) {
		$header_images = self::get_the_header_images( $post_id );
		$html = '';
	  foreach( $header_images as $url ){
	    $html .= '<img src="'. $url .'" width="'. $width .'" height="'. $height .'" alt="" />';
	  }
	  echo $html;
	}

  public function default_header_image(){
    return array( get_header_image() );
  }
  /**
	 * Retrieve Header Images.
	 *
	 * @param string $post_id Optional. Post ID.
	 */
  public function get_the_header_images( $post_id = null, $size = null ){
    if( is_404() ){
      $header_images = self::default_header_image();
    }else{
      $post_id = (null === $post_id) ? get_the_ID() : $post_id;
      $header_images = self::get_the_header_images_meta( $post_id );
    }

    // if no header images attached to this post get the default header image
    if( empty($header_images) ){
      $header_images = self::default_header_image();
    }

    $filtered_images = array();
    $html = '';

    foreach( $header_images as $url ){
      // if size is specified return the specific size
      if( $size ){
        $path_parts = pathinfo( $url );
        $custom_size_img = str_replace( '.' . $path_parts['extension'], '-' . $size . '.' . $path_parts['extension'], $url );
        $url = $custom_size_img;
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

    	array_push($filtered_images, esc_url_raw( $url ) );
    }
		return $filtered_images;
  }

  /**
	 * Retrieve Header Images meta.
	 *
	 * @param string $post_id. Post ID.
	 */
  private function get_the_header_images_meta( $post_id ){
    return json_decode(get_post_meta( $post_id, 'multiple-header-images', true));
  }

} // END: Multiple_Header_Images

function get_header_images() {
  return Multiple_Header_Images::get_the_header_images();
}

function header_images($options = array()) {
  echo '<ul';

  if (array_key_exists('class', $options)) {
    echo sprintf(' class="%s"', $options['class']);
  }

  echo '>';

  foreach (get_header_images() as $image) {
    echo '<li><img src="',$image,'" /></li>';
  }

  echo '</ul>';
}

function multiple_header_images(){
  return new Multiple_Header_Images;
}
add_action( 'load-post.php', 'multiple_header_images' );
add_action( 'load-post-new.php', 'multiple_header_images' );

function multiple_header_images_list($data){
  return Multiple_Header_Images::get_header_images($data);
}
add_action('wp_ajax_list_header_images', 'multiple_header_images_list');

function multiple_header_images_save($data){
  return Multiple_Header_Images::save_images($data);
}
add_action('wp_ajax_save_header_images', 'multiple_header_images_save');
