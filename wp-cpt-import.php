<?php
/*
Plugin Name: WP CPT Import
Plugin URI: https://github.com/msibtain/wp-cpt-import
Description: WP CPT Import
Author: msibtain
Version: 1.0.0
Author URI: https://github.com/msibtain/wp-cpt-import
*/

class WpCptImport
{

    function __construct() {
        add_action('template_redirect', [$this, 'func_es_template_redirect']);
    }

    function func_es_template_redirect() {
        global $post;
        if ($post->post_type === "trip" && @$_GET['sib'] === "test")
        {
            $pm = get_post_meta($post->ID);
            echo "<code>";
            p_r($pm);
            echo "</code>";
        }
    }
}

new WpCptImport();

if (!function_exists('p_r')){function p_r($s){echo "<pre>";print_r($s);echo "</pre>";}}
if (!function_exists('write_log')){ function write_log ( $log )  { if ( is_array( $log ) || is_object( $log ) ) { error_log( print_r( $log, true ) ); } else { error_log( $log ); }}}