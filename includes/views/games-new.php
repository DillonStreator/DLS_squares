<?php
$teams = teams_get_all_Team();
?>

<div class="wrap">
    <h1><?php _e( 'Add Game', '' ); ?></h1>

    <form action="" method="post">

        <table class="form-table">
            <tbody>
                <tr class="row-name">
                    <th scope="row">
                        <label for="name"><?php _e( 'Name', '' ); ?></label>
                    </th>
                    <td>
                        <input type="text" name="name" id="name" class="regular-text" placeholder="<?php echo esc_attr( '', '' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('Enter game name', '' ); ?></span>
                    </td>
                </tr>
                <tr class="row-date">
                    <th scope="row">
                        <label for="date"><?php _e( 'Date', '' ); ?></label>
                    </th>
                    <td>
                        <input type="datetime-local" name="date" id="date" class="regular-text"value="" required="required" />
                        <span class="description"><?php _e('Enter game date', '' ); ?></span>
                    </td>
                </tr>
                <tr class="row-square_cost">
                    <th scope="row">
                        <label for="square_cost"><?php _e( 'Square Cost', '' ); ?></label>
                    </th>
                    <td>
                        <input type="number" name="square_cost" id="square_cost" class="regular-text" placeholder="<?php echo esc_attr( '', '' ); ?>" value="" required="required" />
                        <span class="description"><?php _e('Enter the cost of each individual square', '' ); ?></span>
                    </td>
                </tr>
                <tr class="row-home_team">
                    <th scope="row">
                        <label for="home_team"><?php _e( 'Home Team', '' ); ?></label>
                    </th>
                    <td>
                        <select name="home_team" id="home_team">
                            <option value="" disabled selected>select a home team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team->id; ?>"><?php echo $team->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr class="row-away_team">
                    <th scope="row">
                        <label for="away_team"><?php _e( 'Away Team', '' ); ?></label>
                    </th>
                    <td>
                        <select name="away_team" id="away_team">
                            <option value="" disabled selected>select an away team</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?php echo $team->id; ?>"><?php echo $team->name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
             </tbody>
        </table>

        <input type="hidden" name="field_id" value="0">

        <?php wp_nonce_field( 'game-new' ); ?>
        <?php submit_button( __( 'Add New Game', '' ), 'primary', 'submit_game' ); ?>

    </form>
</div>