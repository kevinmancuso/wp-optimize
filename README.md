#WP Optimizer

/**
 * Load in our custom functions..
*/
function kraken_load_include_files() {
    foreach (glob(dirname(__FILE__) . '/includes/*/*.php') as $file) {
        include $file;
    }
}
add_action('init', 'kraken_load_include_files', 15);