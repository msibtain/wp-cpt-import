<?php
class WpCptExport
{
    function __construct() {
        add_action('rest_api_init', [$this, 'func_init_endpoints']);
    }

    function func_init_endpoints(){

        register_rest_route( 'v1', '/cptexport', array(
            'methods' => 'GET',
            'callback' => [$this, 'func_fetch_cptdata'], 
        ) );
    
    }

    function func_fetch_cptdata($data) {

        $arrPosts = get_posts([
            'post_type' => $data['cpt'],
            'post_status' => 'publish',
            'posts_per_page' => $data['ppp'],
            'offset' => $data['pn'] * $data['ppp'],
            'orderby' => 'ID',
            //'exclude' => [8294, 8426],
            //'include' => [17015, 19551, 21584]      // shore excursion, transport, excursions
        ]);

        $return = [];
        
        foreach ($arrPosts as $objPost)
        {
            $pm = get_post_meta($objPost->ID);

            $gallery = [];

            if (have_rows('excursion_gallery', $objPost->ID) )
            {
                while( have_rows('excursion_gallery', $objPost->ID) ) : 
                    the_row();
                    $variable = get_sub_field('image');
                    $gallery[] = $variable['url'];
                endwhile;
            }

            $prices = [];

            $excursion_options = get_field("excursion_options", $objPost->ID);
            
            
            if ($excursion_options) :
            foreach ($excursion_options as $index => $eo)
            {
                $prices[$index]['option_title'] = $eo['option_title'];
                $prices[$index]['option_group'] = $eo['option_description'];

                if (is_countable($eo['private_prices']['prices']))
                {
                    foreach ($eo['private_prices']['prices'] as $p)
                    {
    
                        $prices[] = [
                            'option_title' => $eo['option_title'],
                            'option_description' => $eo['option_description'],
                            'from' => $p['from'],
                            'to' => $p['to'],
                            'adult' => $p['prices']['adults'],
                            'adult_sale' => $p['prices']['adult_sale'],
                            'child' => $p['prices']['child'],
                            'child_sale' => $p['prices']['child_sale'],
                            'student_id' => $p['prices']['student_id'],
                            'student_id_sale' => $p['prices']['student_id_sale'],
                            'infants' => $p['prices']['infants'],
                            'infants_sale' => $p['prices']['infants_sale'],
    
                        ];
                    }
                }
                else
                {
                    $prices[] = [
                        'option_title' => $eo['option_title'],
                        'option_description' => $eo['option_description'],
                        'from' => "",
                        'to' => "",
                        'adult' => $eo['group_prices']['adults'],
                        'adult_sale' => $eo['group_prices']['adult_sale'],
                        'child' => $eo['group_prices']['child'],
                        'child_sale' => $eo['group_prices']['child_sale'],
                        'student_id' => $eo['group_prices']['student_id'],
                        'student_id_sale' => $eo['group_prices']['student_id_sale'],
                        'infants' => $eo['group_prices']['infants'],
                        'infants_sale' => $eo['group_prices']['infants_sale'],

                    ];
                }

                
                
                
            }
            endif;

            $return[] = [
                'post' => $objPost,
                'meta' => $pm,
                'gallery' => $gallery,
                'prices' => $prices
            ];
        }

        return [
            'success' => true,
            'cpt' => $data['cpt'],
            'data' => $return
        ];
    }
}

new WpCptExport();