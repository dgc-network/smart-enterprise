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
/*            
            wp_insert_term( 'Badges', 'product_cat', array(
                'description' => 'Description for category', // optional
                'parent' => 0, // optional
                'slug' => 'badges' // optional
            ) );
*/            
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

        function edit_mode( $_mode=null , $_id=null ) {

            if ($_mode==null){
                $_mode='Create';
            }

            if ($_id==null){
                if ($_mode=='Create') {} else 
                return '_id is required';
            }
/*
            if( isset($_POST['create_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'courses';
                $data = array(
                    'created_date' => current_time('timestamp'), 
                    'course_title' => $_POST['_course_title'],
                    'list_price' => $_POST['_list_price'],
                    'sale_price' => $_POST['_sale_price'],
                );
                $format = array('%d', '%s', '%f', '%f');
                $insert_id = $wpdb->insert($table, $data, $format);

                $CreateCourseAction = new CreateCourseAction();                
                //$CreateCourseAction->setCourseId(intval($_POST['_course_id']));
                $CreateCourseAction->setCourseId(intval($insert_id));
                $CreateCourseAction->setCourseTitle($_POST['_course_title']);
                $CreateCourseAction->setCreatedDate(intval(current_time('timestamp')));
                //$CreateCourseAction->setListPrice(floatval($_POST['_list_price']));
                //$CreateCourseAction->setSalePrice(floadval($_POST['_sale_price']));
                $CreateCourseAction->setPublicKey($_POST['_public_key']);
                $send_data = $CreateCourseAction->serializeToString();

                $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
            
                if (isset($op_result['error'])) {

                    $result_output = 'Error: '.$op_result['error']."\n";
                    return $result_output;
                } else {

                    $table = $wpdb->prefix.'courses';
                    $data = array(
                        'txid' => $op_result['txid'], 
                    );
                    $where = array('course_id' => $insert_id);
                    $wpdb->update( $table, $data, $where );
                }

                ?><script>window.location='/courses'</script><?php
            }
*/        
            if( isset($_POST['update_action']) ) {
/*        
                $UpdateCourseAction = new UpdateCourseAction();                
                $UpdateCourseAction->setCourseId(intval($_POST['_course_id']));
                $UpdateCourseAction->setCourseTitle($_POST['_course_title']);
                $UpdateCourseAction->setCreatedDate(intval(strtotime($_POST['_created_date'])));
                //$UpdateCourseAction->setListPrice(floatval($_POST['_list_price']));
                //$UpdateCourseAction->setSalePrice(floatval($_POST['_sale_price']));
                $UpdateCourseAction->setPublicKey($_POST['_public_key']);
                $send_data = $UpdateCourseAction->serializeToString();

                $op_result = OP_RETURN_send(OP_RETURN_SEND_ADDRESS, OP_RETURN_SEND_AMOUNT, $send_data);
*/            
                if (isset($op_result['error'])) {
                    $result_output = 'Error: '.$op_result['error']."\n";
                    return $result_output;
                } else {

                    global $wpdb;
                    $table = $wpdb->prefix.'calendars';
                    $data = array(
                        'event_title' => $_POST['_event_title'],
                        'event_begin' => $_POST['_event_begin'],
                        'event_end' => $_POST['_event_end'],
                        //'txid' => $op_result['txid'], 
                    );
                    $where = array('event_id' => $_POST['_event_id']);
                    $wpdb->update( $table, $data, $where );
                }

                ?><script>window.location='/calendars'</script><?php
            }
        
            if( isset($_POST['delete_action']) ) {
        
                global $wpdb;
                $table = $wpdb->prefix.'calendars';
                $where = array('event_id' => $_POST['_event_id']);
                $deleted = $wpdb->delete( $table, $where );
                ?><script>window.location='/calendars'</script><?php
            }

            /** 
             * edit_mode
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}badges WHERE badge_id = {$_id}", OBJECT );
            $output  = '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_badge_title" value="'.$row->badge_title.'"></td></tr>';
            $output .= '<tr><td>'.'Begin:'.'</td><td><input style="width: 100%" type="text" name="_badge_link" value="'.$row->badge_link.'"></td></tr>';
            $output .= '<tr><td>'.'End:'.'</td><td><input style="width: 100%" type="text" name="_image_link" value="'.$row->image_link.'"></td></tr>';
/*
            if( $_mode=='Update' ) {
                $output .= '<tr><td>'.'Name:'.'</td><td><input style="width: 100%" type="text" name="_display_name" value="'.get_orderdata($_id)->display_name.'"></td></tr>';
                $output .= '<tr><td>'.'Email:'.'</td><td><input style="width: 100%" type="text" name="_order_email" value="'.get_orderdata($_id)->order_email.'"></td></tr>';
            } else if( $_mode=='Delete' ) {
                $output .= '<tr><td>'.'Name:'.'</td><td><input style="width: 100%" type="text" name="_display_name" value="'.get_orderdata($_id)->display_name.'" disabled></td></tr>';
                $output .= '<tr><td>'.'Email:'.'</td><td><input style="width: 100%" type="text" name="_order_email" value="'.get_orderdata($_id)->order_email.'" disabled></td></tr>';
            } else {
                $output .= '<tr><td>'.'Name:'.'</td><td><input style="width: 100%" type="text" name="_display_name" value=""></td></tr>';
                $output .= '<tr><td>'.'Email:'.'</td><td><input style="width: 100%" type="text" name="_order_email" value=""></td></tr>';
            }
*/            
            $output .= '</tbody></table></figure>';
    
            $output .= '<div class="wp-block-buttons">';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_action">';
            $output .= '</div>';
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="delete_action">';
/*            
            if( $_mode=='Update' ) {
                //$output .= '<input class="wp-block-button__link" type="submit" value="Update" name="update_action">';
            } else if( $_mode=='Delete' ) {
                //$output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="delete_action">';
            } else {
                //$output .= '<input class="wp-block-button__link" type="submit" value="Create" name="create_action">';
            }
*/            
            $output .= '</div>';
/*            
            $output .= '<div class="wp-block-button">';
            $output .= '<input class="wp-block-button__link" type="submit" value="Cancel"';
            $output .= '</div>';
*/            
            $output .= '</div>';
            $output .= '</form>';
        
            return $output;
        }

        function list_mode() {
            
            if( isset($_GET['view_mode']) ) {
                if ($_GET['view_mode']=='user_badges') return self::user_badges($_GET['_id']);
            }

            if( isset($_GET['edit_mode']) ) {
/*                
                if ($_GET['edit_mode']=='Create') {
                    add_product_to_cart();
                    ?><script>window.location='/checkout'</script><?php
                }
*/                
                return self::edit_mode( $_POST['edit_mode'], $_POST['_id'] );
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