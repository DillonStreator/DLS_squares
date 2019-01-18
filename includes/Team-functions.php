<?php

/**
 * Get all Team
 *
 * @param $args array
 *
 * @return array
 */
function teams_get_all_Team( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'Team-all';
    $items     = wp_cache_get( $cache_key, '' );

    if ( false === $items ) {
        $items = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_team ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'] );

        wp_cache_set( $cache_key, $items, '' );
    }

    return $items;
}

/**
 * Fetch all Team from database
 *
 * @return array
 */
function teams_get_Team_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dls_squares_team' );
}

/**
 * Fetch a single Team from database
 *
 * @param int   $id
 *
 * @return array
 */
function teams_get_Team( $id = 0 ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_team WHERE id = %d', $id ) );
}

/**
 * Insert a new team
 *
 * @param array $args
 */
function team_insert_team( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'name' => '',
        'logo' => '',

    );

    $args       = wp_parse_args( $args, $defaults );
    $table_name = $wpdb->prefix . 'dls_squares_team';

    // some basic validation
    if ( empty( $args['name'] ) ) {
        return new WP_Error( 'no-name', __( 'No Name provided.', '' ) );
    }
    if ( empty( $args['logo'] ) ) {
        return new WP_Error( 'no-logo', __( 'No Logo provided.', '' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {
        
        // insert a new
        if ( $wpdb->insert( $table_name, $args ) ) {
            return $wpdb->insert_id;
        }

    } else {

        // do update method here
        if ( $wpdb->update( $table_name, $args, array( 'id' => $row_id ) ) ) {
            return $row_id;
        }
    }

    return false;
}