<?php

function create_score_for_period($score, $scorer, $team_id, $period_id)
{
    global $wpdb;
    $score_table = $wpdb->prefix . 'dls_squares_score'; 

    $score_item = array(
        'period_id' => $period_id,
        'team_id' => $team_id,
        'points' => $score,
        'scorer' => $scorer
    );
    if ( !$wpdb->insert( $score_table, $score_item ) ) {
        return false;
    }

    return true;
}
function update_score_by_id($score_id,$period_id,$team_id,$points,$scorer)
{
    error_log("update_score_by_id id:$score_id period:$period_id team:$team_id points:$points scorer:$scorer");
    global $wpdb;
    $score_table = $wpdb->prefix . 'dls_squares_score'; 

    $update = array(
        'period_id' => $period_id,
        'team_id' => $team_id,
        'points' => $points,
        'scorer' => $scorer
    );
    $where = array(
        'id' => $score_id
    );
    if ( !$wpdb->update( $score_table, $update, $where ) ) {
        return false;
    }

    return true;
}
function delete_score_by_id($score_id)
{
    global $wpdb;
    $score_table = $wpdb->prefix . 'dls_squares_score';

    if ( !$wpdb->delete($score_table,array('id'=>$score_id)) ) {
        return false;
    }

    return true;
}
function get_scores_for_period_by_teamId($period_id, $team_id)
{
    global $wpdb;
    $score_table = $wpdb->prefix . 'dls_squares_score';

    $query = "SELECT * FROM $score_table WHERE period_id = $period_id AND team_id = $team_id";

    $items = $wpdb->get_results( $query );

    return $items;
}