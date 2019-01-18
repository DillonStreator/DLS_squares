<?php

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Squares_Game_Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form' ) );
    }

    /**
     * Handle the game new and edit form
     *
     * @return void
     */
    public function handle_form() {
        if ( ! isset( $_POST['submit_game'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'game-new' ) ) {
            die( __( 'Are you cheating?', '' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', '' ) );
        }

        $errors   = array();
        $page_url = admin_url( 'admin.php?page=dls-squares-admin-games' );
        $field_id = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $date = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date'] ) : '';
        $square_cost = isset( $_POST['square_cost'] ) ? $_POST['square_cost'] : '';
        $home_team = isset( $_POST['home_team'] ) ? $_POST['home_team'] : '';
        $away_team = isset( $_POST['away_team'] ) ? $_POST['away_team'] : '';

        // some basic validation
        if ( ! $name ) {
            $errors[] = __( 'Error: Name is required', '' );
        }
        if ( ! $date ) {
            $errors[] = __( 'Error: Date is required', '' );
        }
        if ( ! $square_cost ) {
            $errors[] = __( 'Error: Square Cost is required', '' );
        }
        if ( ! $home_team ) {
            $errors[] = __( 'Error: Home team is required', '' );
        }
        if ( ! $away_team ) {
            $errors[] = __( 'Error: Away team is required', '' );
        }

        // bail out if error found
        if ( $errors ) {
            $first_error = reset( $errors );
            $redirect_to = add_query_arg( array( 'error' => $first_error ), $page_url );
            wp_safe_redirect( $redirect_to );
            exit;
        }

        $fields = array(
            'name' => $name,
            'date' => $date,
            'square_cost' => $square_cost,
            'home_team' => $home_team,
            'away_team' => $away_team,
        );

        // New or edit?
        if ( ! $field_id ) {

            error_log("Inserting new game...");
            error_log(print_r($fields,true));
            
            $insert_id = game_insert_game( $fields );

        } else {

            error_log("Updating game... ID = ".$field_id);

            $fields['id'] = $field_id;

            $insert_id = game_insert_game( $fields );
        }

        if ( is_wp_error( $insert_id ) ) {
            error_log('ERROR');
            $redirect_to = add_query_arg( array( 'message' => 'error' ), $page_url );
        } else {
            error_log('SUCCESS');
            $redirect_to = add_query_arg( array( 'message' => 'success' ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }
}

new Squares_Game_Form_Handler();