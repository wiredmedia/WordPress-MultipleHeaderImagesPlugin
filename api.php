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

    echo '<img src="',$image->url,'"';

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

function get_header_images($post_id = null, $size = 'full') {
  return Plugin::get_header_images($post_id, $size);
}