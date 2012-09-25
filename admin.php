<?php

namespace MultipleHeaderImages;

class Admin {

  public $page_title = 'Custom Multiple Header Images';
  public $menu_title = 'Headers';

  public function __construct() {
    add_action('admin_menu', array(&$this, 'init'));
    add_action('add_meta_boxes', array(&$this, 'add_meta_box')); // add the meta box
    add_action('admin_enqueue_scripts', array(&$this, 'the_javascript')); // register js
    add_action('admin_enqueue_scripts', array(&$this, 'the_css'));

    /* bind admin ajax functions */
    add_action('wp_ajax_list_header_images', array(&$this, 'ajax_get'));
    add_action('wp_ajax_save_header_images', array(&$this, 'ajax_save'));

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

        <p>Specify multiple default header images to fallback to if not defined per-post.</p>

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
  public function get($data) {
    global $wpdb;

    $available = get_uploaded_header_images();

    if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
      $selected_headers = json_decode(get_post_meta($_POST['post_id'], '_header_images', true), true);
    } else {
      $selected_headers = json_decode(get_option('default_header_images'), true);
    }

    $selected = array();

    if (is_array($selected_headers)) {

      /*
       * if a header in available headers matches a seleted header take it out of the the available array
       * also retreives the thumbnail and adds that to the data
       */
      foreach($available as $index => $available_header){

        $thumb = wp_get_attachment_image_src($available_header['attachment_id'], 'thumbnail');
        $available[$index]['thumb'] = $thumb[0];
        foreach($selected_headers as $selected_header){
          if($available_header['attachment_id'] == $selected_header){
            // remove from available array
            $selected[] = array('attachment_id' => $available[$index]['attachment_id'], 'thumb' => $available[$index]['thumb']);
            unset($available_header[$index]);
          }
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

  public function ajax_get($data) {
    echo json_encode(self::get($data));
    exit;
  }

  public function ajax_save($data) {
    self::save($data);
    exit;
  }

  /* load the styles */
  public function the_css() {
    wp_register_style('multiple-header-images', plugins_url('css/style.css', __FILE__));
    wp_enqueue_style('multiple-header-images');
  }

  /* load js in footer */
  public function the_javascript() {
    wp_register_script('multiple-header-images', plugins_url('js/main.js', __FILE__), array('jquery'), '', true);
    wp_enqueue_script('multiple-header-images');
  }
}

new Admin;