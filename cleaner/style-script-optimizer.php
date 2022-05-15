
<?php
if( !is_admin() && !is_user_logged_in() ) {
    //Remove Gutenberg Block Library CSS from loading on the frontend
    function smartwp_remove_wp_block_library_css(){
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
    } 
    add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );
    // This retrieves all scripts and style handles
    function handle_retrieval($styles, $scripts) {
        // all loaded Scripts
        if ($scripts) {
            global $wp_scripts;
            return $wp_scripts->queue;
        }
        // all loaded Styles (CSS)
        if ($styles) {
            global $wp_styles;
            return $wp_styles->queue;
        }
    }
    // Move jQuery script to the footer instead of header.
    function chronos_jquery_to_footer() {
        // wp_scripts()->add_data( 'jquery', 'group', 1 );
        wp_scripts()->add_data( 'jquery-core', 'group', 1 );
        wp_scripts()->add_data( 'jquery-migrate', 'group', 1 );
    }
    add_action( 'wp_enqueue_scripts', 'chronos_jquery_to_footer' );
    //Remove JQuery migrate, 
    function remove_jquery_migrate($scripts){
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            if ($script->deps) { // Check whether the script has any dependencies
                $script->deps = array_diff($script->deps, array(
                    'jquery-migrate'
                ));
            }
        }
    }
    add_action('wp_default_scripts', 'remove_jquery_migrate');
    //Add preload to all enqueue styles. 
    function add_preload_attribute($link, $handle) {
        $all_styles = handle_retrieval(true,false); // A list of all the styles with handles.
        $styles_to_preload = $all_styles;
        # add the preload attribute to the css array and keep the original.
        if ($styles_to_preload) {
            foreach($styles_to_preload as $i => $current_style){
                if(true == strpos($link, $current_style)){
                    $org_link = $link;  
                    // $mod_link = str_replace("rel='stylesheet'", "rel='preload' as='style'", $link);
                    $mod_link = str_replace(array("rel='stylesheet'", "id='"), array("rel='preload' as='style'", "id='pre-"    ), $link);
                    $link = $mod_link . $org_link; 
                    return $link;
                }
            }
        }
    }
    add_filter( 'style_loader_tag', 'add_preload_attribute', 10, 2 );
    // defer external scripts
    add_filter('deferred_scripts', function($scripts) {
        $all_scripts = handle_retrieval(false,true);
        return $all_scripts;
    });
    add_filter('script_loader_tag', function($html, $handle) {
        $deferHandles = apply_filters('deferred_scripts', []);
        if (in_array($handle, $deferHandles)) {
            $html = trim(str_replace("type='text/javascript'", 'type="text/javascript" defer', $html));
        }
        return $html;
    }, 10, 2);
}