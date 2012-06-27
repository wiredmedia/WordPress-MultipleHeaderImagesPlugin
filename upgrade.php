<?php
namespace MultipleHeaderImages;

/*
 * used for managing upgrade process between versions
 */
class Upgrade {

  var $user_version;
  public function __construct($plugin_version) {

    $user_version = get_option('wm_multiple_headers');

    if(!$user_version){
      /*
       * user may have installed the non versioned plugin
       * check for the old data pre 3.4 and update it
       */
        $this->non_versioned();
    }

    /*
     * later on we can use $plugin_version to check against the user_version
     * but right now were only interested in catchng any non versioned plugins
     */

  }

  public function non_versioned(){
    /*
     * rename default headers images and populate it with the correct data
     */
    //$default_headers = get_option('default_header_images');
    //delete_option('default_header_images');

  }


}