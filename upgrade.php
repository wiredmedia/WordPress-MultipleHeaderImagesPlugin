<?php
namespace MultipleHeaderImages;

/*
 * used for managing upgrade process between versions
 */
class Upgrade{

    var $user_version;
    var $plugin_version = 1.5;

    public function __construct(){
        //delete_option('wm_multiple_headers_ver');

        $this->user_version = get_option('wm_multiple_headers_ver');

        if(!$this->user_version){
            $this->user_version = 0;
        }

        /*
         * run all available upgrade functions
         */
        if($this->user_version < $this->plugin_version){
            while($this->user_version < $this->plugin_version){
                $this->user_version = $this->user_version + .1;
                $function = 'version_' . str_replace('.', '', $this->user_version);

                if(method_exists($this, $function)){
                    call_user_func('MultipleHeaderImages\Upgrade::' . $function);
                }
            }

            //update_option('wm_multiple_headers_ver', $this->user_version); // update user version
        }

    } // END: construct

    /*
     * Prior to wordpress 3.4 the get_uploaded_header_images() function only returned the url
     * of the header images. Now it returns more data including the attachment id
     * as of this version the plugin now uses the new data.
     */
    static private function version_01(){
        global $wpdb;

        $uploaded_headers = get_uploaded_header_images();

        /*
         * populate default headers images with the new available data
         */
        $default_headers = json_decode(get_option('default_header_images'));
        $new_headers = array();

        foreach($default_headers as $header_url){
            foreach($uploaded_headers as $uploaded_header){
                $uploaded_header_url = str_replace(get_home_url(), '', $uploaded_header['url']);

                if($uploaded_header_url == $header_url){
                    $new_headers[] = $uploaded_header['attachment_id'];
                    break;
                }
            }
        }

        if(!empty($new_headers)){
            update_option('default_header_images', json_encode($new_headers));
        }


        /*
         * populate chosen header images with the new available data
         */
        $header_meta = $wpdb->get_results($wpdb->prepare(
            "
              SELECT * FROM $wpdb->postmeta
              where meta_key = '_header_images'
            "
         ));

        foreach($header_meta as $meta){

            $chosen_headers = json_decode($meta->meta_value);
            $new_headers = array();

            foreach($chosen_headers as $header_url){
                foreach($uploaded_headers as $uploaded_header){
                    $uploaded_header_url = str_replace(get_home_url(), '', $uploaded_header['url']);

                    if($uploaded_header_url == $header_url){
                        $new_headers[] = $uploaded_header['attachment_id'];
                        break;
                    }
                }
            }

            if(!empty($new_headers)){
                update_post_meta($meta->post_id, $meta->meta_key, json_encode($new_headers));
            }

        }


    }


}