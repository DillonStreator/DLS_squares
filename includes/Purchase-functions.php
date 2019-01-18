<?php

function get_square_purchases_for_game($game_id)
{
    global $wpdb;

    $purchase_table = $wpdb->prefix . 'dls_squares_purchase';
    $user_table = $wpdb->prefix . 'users';

    $query = "SELECT dsp.coordinate, u.display_name AS name FROM $purchase_table AS dsp JOIN $user_table AS u ON u.ID = dsp.user_id WHERE game_id = $game_id";
    $purchases = $wpdb->get_results( $query );

    return $purchases;
    
}