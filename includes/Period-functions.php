<?php

function period_get_game_periods($game_id)
{
    global $wpdb;

    $query = 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_period WHERE game_id = ' . $game_id;

    $items = $wpdb->get_results( $query );

    return $items;
}

function period_get_column_values($period_id, $home_or_away = null)
{
    global $wpdb;

    $query = 'SELECT * FROM ' . $wpdb->prefix . 'dls_squares_period_column WHERE period_id = ' . $period_id . ($home_or_away ? " AND home_or_away = '$home_or_away'" : '');

    $items = $wpdb->get_results( $query );

    return $items;
}

function create_next_period_for_game($game_id)
{
    global $wpdb;

    $period_table = $wpdb->prefix . 'dls_squares_period';

    $max_period_query = 'SELECT MAX(period) AS period FROM ' . $period_table . ' WHERE game_id = '. $game_id;
    $period = $wpdb->get_results( $max_period_query );
    $next_period = 1;
    if (count($period))
    {
        $next_period = ($period[0]->period + 1);
    }
    $new_period = array(
        'game_id' => $game_id,
        'period' => $next_period
    );
    if ($wpdb->insert( $period_table , $new_period ))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function generate_and_save_period_column_values_for_game($period_id)
{
    global $wpdb;

    $period_column_table = $wpdb->prefix . 'dls_squares_period_column';

    $period_col_vals_query = "SELECT * FROM $period_column_table WHERE period_id = $period_id";
    $period_col_vals_result = $wpdb->get_results( $period_col_vals_query );
    if (count($period_col_vals_result)) return false;

    $coords = array( 'away' , 'home' );
    $values = array( 0 , 1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9 );

    foreach ($coords as $coord)
    {
        $working_values = $values; // copy the values as we will be shuffling and popping values from the working array
        $index = 0;
        while(count($working_values))
        {
            shuffle($working_values);
            $value = array_shift($working_values);
            $period_column = array(
                'period_id' => $period_id,
                'home_or_away' => $coord,
                'position' => $index,
                'column_value' => $value
            );
            if ( !$wpdb->insert( $period_column_table, $period_column ) ) {
                return false;
            }
            $index++;
        }
    }
    return true;
}

?>