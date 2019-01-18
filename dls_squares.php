<?php
/**
 * Plugin Name: DLS Squares
 * Description: Allows square purchashing
 * Author: DLS
 */

//=========================================== BACKEND : START ============================================================

add_action( 'init', function() {

    include dirname(__FILE__) . '/includes/class-squares-team-admin-menu.php';
    include dirname(__FILE__) . '/includes/class-Team-list-table.php';
    include dirname(__FILE__) . '/includes/class-form-handler-team.php';
    include dirname(__FILE__) . '/includes/Team-functions.php';

    include dirname(__FILE__) . '/includes/class-squares-game-admin-menu.php';
    include dirname(__FILE__) . '/includes/class-Game-list-table.php';
    include dirname(__FILE__) . '/includes/class-form-handler-game.php';
    include dirname(__FILE__) . '/includes/Game-functions.php';

    include dirname(__FILE__) . '/includes/Score-functions.php';

    include dirname(__FILE__) . '/includes/Purchase-functions.php';
    
    include dirname(__FILE__) . '/includes/process-payment.php';


    include dirname(__FILE__) . '/includes/Period-functions.php';


    new Squares_Team_Admin_Menu();
    new Squares_Game_Admin_Menu();

});


function dls_squares_manager_page()
{

    if ( !current_user_can('administrator') ) die("nope");


    if ( isset($_POST['dls_squares_admin_stripe_keys']) )
    {
        update_option('dls_squares_spk', $_POST['spk']);
        update_option('dls_squares_ssk', $_POST['ssk']);
    }

    $spk = get_option('dls_squares_spk', '');
    $ssk = get_option('dls_squares_ssk', '');
    
    ?>
        <div class="wrap">
            <h1>Squares manager</h1>
            <hr>

            <h2>Stripe</h2>
            <form method="POST">
                <table class="form-table">
                    <tbody>
                        <tr class="row-name">
                            <th scope="row">
                                <label for="spk">Publishable key</label>
                            </th>
                            <td>
                                <input type="text" class="form-control" id="spk" name="spk" value="<?= $spk ?>">
                            </td>
                        </tr>
                        <tr class="row-logo">
                            <th scope="row">
                                <label for="ssk">Secret key</label>
                            </th>
                            <td>
                                <input type="text" class="form-control" id="ssk" name="ssk" value="<?= $ssk ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="submit" name="dls_squares_admin_stripe_keys" value="Update Stripe Keys" class="button button-primary">
            </form>
        </div>
    <?php
}
function dls_squares_admin()
{
    add_menu_page(
        'Squares manager', //title
        'Squares',
        'manage_options', //user access
        'dls-squares-admin', //slug
        'dls_squares_manager_page', //callback function for rendering
        plugin_dir_url( __FILE__ ) . '/public/images/squares_icon_20x20.png', //menu icon.
        200 //where on menu
    );
}
add_action('admin_menu', 'dls_squares_admin');
//=========================================== BACKEND : END ==============================================================




//=========================================== DB INSTALL : START ==========================================================

global $dls_squares_db_version;
$dls_squares_db_version = '1.0';

function dls_squares_install() {
	global $wpdb;
    global $dls_squares_db_version;
    
    $user_table_name = $wpdb->prefix . 'users';
    
    $charset_collate = $wpdb->get_charset_collate();

	$team_table_name = $wpdb->prefix . 'dls_squares_team';
	$sql = "CREATE TABLE $team_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		logo tinytext DEFAULT '' NOT NULL,
		PRIMARY KEY  (id)
    ) $charset_collate;";

    $game_table_name = $wpdb->prefix . 'dls_squares_game';
    $sql .= "CREATE TABLE $game_table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name tinytext NOT NULL,
		date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        square_cost int(9) NOT NULL,
		home_team mediumint(9) NOT NULL,
		away_team mediumint(9) NOT NULL,
		PRIMARY KEY  (id),
        FOREIGN KEY  (home_team) REFERENCES $team_table_name(id),
        FOREIGN KEY  (away_team) REFERENCES $team_table_name(id)
    ) $charset_collate;";

    $period_table_name = $wpdb->prefix . 'dls_squares_period';
    $sql .= "CREATE TABLE $period_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        period int(4) DEFAULT 1 NOT NULL,
        game_id mediumint(9) NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY  (game_id) REFERENCES $game_table_name(id)
    ) $charset_collate;";

    $period_table_name = $wpdb->prefix . 'dls_squares_period_column';
    $sql .= "CREATE TABLE $period_table_name (
        period_id mediumint(9) DEFAULT 1 NOT NULL,
        home_or_away tinytext NOT NULL,
        position int NOT NULL,
        column_value int NOT NULL,
        FOREIGN KEY  (period_id) REFERENCES $period_table_name(id)
    ) $charset_collate;";

    $score_table_name = $wpdb->prefix . 'dls_squares_score';
    $sql .= "CREATE TABLE $score_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        period_id mediumint(9) NOT NULL,
        team_id mediumint(9) NOT NULL,
        points mediumint(9) NOT NULL,
        scorer tinytext DEFAULT '' NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY  (period_id) REFERENCES $period_table_name(id),
        FOREIGN KEY  (team_id) REFERENCES $team_table_name(id)
    ) $charset_collate;";

    $purchase_table_name = $wpdb->prefix . 'dls_squares_purchase';
    $sql .= "CREATE TABLE $purchase_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        game_id mediumint(9) NOT NULL,
        user_id bigint(20) UNSIGNED NOT NULL,
        coordinate tinytext NOT NULL,
        PRIMARY KEY  (id),
        FOREIGN KEY  (game_id) REFERENCES $game_table_name(id),
        FOREIGN KEY  (user_id) REFERENCES $user_table_name(ID)
    ) $charset_collate;";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	add_option( 'dls_squares_db_version', $dls_squares_db_version );
}

register_activation_hook( __FILE__, 'dls_squares_install' );

//=========================================== DB INSTALL : END ============================================================




//============================================= FRONTEND : START ============================================================
function dls_squares()
{
    
    global $wpdb;


    if (isset($_POST['submit_generate_period_values']) && current_user_can('administrator'))
    {
        if( generate_and_save_period_column_values_for_game((int)$_POST['period_id']) )
        {
            ?>
                <script>alert("SUCCESS\n\nGenerated the column values successfully."</script>
            <?php
        }
        else
        {
            ?>
                <script>alert("ERROR\n\There was an error generating the column values."</script>
            <?php
        }
    }
    elseif (isset($_POST['submit_create_next_period']) && current_user_can('administrator'))
    {
        error_log("creating next period for game ".$_POST['game_id']);
        if ( create_next_period_for_game((int)$_POST['game_id']) )
        {
            ?>
                <script>alert("SUCCESS\n\Created the next period successfully."</script>
            <?php
        }
        else
        {
            ?>
                <script>alert("ERROR\n\There was an error creating the next period."</script>
            <?php
        }
    }
    elseif ( isset($_POST['submit_create_score']) && current_user_can('administrator') )
    {
        if ( create_score_for_period((int)$_POST['points'], $_POST['scorer'], (int)$_POST['team_id'], (int)$_POST['period_id']) )
        {
            //success
        }
        else
        {
            //failure
        }
    }
    elseif ( isset($_POST['submit_update_score']) && current_user_can('administrator') )
    {
        if ( update_score_by_id((int)$_POST['score_id'],$_POST['period_id'],$_POST['team_id'],$_POST['points'],$_POST['scorer']) )
        {
            //success
        }
        else
        {
            //fail
        }
    }
    elseif ( isset($_POST['submit_delete_score']) && current_user_can('administrator') )
    {
        if ( delete_score_by_id((int)$_POST['score_id']) )
        {
            //success
        }
        else
        {
            //fail
        }
    }

    // all styles
    wp_enqueue_style( 'bootstrap', plugin_dir_url( __FILE__ ) . '/public/vendor/bootstrap/css/bootstrap.css', array(), 20141119 );
    wp_enqueue_style( 'theme-style', plugin_dir_url( __FILE__ ) . '/public/css/squares_styles.css?v='.time(), array(), 20141119 );
    // all scripts
    wp_enqueue_script( 'bootstrap', plugin_dir_url( __FILE__ ) . '/public/vendor/bootstrap/js/bootstrap.min.js', array('jquery'), '20120206', true );
    wp_enqueue_script( 'theme-script', plugin_dir_url( __FILE__ ) . '/public/js/squares_scripts.js', array('jquery'), '20120206', true );

    $current_user = wp_get_current_user();
    // print_r($current_user->data);
    $rows = 10;
    $cols = 10;

    $condition = array(
        'orderby' => 'date'
    );
    $games = games_get_all_Game($condition);
    function array_object_find_by_id($id, $array) {
        $item = null;
        foreach ($array as $object) {
            if ($object->id == $id) {
                $item = $object;
                break;
            }
        }
        return $item;
    }
    $game = isset($_GET['game']) ? array_object_find_by_id($_GET['game'],$games) : $games[0];
    if (!$game) $game = $games[0];

    $periods = period_get_game_periods($game->id);
    $period = isset($_GET['period']) ? array_object_find_by_id($_GET['period'],$periods) : $periods[count($periods)-1];
    if (!$period) $period = $periods[count($periods)-1];

    $period_columns_home = period_get_column_values($period->id, "home" );
    $period_columns_away = period_get_column_values($period->id, "away" );

    $column_values_generated = count($period_columns_home) > 0;
    $period_is_last = $period->id === $periods[count($periods)-1]->id;
    
    $purchases = get_square_purchases_for_game($game->id);
    function get_purchase($coord, $purchases)
    {
        $purchase = null;
        foreach($purchases as $p) {
            if ($p->coordinate == $coord) {
                $purchase = $p;
                break; 
            }
        }
        return $purchase;
    }
    
    $home_team = teams_get_Team($game->home_team);
    $away_team = teams_get_Team($game->away_team);

    $home_team_scores = get_scores_for_period_by_teamId($period->id, $home_team->id);
    $away_team_scores = get_scores_for_period_by_teamId($period->id, $away_team->id);

    $home_team_scores_total = array_reduce($home_team_scores, function ($acc, $curr) {
        return $acc += $curr->points;
    }, 0);
    $away_team_scores_total = array_reduce($away_team_scores, function ($acc, $curr) {
        return $acc += $curr->points;
    }, 0);

    $htst_str = (string)$home_team_scores_total;
    $atst_str = (string)$away_team_scores_total;
    $hlen = strlen($htst_str);
    $alen = strlen($atst_str);
    $home_team_score_last_digit = (int) substr($htst_str, $hlen - 1);
    $away_team_score_last_digit = (int) substr($atst_str, $alen - 1);

    ?>
        <script src="https://checkout.stripe.com/checkout.js"></script>
        <h3>Game</h3>
        <select class="form-control" name="game" id="game">
            <?php foreach ($games as $g): ?>
                <option <?= $g->id === $game->id ? 'selected' : '' ?> value="<?php echo $g->id; ?>"><?php echo $g->name . ' | ' . date('m/d/Y', strtotime($g->date)) . ' | $' . $g->square_cost; ?></option>
            <?php endforeach; ?>
        </select>
        <div class="col-xs-12">
            <h4><?= $game->name; ?></h4>
            <table class="table gameboard-outer">
                <thead>
                    <th>&nbsp;</th>
                    <th class="team"><?= $home_team->name; ?></th>
                </thead>
                <tbody>
                    <tr>
                        <td class="team" width="2%"><?= $away_team->name; ?></td>
                        <td width="98%">
                            <div class="table-responsive">
                                <table class="gameboard-inner">
                                    <tbody>
                                        <?php
                                            for ($i=0; $i <= $rows; $i++) {
                                                ?>
                                                    <tr>
                                                        <?php
                                                            for ($j=0; $j <= $cols; $j++) {

                                                                $isColumnNumber =  (($i == 0 && $j >= 1)||($j == 0 && $i >= 1));
                                                                $isQuarter = ($i == 0 && $j == 0);
                                                                $isHome = $i == 0;
                                                                $team_str = $isHome ? 'home' : 'away';
                                                                $home_col_val = null;
                                                                $away_col_val = null;
                                                                if ($column_values_generated && $i != 0 && $j != 0) {
                                                                    $home_col_val = $period_columns_home[$j-1]->column_value;
                                                                    $away_col_val = $period_columns_away[$i-1]->column_value;
                                                                }
                                                                
                                                                if ($isQuarter)
                                                                {
                                                                    ?>

                                                                    <td class="column-number quarter" style="padding:0px;">
                                                                        <select style="padding:0px;" class="form-control" name="period" id="period">
                                                                            <?php foreach ($periods as $p): ?>
                                                                                <option <?= $p->id === $period->id ? 'selected' : '' ?> value="<?= $p->id ?>">P<?= $p->period ?></option>
                                                                            <?php endforeach; ?>
                                                                            <?php if( current_user_can('administrator') ): ?>
                                                                                <?php if ($column_values_generated && $period_is_last): ?>
                                                                                    <option disabled>────────────────</option>
                                                                                    <option value="create_next_period">Create Next Period</option>
                                                                                <?php elseif (!$column_values_generated): ?>
                                                                                    <option disabled>────────────────</option>                                                                                
                                                                                    <option value="generate_period_values">Generate Period Values</option>
                                                                                <?php endif; ?>
                                                                            <?php endif; ?>
                                                                        </select>
                                                                    </td>
                                                                    
                                                                    <?php
                                                                }
                                                                elseif ($isColumnNumber)
                                                                {
                                                                    if ($isHome) {
                                                                        render_column_number_cell($j, 'home', $column_values_generated?$period_columns_home[$j-1]->column_value:null);
                                                                    }
                                                                    else {
                                                                        render_column_number_cell($i, 'away', $column_values_generated?$period_columns_away[$i-1]->column_value:null);
                                                                    }
                                                                }
                                                                else
                                                                {
                                                                    $is_winning_coord = $column_values_generated && ($home_team_score_last_digit == $home_col_val && $away_team_score_last_digit == $away_col_val);
                                                                    $is_winning_col = $column_values_generated && ($home_team_score_last_digit == $home_col_val || $away_team_score_last_digit == $away_col_val) && !$is_winning_coord;
                                                                    $extra_class = $is_winning_col ? 'winning-col' : ($is_winning_coord ? 'winning-coord' : '');

                                                                    $coord = "".($j-1).",".($i-1);
                                                                    $user_purchase = get_purchase($coord, $purchases);
                                                                    ?>
                                                                    <td class="dls-square <?= $extra_class; ?>" data-homecol="<?= $j-1 ?>" data-awaycol="<?= $i-1 ?>">
                                                                        <?php if ($user_purchase): ?>
                                                                            <?= $user_purchase->name; ?>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <?php
                                                                }
                                                            }
                                                        ?>
                                                    </tr>
                                                <?php
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            
            <table class="table">
                <thead>
                    <th>&nbsp;</th>
                    <th>Teams</th>
                    <th>Scores</th>
                    <th>Total</th>
                </thead>
                <tbody>
                    <tr>
                        <td width="2%" style="font-size:10px;">Home</td>
                        <td width="100px;">
                            <img src="<?= $home_team->logo; ?>">
                        </td>
                        <td style="padding-right:0px;">
                            <div class="scores" id="scores_home">
                                <?php foreach($home_team_scores as $hts): ?>
                                    <div class="score score_edit" onclick="<?= current_user_can('administrator') ? ('editScore('.$hts->id.','.$home_team->id.','.$hts->points.',\''.$hts->scorer.'\')') : null; ?>">
                                        <p class="score_points"><?= $hts->points; ?></p>
                                        <p class="score_name"><?= $hts->scorer; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <div class="score">
                                    <button onclick="newScore(<?= $home_team->id; ?>)" class="btn btn-primary" style="height:100%;width:100%;">+</button>
                                </div>
                            </div>
                        </td>
                        <td width="100px;">
                            <?= $home_team_scores_total; ?>
                        </td>
                    </tr>
                    <tr>
                        <td width="2%" style="font-size:10px;">Away</td>
                        <td width="100px;">
                            <img src="<?= $away_team->logo; ?>">
                        </td>
                        <td style="padding-right:0px;">
                            <div class="scores" id="scores_away">
                                <?php foreach($away_team_scores as $ats): ?>
                                    <div class="score score_edit" onclick="<?= current_user_can('administrator') ? ('editScore('.$ats->id.','.$away_team->id.','.$ats->points.',\''.$ats->scorer.'\')') : null; ?>">
                                        <p class="score_points"><?= $ats->points; ?></p>
                                        <p class="score_name"><?= $ats->scorer; ?></p>
                                    </div>
                                <?php endforeach; ?>
                                <div class="score">
                                    <button onclick="newScore(<?= $away_team->id; ?>)" class="btn btn-primary" style="height:100%;width:100%;">+</button>
                                </div>
                            </div>
                        </td>
                        <td width="100px;">
                            <?= $away_team_scores_total; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php if( current_user_can('administrator') ): ?>
                <form method="POST" id="generate_period_values">
                    <input type="hidden" name="game_id" value="<?= $game->id ?>">
                    <input type="hidden" name="period_id" value="<?= $period->id ?>">
                    <input type="hidden" name="submit_generate_period_values">
                </form>
                <form method="POST" id="create_next_period">
                    <input type="hidden" name="game_id" value="<?= $game->id ?>">
                    <input type="hidden" name="period_id" value="<?= $period->id ?>">
                    <input type="hidden" name="submit_create_next_period">
                </form>

                <div class="modal fade" id="modalScoreManage" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title w-100 font-weight-bold" id="score_manager_title">New score</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" id="scoreManagerForm">
                                    <div class="form-group">
                                        <label for="points">Points</label>
                                        <input type="number" class="form-control" id="points" name="points" placeholder="Points scored">
                                    </div>
                                    <div class="form-group">
                                        <label for="scorer">By who?</label>
                                        <input type="text" class="form-control" id="scorer" name="scorer" placeholder="Who scored the points?">
                                    </div>

                                    <input type="hidden" name="period_id" value="<?= $period->id ?>">
                                    <input type="hidden" name="team_id">
                                    <input type="hidden" name="score_id">
                                    <input type="hidden" name="submit_create_score" id="scoreManagerForm_type">
                                </form>
                                <form method="POST" id="scoreDeleteForm">
                                    <input type="hidden" name="score_id">
                                    <input type="hidden" name="submit_delete_score">
                                </form>
                            </div>
                            <div class="modal-footer d-flex justifty-content-center">
                                <button class="btn btn-danger" onclick="deleteScore()" id="score_manager_delete">Delete</button>
                                <button class="btn btn-primary" onclick="jQuery('#scoreManagerForm').submit();" id="score_manager_submit">Create</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    if ( window.history.replaceState ) {
                        window.history.replaceState( null, null, window.location.href );
                    }
                    function newScore(team_id) {
                        // console.log("New score for team iD:",team_id);
                        jQuery('#score_manager_delete').hide();
                        jQuery('#scoreManagerForm_type').prop('name', 'submit_create_score');
                        jQuery('#score_manager_submit').text('Create');
                        jQuery('#score_manager_title').text('New score');
                        jQuery('#points').val(null);
                        jQuery('#scorer').val('');
                        jQuery('#scoreManagerForm > input[name="team_id"]').val(team_id);
                        jQuery('#modalScoreManage').modal();
                    }
                    function editScore(score_id, team_id, points, scorer) {
                        // console.log('editScore for score_id:',score_id,' and team_id:',team_id,' and points:',points,' and scorer:',scorer);
                        jQuery('#scoreManagerForm_type').prop('name', 'submit_update_score');
                        jQuery('#score_manager_delete').show();
                        jQuery('#scoreManagerForm > input[name="team_id"]').val(team_id);
                        jQuery('#scoreDeleteForm > input[name="score_id"]').val(score_id);
                        jQuery('#scoreManagerForm > input[name="score_id"]').val(score_id);
                        jQuery('#points').val(points);
                        jQuery('#scorer').val(scorer);
                        jQuery('#score_manager_submit').text('Update');
                        jQuery('#score_manager_title').text('Edit score');
                        jQuery('#modalScoreManage').modal();
                    }
                    function deleteScore() {
                        if(confirm("Are you sure that you want to delete this score? This cannot be undone.")) {
                            jQuery('#scoreDeleteForm').submit();
                        }
                    }
                </script>
            <?php endif; ?>
            <form method="POST" action="" id="create_stripe_charge">
                <input type="hidden" name="game_id" value="<?= $game->id ?>">
                <input type="hidden" name="coordinate" value="">
                <input type="hidden" name="action" value="stripe"/>
                <input type="hidden" name="redirect" value="<?php echo get_permalink(); ?>"/>
                <input type="hidden" name="stripe_nonce" value="<?php echo wp_create_nonce('stripe-nonce'); ?>"/>
            </form>
            <script>
                window.addEventListener('load', function() {
                    var handler = StripeCheckout.configure({
                        key: "<?= get_option('dls_squares_spk') ?>",
                        image: 'https://stripe.com/img/documentation/checkout/marketplace.png',
                        locale: 'auto',
                        email: "<?= $current_user->data->user_email ?>",
                        token: function(token) {
                            console.log('creating charge...', token);
                            // You can access the token ID with `token.id`.
                            // Get the token ID to your server-side code for use.
                            var form$ = jQuery('#create_stripe_charge');
                            form$.append("<input type='hidden' name='stripeToken' value='"+token.id+"' />");
                            form$.submit();
                        }
                    });
                    window.addEventListener('popstate', function() {
                        handler.close();
                    });
                    jQuery('.dls-square').click(function(e) {
                        var home_column = jQuery(this).data('homecol');
                        var away_column = jQuery(this).data('awaycol');
                        jQuery('#create_stripe_charge>input[name="coordinate"]').val(home_column+","+away_column);
                        handler.open({
                            name: "Sqauares",
                            description: ("Purchase square ("+home_column+","+away_column+")"),
                            amount: <?= $game->square_cost * 100; ?>
                        })
                    });


                    jQuery('select#game').change(function(){
                        var uri = DLS_HELPERS.url.removeAllParameters();
                        window.location.href = DLS_HELPERS.url.updateQueryStringParameter(uri, 'game', this.value);
                    });
                    jQuery('select#period').change(function(e){
                        <?php if( current_user_can('administrator') ): ?>
                            if(this.value == "create_next_period"){
                                if (confirm("Are you sure that you want to create the next period?")) {
                                    jQuery('form#create_next_period').submit();
                                }
                                else {
                                    e.preventDefault();
                                }
                            }
                            else if(this.value == "generate_period_values"){
                                if (confirm("Are you sure that you want to generate the values for this period?")) {
                                    jQuery('form#generate_period_values').submit();
                                }
                                else {
                                    e.preventDefault();
                                }
                            }
                            else{
                                window.location.href = DLS_HELPERS.url.updateQueryStringParameter(window.location.href, 'period', this.value);
                            }
                        <?php else: ?>                            
                            window.location.href = DLS_HELPERS.url.updateQueryStringParameter(window.location.href, 'period', this.value);
                        <?php endif; ?>
                    });
                    
                });
            </script>
        </div>
        <script>
            var DLS_HELPERS = {
                url: {
                    updateQueryStringParameter: function (uri, key, value) {
                        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
                        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
                        if (uri.match(re)) {
                            return uri.replace(re, '$1' + key + "=" + value + '$2');
                        }
                        else {
                            return uri + separator + key + "=" + value;
                        }
                    },
                    removeAllParameters: function() {
                        return window.location.origin + window.location.pathname;
                    },
                    removeURLParameter: function (url, parameter) {
                        //prefer to use l.search if you have a location/link object
                        var urlparts= url.split('?');   
                        if (urlparts.length>=2) {

                            var prefix= encodeURIComponent(parameter)+'=';
                            var pars= urlparts[1].split(/[&;]/g);

                            //reverse iteration as may be destructive
                            for (var i= pars.length; i-- > 0;) {    
                                //idiom for string.startsWith
                                if (pars[i].lastIndexOf(prefix, 0) !== -1) {  
                                    pars.splice(i, 1);
                                }
                            }

                            return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
                        }
                        return url;
                    }
                }
            }
        </script>
    <?php
}
if ( !is_admin() ) {
    add_shortcode('dls_squares', 'dls_squares');
}
//dls_squares helpers
function render_column_number_cell($column, $side, $value = '?')
{
    ?>
        <td class="column-number column-number-<?php echo $column; ?>" data-side="<?php echo $side; ?>" data-column="<?php echo $column; ?>">
            <?= $value ?>
        </td>
    <?php
}
//============================================== FRONTEND : END ======================================================


?>