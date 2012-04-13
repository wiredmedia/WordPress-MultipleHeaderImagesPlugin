<?php

use MultipleHeaderImages\Plugin;
use MultipleHeaderImages\Admin;

function header_images($options = array()) {
  list($post_id, $size, $class, $width, $height) = array(null, null, false, false, false); // defaults options
  extract($options); // user-defined options

  echo '<ul';

  if (isset($class)) {
    echo sprintf(' class="%s"', $class);
  }

  echo '>';

  foreach (get_header_images($post_id, $size) as $image) {
    echo '<li>';

    echo '<img src="',$image,'"';

    if ($width) {
      echo sprintf(' width="%s"', $width);
    }

    if ($height) {
      echo sprintf(' height="%s"', $height);
    }

    echo ' alt="" />';

    echo '</li>';
  }

  echo '</ul>';
}

function get_header_images($post_id = null, $size = null) {
  return Plugin::get_header_images($post_id, $size);
}

/* bind admin ajax functions */

function multiple_header_images() {
  return new Plugin;
}
add_action('load-post.php', 'multiple_header_images' );
add_action('load-post-new.php', 'multiple_header_images' );

function multiple_header_images_list($data) {
  return Admin::ajax_get($data);
}
add_action('wp_ajax_list_header_images', 'multiple_header_images_list');

function multiple_header_images_save($data) {
  return Admin::ajax_save($data);
}
add_action('wp_ajax_save_header_images', 'multiple_header_images_save');