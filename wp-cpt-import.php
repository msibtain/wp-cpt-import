<?php
/*
Plugin Name: WP CPT Import
Plugin URI: https://github.com/msibtain/wp-cpt-import
Description: WP CPT Import
Author: msibtain
Version: 1.2.0
Author URI: https://github.com/msibtain/wp-cpt-import
*/

include( __DIR__ . '/wp-cpt-export.php' );

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
            p_r($pm);
            
            $wp_travel_engine_setting = get_post_meta($post->ID, "wp_travel_engine_setting", true);
            p_r($wp_travel_engine_setting);

            $wpte_gallery_id = get_post_meta( $post->ID, "wpte_gallery_id", true);
            p_r($wpte_gallery_id);

        }


        if (
            @$_GET['action'] === "ilab_import"
        )
        {
            
            
            $cpt = "excursions_posts";
            $ppp = $_GET['ppp'];
            $pn = $_GET['pn'];

            $response = wp_remote_get( "https://ftstravels.com/api/v1/cptexport?cpt={$cpt}&ppp={$ppp}&pn={$pn}" );
            $body = json_decode( $response['body'] );
            $posts = $body->data;

            foreach ($posts as $objPost)
            {
                $source_post_id = $objPost->ID;

                $arrPost = (array)$objPost->post;

                unset($arrPost['ID']);
                unset($arrPost['guid']);
                unset($arrPost['post_author']);

                $arrPost['post_type'] = "trip";

                $post_id = wp_insert_post( $arrPost );

                if ( is_wp_error( $post_id ) ) 
                {
                    $error_string = $post_id->get_error_message();
                    echo '<div id="message" class="error"><p>source post ID: ' . $source_post_id . ' - ' . $error_string . '</p></div>';
                    exit;
                }

                echo $post_id . ' new post created<br>';

                
                
                $objPostMeta = $objPost->meta;

                //p_r($objPostMeta);

                $arrIncludes = [];
                $cost_includes = "";
                for ($ciLoop = 0; $ciLoop <= 50; $ciLoop++)
                {
                    $variable = "includes_{$ciLoop}_include_point";
                    if (isset($objPostMeta->$variable))
                    {
                        $arrIncludes[] = $objPostMeta->$variable[0];
                    }
                    $cost_includes = implode("\n", $arrIncludes);
                }

                $arrExcludes = [];
                $cost_excludes = "";
                for ($ciLoop = 0; $ciLoop <= 50; $ciLoop++)
                {
                    $variable = "not_includes_{$ciLoop}_not_included_point";
                    if (isset($objPostMeta->$variable))
                    {
                        $arrExcludes[] = $objPostMeta->$variable[0];
                    }
                    $cost_excludes = implode("\n", $arrExcludes);
                }
                
                if (isset($objPostMeta->description_hours[0]))
                    $trip_duration                          = $objPostMeta->description_hours[0];
                else
                    $trip_duration                          = $objPostMeta->description_duration_in_days[0];
                $trip_duration_unit                     = $objPostMeta->description_select_days_or_hours[0];
                $overview_section_title                 = "Overview";
                $tab_content['1_wpeditor']              = $objPostMeta->excursion_highlight[0];
                //$trip_highlights_title                = "Highlights";
                $trip_itinerary_title                   = "Itinerary";
                $itinerary['itinerary_title']['1']      = $objPostMeta->excursion_times_itinerary_0_title[0];
                $itinerary['itinerary_content']['1']    = $objPostMeta->excursion_times_itinerary_0_description[0];
                $cost_tab_sec_title                     = "Includes/Excludes";
                $cost['includes_title']                 = "The Cost Includes";
                $cost['cost_includes']                  = $cost_includes;
                $cost['excludes_title']                 = "The Cost Excludes";
                $cost['cost_excludes']                  = $cost_excludes;
                $faq_section_title                      = "FAQs";

                $trip_facts = [];
                $trip_facts['field_id'] = [
                    '12647846' => 'Duration',
                    '12660073' => 'Type',
                    '28890066' => 'Live tour guide',
                    '31652972' => 'Starting point',
                    '33257212' => 'Category',
                    '35550118' => 'Pick-up Time',
                    '69738162' => 'Pickup included'
                ];
                $trip_facts['field_type'] = [
                    '12647846' => 'text',
                    '12660073' => 'text',
                    '28890066' => 'text',
                    '31652972' => 'text',
                    '33257212' => 'text',
                    '35550118' => 'text',
                    '69738162' => 'text'
                ];
                $trip_facts['12647846']['12647846'] = $trip_duration . ' ' . $trip_duration_unit;
                $trip_facts['12660073']['12660073'] = @implode(", ", unserialize($objPostMeta->description_type[0]) );
                $trip_facts['28890066']['28890066'] = @implode(", ", unserialize($objPostMeta->description_live_tour_guide_type[0]) );
                $trip_facts['31652972']['31652972'] = @implode(", ", unserialize($objPostMeta->description_starting_point[0]) );
                $trip_facts['33257212']['33257212'] = @implode(", ", unserialize($objPostMeta->description_category[0]) );
                $trip_facts['35550118']['35550118'] = $objPostMeta->description_pickup_time[0];
                $trip_facts['69738162']['69738162'] = $objPostMeta->description_pickup_included[0];

                $map_section_title                          = "Map";
                $map['image_url']                           = "";
                $map['iframe']                              = "";



                $wp_travel_engine_setting = [
                    'trip_duration'             => $trip_duration,
                    'trip_duration_unit'        => strtolower($trip_duration_unit),
                    'overview_section_title'    => $overview_section_title,
                    'tab_content'               => $tab_content,
                    'trip_itinerary_title'      => $trip_itinerary_title,
                    'itinerary'                 => $itinerary,
                    'cost_tab_sec_title'        => $cost_tab_sec_title,
                    'cost'                      => $cost,
                    'faq_section_title'         => $faq_section_title,
                    'trip_facts'                => $trip_facts,
                    'map_section_title'         => $map_section_title,
                    'map'                       => $map,


                ];

                update_post_meta($post_id, "wp_travel_engine_setting", $wp_travel_engine_setting);

                //p_r($wp_travel_engine_setting);

                # Gallery stuff;
                $objPostGallery = $objPost->gallery;
                if ($objPostGallery)
                {
                    $wpte_gallery_id['enable']          = "1";
                    $gIndex = 21149549;
                    foreach ($objPostGallery as $image)
                    {
                        $wpte_gallery_id[$gIndex] = $this->z_upload_file_by_url( $image, $gIndex );
                        $gIndex++;
                    }

                    update_post_meta($post_id, "wpte_gallery_id", $wpte_gallery_id);

                    //p_r($wpte_gallery_id);
                }

                # Price stuff;
                update_post_meta($post_id, "adult_price", $objPostMeta->excursion_options_0_group_prices_adults[0]);
                update_post_meta($post_id, "adult_price_sale", $objPostMeta->excursion_options_0_group_prices_adult_sale[0]);

                update_post_meta($post_id, "child_price", $objPostMeta->excursion_options_0_group_prices_child[0]);
                update_post_meta($post_id, "child_price_sale", $objPostMeta->excursion_options_0_group_prices_child_sale[0]);

                update_post_meta($post_id, "student_price", $objPostMeta->excursion_options_0_group_prices_student_id[0]);
                update_post_meta($post_id, "student_price_sale", $objPostMeta->excursion_options_0_group_prices_student_id_sale[0]);

                update_post_meta($post_id, "infant_price", $objPostMeta->excursion_options_0_group_prices_infants[0]);
                update_post_meta($post_id, "infant_price_sale", $objPostMeta->excursion_options_0_group_prices_infants_sale[0]);

                $arrPrices = $objPost->prices;

                if ($arrPrices)
                {
                    foreach ($arrPrices as $index => $objPrice)
                    {
                        update_row('prices', ($index+1), [
                            'option_title' => $objPrice->option_title,
                            'option_description' => $objPrice->option_description,
                            'from' => $objPrice->from,
                            'to' => $objPrice->to,
                            'adult' => $objPrice->adult,
                            'adult_sale' => $objPrice->adult_sale,
                            'child' => $objPrice->child,
                            'child_sale' => $objPrice->child_sale,
                            'student_id' => $objPrice->student_id,
                            'student_id_sale' => $objPrice->student_id_sale,
                            'infants' => $objPrice->infants,
                            'infants_sale' => $objPrice->infants_sale,
                        ], $post_id);
                    }
                }
            }

            # update page number;
            update_option($cpt . "_pn", $pn);

            exit;

        }

    }

    function z_upload_file_by_url( $image_url, $loopCount = 0 ) {
        
        $args = array(
            //'timeout'     => 0,
            'sslverify' => false
        );
        $objResponse = wp_remote_get( $image_url, $args );

        $response = $objResponse['body'];
        
        $txtFileName = time() . '_' . $loopCount . ".jpg";
        file_put_contents(ABSPATH . "wp-content/uploads/" . $txtFileName, $response);
        $file = home_url() . "/wp-content/uploads/" . $txtFileName;

        $wp_filetype = wp_check_filetype( $txtFileName, null );

        $attachment = array(
          'post_mime_type' => $wp_filetype['type'],
          'post_title' => sanitize_file_name( $txtFileName ),
          'post_content' => '',
          'post_status' => 'inherit'
        );

        $attach_id = wp_insert_attachment( $attachment, $file );
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
        wp_update_attachment_metadata( $attach_id, $attach_data );
        
        return $attach_id;

    }
}

new WpCptImport();

if (!function_exists('p_r')){function p_r($s){echo "<code><pre>";print_r($s);echo "</pre></code>";}}
if (!function_exists('write_log')){ function write_log ( $log )  { if ( is_array( $log ) || is_object( $log ) ) { error_log( print_r( $log, true ) ); } else { error_log( $log ); }}}