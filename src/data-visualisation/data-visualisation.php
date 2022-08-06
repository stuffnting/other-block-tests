<?php
// ACF options page to hold the Google Cloud API key.
if( function_exists('acf_add_options_page') ) {
	
  // NOTE: The ACF must be set up to have the Field Name google_api_key
	acf_add_options_page(array(
		'page_title' 	=> 'SNT Google API Key',
		'menu_title'	=> 'SNT Data Visualisation',
		'menu_slug' 	=> 'google-api-key-options',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}

/**
 * Register the dynamic block
 */
add_action( 'init', 'snt_obt_data_visualisation_init' );

function snt_obt_data_visualisation_init() {
  if ( ! function_exists( 'register_block_type' ) ) {
    // Gutenberg is not active.
    return;
  }
  
  wp_register_script( 
    'snt-obt-data-visualisation-script', // This handle used in block.json to identify main block script
    SNT_OBT_PLUGIN_URL . basename( __DIR__ ) . '/index.js',
    array(),
    filemtime( SNT_OBT_PLUGIN_PATH . basename( __DIR__ ) . '/index.js' ), // *** Dev only
    true
  );

  register_block_type( __DIR__, array(
    'render_callback' => 'snt_obt_data_visualisation_cb'        
  ) );
}

/**
 * Dynamic block callback function for the block.
 * 
 * Gets the spreadsheet data and makes the SVG element.
 * 
 * @param   array   $attributes The attributes from the block
 * 
 * @return  string  HTML for the block including the SVG element.
 */
function snt_obt_data_visualisation_cb( $attributes ) {

  if ( function_exists('acf_add_options_page') ) {
    $api_key = get_field('google_api_key', 'option');
  }

  if ( empty( $api_key ) ) {
    return "<p>Can't find the Google API key.</p>";
  }

  $processed_body = snt_obt_data_visualisation_get_sheet_data($attributes, $api_key);
  $data = snt_obt_data_visualisation_count_classes( $processed_body );

  $svg = snt_obt_data_visualisation_svg( $data );

  return "<div class='my-dynamic-block'>{$svg}</div>";
}

/**
 * Gets the data from the spreadsheet.
 * 
 * @param array   $attributes The attributes from the block
 * @param string  $api_key The Google API key
 */
function snt_obt_data_visualisation_get_sheet_data( $attributes, $api_key ) {
  // Extract the Google sheet ID by replacing all the other bits of the URL with nothing
  $sheet_id = preg_replace(
    '/(https:\/\/docs.google.com\/spreadsheets\/d\/)|\/edit.*/',
    '',
    $attributes['sheetUrl']
  );

  // Calculate the range of data to get.
  $range = $attributes['column'];
  $range .= '2%3A'; // Second column, %3A is the escape value for :
  $range .= $attributes['column'];
  $range .= '1000';

  $get_data = new WP_Http();
  $url = 'https://sheets.googleapis.com/v4/spreadsheets/';
  $url .= $sheet_id;
  $url .= '/values/' . $range;
  $url .= '/?&key=' . $api_key;

  $raw_data = $get_data->get( $url );

  $processed_data = json_decode(
    $raw_data['body'],
    true
  );
  
  return $processed_data;
}

/**
 * Count the number of each class in the data.
 * 
 * PHP Version 7.3
 * 
 * @param   array   $processed_body An array og data obtained from JSON retrieved form the spreadsheet
 * 
 * @return  array   $data  An array containing the count values
 */
function snt_obt_data_visualisation_count_classes( $processed_body ) {
  $data = array();

  // Work through the data array
  foreach ( $processed_body['values'] as $d ) {
    /* 
      $d[0] contains the data we are interested in. 
       It data will be:
         1. Freshman
         2. Sophomore
         3. Junior
         4. Senior 
    */
    if ( array_key_exists( $d[0], $data ) ) {
      // If the value already exists add one
      $data[ $d[0] ]++;
    } else {
      // Otherwise, create new item
      $data[ $d[0] ] = 1;
    }
  }

  return $data;
}

/**
 * Generate the SVG graph
 * 
 * @param array $data An array containing the count values.
 * 
 * @return string The SVG graph
 */
function snt_obt_data_visualisation_svg( $data ) {
  // Work out the total height of the svg element.
  $bar_height = 60;
  $bar_gap = 20;
  $bottom_margin = 20;

  $svg_height = ( sizeof($data) * ( $bar_height + $bar_gap ) ) + $bottom_margin;
  $svg_height .= "px";

  $x_offset = "20%";
  
  $y_axis_height = ( sizeof($data) * ( $bar_height + $bar_gap ) );

  $max_no = max( $data );

  // Generate the bars from the data.
  $bars_so_far = 0;
  $bars_svg = "";

  foreach ( $data as $label => $value ) {
    $x = $x_offset;
    $y = $bars_so_far * ( $bar_height + $bar_gap);
    $y_label = $y + ( $bar_height / 2 );
    $bar_width = $value / $max_no * 100 . "%";

    $bars_svg .= <<<HEREA
      <g role="listitem" aria-label="$label, $value" tabindex="0">
        <desc>Bar for $label</desc>
        <rect role="presentation" x="$x" y="$y" width="$bar_width" height="$bar_height" fill="#00f" />
        <text role="presentation" x="0" y="$y_label" fill="#000" font-size="16">$label</text>
      </g>
HEREA;

    $bars_so_far++;
  }

  // Generate the rest of the graph.
  $svg =<<<HEREB
    <svg xmlns="http://www.w3.org/2000/svg" 
      width="100%" 
      height="$svg_height">
      <title>My Chart</title>
      <desc>What my chart is about!</desc>
      <g class="chart_setup">
        <!-- X-Axis -->
        <line
          role="presentation"
          x1="$x_offset" y1="$y_axis_height"
          x2="100%" y2="$y_axis_height"
          stroke="#000" stroke-width="2" />
          <text
            role="presentation"
            x="$x_offset"
            y="$svg_height"
            fill="#000" font-size="14">0</text>
          <text
            role="presentation"
            x="96%"
            y="$svg_height"
            fill="#000" font-size="14">$max_no</text>
        <!-- Y-Axis -->
        <line
          role="presentation"
          x1="$x_offset" y1="0"
          x2="$x_offset" y2="$y_axis_height"
          stroke="#000" stroke-width="2" />
      </g>
      <!-- Bars -->
      <g role="list" aria-label="Bar graph">
        $bars_svg
      </g>
    </svg>
HEREB;

return $svg;
}