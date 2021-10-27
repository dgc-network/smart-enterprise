<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('badges')) {

    class badges {

        /**
         * Class constructor
         */
        public function __construct() {
            add_shortcode('badge_list', __CLASS__ . '::list_mode');
            add_shortcode('badge-list', __CLASS__ . '::list_mode');
            self::create_tables();
            wp_insert_term( 'Badges', 'product_cat', array(
                'description' => 'Description for category', // optional
                'parent' => 0, // optional
                'slug' => 'badges' // optional
            ) );
            
        }

        function user_badges( $_id=null ) {

            if ($_id==null){
                return '<div>ID is required</div>';
            }

            /** 
             * submit
             */
            if( isset($_POST['submit_action']) ) {        
                $current_user_id = get_current_user_id();
                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_badges WHERE u_b_id = {$_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if (( $_POST['_badge_id_'.$index]=='select_delete' )){
                        $table = $wpdb->prefix.'user_badges';
                        $where = array(
                            'u_b_id' => $results[$index]->u_b_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'user_badges';
                        $data = array(
                            'badge_id' => $_POST['_badge_id_'.$index],
                        );
                        $where = array(
                            'u_b_id' => $results[$index]->u_b_id
                        );
                        $wpdb->update( $table, $data, $where );    
                    }
                }

                if ( !($_POST['_badge_id']=='no_select') ){
                    $table = $wpdb->prefix.'user_badges';
                    $data = array(
                        'badge_id' => $_POST['_badge_id'],
                        'student_id' => $_POST['_student_id'],
                    );
                    $format = array('%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }

            }

            /** 
             * user_badges header
             */
            $output  = '<h2>個人認證項目</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Name:'.'</td><td>'.get_userdata($_id)->display_name.'</td></tr>';
            $output .= '<tr><td>'.'Email:'.'</td><td>'.get_userdata($_id)->user_email.'</td></tr>';
            $output .= '</tbody></table></figure>';
            //return $output;

            /** 
             * user_badges relationship
             */
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}user_badges WHERE student_id = {$_id}", OBJECT );
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'#'.'</td><td>Badges</td></tr>';
            foreach ($results as $index => $result) {
                $output .= '<tr><td>'.($index+1).'</td>';                
                $output .= '<td>'.'<select name="_badge_id_'.$index.'">'.self::select_options($results[$index]->badge_id).'</select></td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td>'.'#'.'</td>';
            $output .= '<td>'.'<select name="_badge_id">'.self::select_options().'</select>'.'</td>';
            $output .= '<input type="hidden" name="_student_id" value="'.$_id.'">';
            $output .= '</tr>';
            $output .= '</tbody></table></figure>';
            
            /** 
             * user_badges footer
             */
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Submit" name="submit_action">';
            $output .= '</div>';
            $output .= '</form>';
            $output .= '<form method="get">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';

            return $output;
        }

        function list_mode() {
            
            if( isset($_GET['view_mode']) ) {
                if ($_GET['view_mode']=='user_badges') return self::user_badges($_GET['_id']);
            }

            /**
             * List Mode
             */
            global $wpdb;
            $badges = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}badges", OBJECT );
            $members = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}members", OBJECT );
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}member_badges WHERE student_id = {$results[$index]->ID} AND badge_id = {$loop->post->ID}", OBJECT );
            $output  = '<h2>教師考取相關證照紀錄</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>證照紀錄</td>';
            foreach ($badges as $index => $badge) {
                $output .= '<td><a href="?view_mode=badge&_id='.$badge->badge_id.'">'.$badge->badge_title.'</a></td>';
            }
            $output .= '</tr>';
            foreach ($members as $index => $member) {
                $output .= '<tr>';
                $output .= '<td><a href="?view_mode=user_badges&_id='.$member->member_id.'">'.$member->member_name.'</a></td>';
                foreach ($badges as $index => $badge) {
                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}member_badges WHERE member_id = {$member->member_id} AND badge_id = {$badge->badge_id}", OBJECT );
                    if (empty($row)) {
                        $output .= '<td></td>';
                    } else {
                        $output .= '<td><img src="'.$badge->image_link.'" data-id="'.$badge->badge_id.'"></td>';
                    }
                }
                $output .= '</tr>';
            }
            $output .= '</tbody></table></figure>';
/*
            $args = array(
                'post_type'     => 'product',
                'product_cat'   => 'Badges',
                'posts_per_page'=> 200,
                'order'         => 'ASC'
            );
                
            $output  = '<h2>教師考取相關證照紀錄</h2>';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>證照紀錄</td>';
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                $output .= '<td><a href="?view_mode=badge&_id='.$product->get_id().'">'.$product->get_name().'</a></td>';
            endwhile;
            $output .= '</tr>';

            $results = get_users();
            foreach ($results as $index => $result) {
                $output .= '<tr>';
                $output .= '<td><a href="?view_mode=user_badges&_id='.$results[$index]->ID.'">'.$results[$index]->display_name.'</a></td>';
                while ( $loop->have_posts() ) : $loop->the_post();
                    global $wpdb;
                    $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}user_badges WHERE student_id = {$results[$index]->ID} AND badge_id = {$loop->post->ID}", OBJECT );
                    if (empty($row)) {
                        $output .= '<td></td>';
                    } else {
                        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $loop->post->ID ), 'single-post-thumbnail' );
                        $output .= '<td><img src="'.$image[0].'" data-id="'.$loop->post->ID.'"></td>';
                    }
                endwhile;
                $output .= '</tr>';
            }
            wp_reset_query();
            $output .= '</tbody></table></figure>';
*/
            $output .= '<form method="get">';
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<a class="wp-block-button__link" href="/wp-admin/post-new.php?post_type=product">Create</a>';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
            $output .= '</div>';
            $output .= '</div>';
            $output .= '</form>';
            return $output;
        }
        
        function select_options( $default_id=null ) {

            $args = array(
                'post_type'     => 'product',
                'product_cat'   => 'Badges',
                'posts_per_page'=> 200,
                'order'         => 'ASC'
            );       
        
            $output = '<option value="no_select">-- Select an option --</option>';
            $loop = new WP_Query( $args );
            while ( $loop->have_posts() ) : $loop->the_post();
                global $product;
                if ( $product->get_id() == $default_id ) {
                    $output .= '<option value="'.$product->get_id().'" selected>';
                } else {
                    $output .= '<option value="'.$product->get_id().'">';
                }
                $output .= $product->get_name();
                $output .= '</option>';        
            endwhile;
            wp_reset_query();
            $output .= '<option value="delete_select">-- Remove this --</option>';

            return $output;
        }

        function select_users( $default_id=null ) {

            $results = get_users();
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($results as $index => $result) {
                if ( $results[$index]->ID == $default_id ) {
                    $output .= '<option value="'.$results[$index]->ID.'" selected>';
                } else {
                    $output .= '<option value="'.$results[$index]->ID.'">';
                }
                $output .= $results[$index]->display_name;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';
            return $output;    
        }

        function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE `{$wpdb->prefix}members` (
                member_id int NOT NULL AUTO_INCREMENT,
                member_name varchar(255),
                member_title varchar(255),
                member_link varchar(255),
                is_teacher boolean,
                txid varchar(255),
                PRIMARY KEY  (member_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}badges` (
                badge_id int NOT NULL AUTO_INCREMENT,
                badge_title varchar(255),
                badge_link varchar(255),
                image_link varchar(255),
                txid varchar(255),
                PRIMARY KEY  (badge_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}member_badges` (
                m_b_id int NOT NULL AUTO_INCREMENT,
                member_id int NOT NULL,
                badge_id int NOT NULL,
                txid varchar(255),
                PRIMARY KEY  (m_b_id)
            ) $charset_collate;";        
            dbDelta($sql);

            $sql = "CREATE TABLE `{$wpdb->prefix}user_badges` (
                u_b_id int NOT NULL AUTO_INCREMENT,
                student_id int NOT NULL,
                badge_id int NOT NULL,
                txid varchar(255),
                PRIMARY KEY  (u_b_id)
            ) $charset_collate;";        
            dbDelta($sql);

        }        
    }
    //if ( is_admin() )
    new badges();
}
?>