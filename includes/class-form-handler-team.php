<?php

/**
 * Handle the form submissions
 *
 * @package Package
 * @subpackage Sub Package
 */
class Squares_Team_Form_Handler {

    /**
     * Hook 'em all
     */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'handle_form' ) );
    }

    /**
     * Handle the team new and edit form
     *
     * @return void
     */
    public function handle_form() {
        if ( ! isset( $_POST['submit_team'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'team-new' ) ) {
            die( __( 'Are you cheating?', '' ) );
        }

        if ( ! current_user_can( 'read' ) ) {
            wp_die( __( 'Permission Denied!', '' ) );
        }

        $errors   = array();
        $page_url = admin_url( 'admin.php?page=dls-squares-admin-teams' );
        $field_id = isset( $_POST['field_id'] ) ? intval( $_POST['field_id'] ) : 0;

        $name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
        $logo = isset( $_POST['logo'] ) ? sanitize_text_field( $_POST['logo'] ) : '';

        // some basic validation
        if ( ! $name ) {
            $errors[] = __( 'Error: Name is required', '' );
        }

        if ( ! $logo ) {
            $errors[] = __( 'Error: Logo is required', '' );
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
            'logo' => $logo,
        );

        // New or edit?
        if ( ! $field_id ) {
            
            $insert_id = team_insert_team( $fields );

        } else {

            $fields['id'] = $field_id;

            $insert_id = team_insert_team( $fields );
        }

        if ( is_wp_error( $insert_id ) ) {
            $redirect_to = add_query_arg( array( 'message' => 'error' ), $page_url );
        } else {
            $redirect_to = add_query_arg( array( 'message' => 'success' ), $page_url );
        }

        wp_safe_redirect( $redirect_to );
        exit;
    }
}

new Squares_Team_Form_Handler();