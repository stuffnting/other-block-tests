<?php
/**
 * Plugin Name: SNT Other Block Tests
 * Plugin URI: https://github.com/stuffnting/gutenberg-block-examples
 * Description: Testing Gutenberg blocks.
 * Author: Grover Stones
 * Author URI: https://stuffnting.com
 * version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.2
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: sntOBE
 */

if( !defined( 'ABSPATH') ) {
  exit;
}

/*
 * Define path and URL of this plugin directory
 */
define( 'SNT_OBT_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SNT_OBT_PLUGIN_URL', plugins_url( '/', __FILE__ ) );

/*
 * Generate PHP require_once statements from build-list.json
 */
if ( file_exists( __DIR__ . '/build-list.json' ) ) {
  $build_list_json = file_get_contents('build-list.json', true);
  $build_list = json_decode($build_list_json, true);

  snt_ogt_generate_require_from_json( $build_list );
} else {
  error_log("ERROR: Can't find build-list.json file. Logged from line" . __LINE__ . " in " . __FILE__);
}

function snt_ogt_generate_require_from_json( $build_list ) {

  if ( !is_array( $build_list ) ) {
    return;
  }

  // Ignore these keys in the JSON data
  $ignore_keys = [ '_NOTE' ];

  // Loop through the build list and generate require_once statements
  foreach ( $build_list as $key => $value ) {

    if ( in_array( $key, $ignore_keys ) ) {
      continue;
    }

    $file = __DIR__ . '/' . $value['name'] . '/index.php';

    if ( $value['include'] === true && file_exists( $file )  ) {
      require_once( $file );
    }
  }
}