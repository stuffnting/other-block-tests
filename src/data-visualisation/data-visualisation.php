<?php
// ACF options page to hold the Google Cloud API key.
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'SNT Google API Key',
		'menu_title'	=> 'SNT Data Visualisation',
		'menu_slug' 	=> 'google-api-key-options',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}

define('SNT_OBT_DATA_VIS_META_FIELD_OBJECT_NAME', '_myprefix_dynamic_meta_block_object');

// register custom meta data field
add_action( 'init', 'snt_obt_data_visualisation_cb_meta' );

function snt_obt_data_visualisation_cb_meta() {
  register_post_meta( 
    'post', 
    SNT_OBT_DATA_VIS_META_FIELD_OBJECT_NAME, 
    array(
      'type'          => 'object',
      'single'        => true,
      'show_in_rest'  => array(
        'schema' => array(
          'type'       => 'object',
          'properties' => array(
            'field1' => array(
              'type' => 'string',
            ),
            'field2' => array(
              'type' => 'string',
            ),
          ),
        ),
      ),
      'auth_callback' => function() { 
        return current_user_can('edit_posts');
      }
    )
  );
}

function snt_obt_data_visualisation_cb( $attributes, $inner_blocks ) {
  // Format the meta values as HTML
  $meta_out = '<h2 style="margin-top: 0">Meta save test with inner blocks</h2>';

  //Get a flattened array
  $meta = array_merge( [], get_post_meta( get_the_ID(), SNT_OBT_DATA_VIS_META_FIELD_OBJECT_NAME, true ) );

  $meta_out .= $meta ? sprintf( "<p>Field 1: %s</p>\n<p>Field 2: %s</p>",
    esc_html( $meta['field1'] ?? '' ),
    esc_html( $meta['field2'] ?? '' )
  ) : "";

  /**
   * The block's meta values can not be used in its `save` function,
   * whereas, the block's inner-blocks are included. This means that
   * the meta values need to be added back into the HTML from 
   * the `save` function.
   * 
   * $meta_out is placed into the div wrapper added by the inner-block
   * by the `save` function.
   */ 
  $re = '@(<div[\w\W\r\t\n]*>)@mU';
  $subst = "$1\n$meta_out";

  $final_out = preg_replace($re, $subst, $inner_blocks);

  return $final_out;
}

add_action( 'init', 'snt_obt_data_visualisation_script_editor_assets' );

function snt_obt_data_visualisation_script_editor_assets() {

  if ( ! function_exists( 'register_block_type' ) ) {
    // Gutenberg is not active.
    return;
  }

  // Your Gutenberg Block JS code
  wp_register_script( 
    'snt-obt-data-visualisation-script', 
    SNT_OBT_PLUGIN_URL . basename( __DIR__ ) . '/index.js',
    array(),
    filemtime( SNT_OBT_PLUGIN_PATH . basename( __DIR__ ) . '/index.js' ), // *** Dev only
    true
  );

  // Register the call_back for rendering on the front end
  register_block_type( __DIR__, array(
    'api_version' => 2,    
    'editor_script'   => 'snt-obt-data-visualisation-script',
    'render_callback' => 'snt_obt_data_visualisation_cb'
  ) );
}

