<?php

/**
 * Get all Game
 *
 * @param $args array
 *
 * @return array
 */
function games_get_all_Game( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'number'     => 20,
        'offset'     => 0,
        'orderby'    => 'id',
        'order'      => 'ASC',
        'where'      => null
    );

    $args      = wp_parse_args( $args, $defaults );
    $cache_key = 'Game-all';
    $items     = wp_cache_get( $cache_key, '' );

    if ( false === $items ) {
        $query = 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_game' . (isset($args['where']) ? (' WHERE '. $args['where'] . ' ') : '') .' ORDER BY ' . $args['orderby'] .' ' . $args['order'] .' LIMIT ' . $args['offset'] . ', ' . $args['number'];
        $items = $wpdb->get_results( $query );

        wp_cache_set( $cache_key, $items, '' );
    }

    return $items;
}

/**
 * Fetch all Game from database
 *
 * @return array
 */
function games_get_Game_count() {
    global $wpdb;

    return (int) $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'dls_squares_game' );
}

/**
 * Fetch a single Game from database
 *
 * @param int   $id
 *
 * @return array
 */
function games_get_Game( $id = 0 ) {
    global $wpdb;

    return $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_game WHERE id = %d', $id ) );
}

/**
 * Insert a new game
 *
 * @param array $args
 */
function game_insert_game( $args = array() ) {
    global $wpdb;

    $defaults = array(
        'id'         => null,
        'name' => '',
        'date' => null,
        'square_cost' => null,
        'home_team' => null,
        'away_team' => null
    );

    $args       = wp_parse_args( $args, $defaults );
    $table_name = $wpdb->prefix . 'dls_squares_game';

    // some basic validation
    if ( empty( $args['date'] ) ) {
        return new WP_Error( 'no-date', __( 'No Date provided.', '' ) );
    }
    if ( empty( $args['square_cost'] ) ) {
        return new WP_Error( 'no-square_cost', __( 'No Square cost provided.', '' ) );
    }

    // remove row id to determine if new or update
    $row_id = (int) $args['id'];
    unset( $args['id'] );

    if ( ! $row_id ) {

        $wpdb->query('START TRANSACTION');

        $game_insert_good = $wpdb->insert( $table_name, $args );
        $game_id = $wpdb->insert_id;

        $period_table = $wpdb->prefix . 'dls_squares_period';
        $period = array(
            'period' => 1,
            'game_id' => $game_id
        );
        $period_insert_good = $wpdb->insert( $period_table, $period );

        // insert a new
        if ( $game_insert_good && $period_insert_good )
        {
            $wpdb->query("COMMIT");
        }
        else
        {
            $wpdb->query("ROLLBACK");
        }

    }
    else
    {

        // do update method here
        if ( $wpdb->update( $table_name, $args, array( 'id' => $row_id ) ) )
        {
            return $row_id;
        }
    }

    return false;
}