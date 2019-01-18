<div class="wrap">
    <h1><?php _e( 'Add Team', '' ); ?></h1>

    <form action="" method="post">

        <table class="form-table">
            <tbody>
                <tr class="row-name">
                    <th scope="row">
                        <label for="name"><?php _e( 'Name', '' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" placeholder="<?php echo esc_attr( '', '' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('Enter team name', '' ); ?></span>
                    </td>
                </tr>
                <tr class="row-logo">
                    <th scope="row">
                        <label for="logo"><?php _e( 'Logo', '' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="logo" id="logo" class="regular-text" placeholder="<?php echo esc_attr( '', '' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('Add the URL of a logo that represents the team', '' ); ?></span>
                    </td>
                </tr>
             </tbody>
        </table>

        <input type="hidden" name="field_id" value="0">

        <?php wp_nonce_field( 'team-new' ); ?>
        <?php submit_button( __( 'Add New Team', '' ), 'primary', 'submit_team' ); ?>

    </form>
</div>