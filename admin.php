<?php

namespace MultipleHeaderImages;

class Admin extends Base{
  public $page_title = 'Custom Multiple Header Images';
  public $menu_title = 'Headers';

  public function __construct() {
    add_action('admin_menu', array(&$this, 'init'));

    add_action('add_meta_boxes', array(&$this, 'add_meta_box')); // add the meta box
    add_action('admin_enqueue_scripts', array(&$this, 'the_javascript')); // register js
    add_action('admin_enqueue_scripts', array(&$this, 'the_css'));
  }

  public function init() {
    add_submenu_page('themes.php', $this->page_title, $this->menu_title, 'upload_files', 'custom-headers', array(&$this, 'render'));
  }

  /* meta box register */
  public function add_meta_box() {
    $types = apply_filters( 'multiple_header_images_post_types', array( 'post', 'page' ) );
    foreach($types as $type) {
      add_meta_box(
        'header_images'
        ,__( 'Select Header Images', 'wiredpress' )
        ,array( &$this, 'form' )
        ,$type
        ,'side'
        ,'core'
      );
    }
  }// END: add_meta_box()

  public function render() {
    echo '
      <div class="wrap">
        <div id="icon-themes" class="icon32"><br></div>
        <h2>',$this->page_title,'</h2>

        <p>Specify default header images to fallback to if not defined per-post.</p>

        <p>Should none be selected here, we\'ll fallback to the regular header image.</p>

    ';

    $this->form();

    echo '
      </div>
    ';
  }

  public function form() {
    global $post;
    $post_id = (isset($post->ID)) ? $post->ID : '' ;
    echo '
      <div id="multiple_header_images_container">
        <div id="multiple-header-images" data-post-id="'.$post_id.'">

          <div class="mhi-selected">
            <h4 class="media-title">Selected images</h3>
            <ul id="multiple-header-images-selected" class="mhi-list">

            </ul>
          </div>

          <div class="mhi-available">
            <h4 class="media-title">Available images</h3>
            <ul id="multiple-header-images-available" class="mhi-list">

            </ul>
          </div>

          <div class="mhi-actions">
            <a href="#" id="mhi-save-images">Save Changes<span class="feedback"></span></a>
          </div>

        </div>
      </div>
    ';
  }

  /* ajax response for header images */
  public static function get($data) {
    global $wpdb;

    $available_imgs = get_uploaded_header_images();

    if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
      $selected_imgs = json_decode(get_post_meta($_POST['post_id'], '_header_images', true));
    } else {
      $selected_imgs = json_decode(get_option('default_header_images'));
    }

    $available = array();
    $selected = array();

    if (is_array($available_imgs)) {
      foreach( $available_imgs as $header => $attrs ) {
        // strip absolute url
        $img_url = parse_url($attrs['url']);

        if (isset($img_url['path'])) {
          $attrs['url'] = $img_url['path'];
        }

        if (!is_array($selected_imgs) || !in_array($attrs['url'], $selected_imgs)) {
          // not in selected images
          array_push($available, $attrs['url']);
        } else {
          // image is selected
          array_push($selected, $attrs['url']);
        }
      }
    }

    return (object) array(
      'available' => $available,
      'selected' => $selected
    );
  }

  /* save images to db */
  public function save($data) {
    global $wpdb;

    $images = isset($_POST['images']) ? $_POST['images'] : false;

    if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
      // per-post
      if ($images) {
        update_post_meta($_POST['post_id'], '_header_images', json_encode($images));
      } else {
        delete_post_meta($_POST['post_id'], '_header_images');
      }
    } else {
      // default
      if ($images) {
        update_option('default_header_images', json_encode($images));
      } else {
        delete_option('default_header_images');
      }

    }
  }

  public static function ajax_get($data) {
    echo json_encode(self::get($data));
    exit;
  }

  public function ajax_save($data) {
    self::save($data);
    exit;
  }

  /* load the styles */
  public function the_css() {
    wp_register_style('multiple-header-images', plugins_url('css/style.css', $this->plugin_file()));
    wp_enqueue_style('multiple-header-images');
  }

  /* load js in footer */
  public function the_javascript() {
    wp_register_script('multiple-header-images', plugins_url('js/main.js', $this->plugin_file()), array('jquery'), '', true);
    wp_enqueue_script('multiple-header-images');
  }
}

new Admin;