<?php
/*
Plugin Name: FacetWP - Time Since
Plugin URI: https://facetwp.com/
Description: "Time Since" facet
Version: 1.1.1
Author: Matt Gibbs
Author URI: https://facetwp.com/
GitHub Plugin URI: https://github.com/FacetWP/facetwp-time-since

Copyright 2015 Matt Gibbs

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * WordPress init hook
 */
add_action( 'init' , 'fwpts_init' );


/**
 * Intialize facet registration and any assets
 */
function fwpts_init() {
    add_filter( 'facetwp_facet_types', 'fwpts_facet_types' );
}


/**
 * Register the facet type
 */
function fwpts_facet_types( $facet_types ) {
    include( dirname( __FILE__ ) . '/time_since.php' );
    $facet_types['time_since'] = new FacetWP_Facet_Time_Since();
    return $facet_types;
}
