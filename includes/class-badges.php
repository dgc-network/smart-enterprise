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
            add_shortcode('teacher-badge-list', __CLASS__ . '::teacher_list_mode');
            add_shortcode('student-badge-list', __CLASS__ . '::student_list_mode');
            self::create_tables();
        }

        public static function member_badges_mode( $_id=null ) {

            if ($_id==null){
                return '<div>ID is required</div>';
            }

            if( isset($_POST['submit_action']) ) {        

                global $wpdb;
                $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}member_badges WHERE member_id = {$_id}", OBJECT );
                foreach ($results as $index => $result) {
                    if (( $_POST['_badge_id_'.$index]=='delete_select' )){
                        $table = $wpdb->prefix.'member_badges';
                        $where = array(
                            'm_b_id' => $results[$index]->m_b_id
                        );
                        $wpdb->delete( $table, $where );    
                    } else {
                        $table = $wpdb->prefix.'member_badges';
                        $data = array(
                            'badge_id' => $_POST['_badge_id_'.$index],
                        );
                        $where = array(
                            'm_b_id' => $results[$index]->m_b_id
                        );
                        $wpdb->update( $table, $data, $where );    
                    }
                }

                if ( !($_POST['_badge_id']=='no_select') ){
                    $table = $wpdb->prefix.'member_badges';
                    $data = array(
                        'badge_id' => $_POST['_badge_id'],
                        'member_id' => $_POST['_member_id'],
                    );
                    $format = array('%d', '%d');
                    $wpdb->insert($table, $data, $format);
                }

            }

            /** 
             * member_badges header
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}members WHERE member_id = {$_id}", OBJECT );
            $output  = '<h2>個人認證項目</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Name:'.'</td><td>'.$row->member_name.'</td></tr>';
            $output .= '<tr><td>'.'Title:'.'</td><td>'.$row->member_title.'</td></tr>';
            $output .= '</tbody></table></figure>';

            /** 
             * member_badges body
             */
            global $wpdb;
            $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}member_badges WHERE member_id = {$_id}", OBJECT );
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'#'.'</td><td>Badges</td></tr>';
            foreach ($results as $index => $result) {
                $output .= '<tr><td>'.($index+1).'</td>';                
                $output .= '<td>'.'<select name="_badge_id_'.$index.'">'.self::select_badges($results[$index]->badge_id).'</select></td>';
                $output .= '</tr>';
            }
            $output .= '<tr><td>'.'#'.'</td>';
            $output .= '<td>'.'<select name="_badge_id">'.self::select_badges().'</select>'.'</td>';
            $output .= '<input type="hidden" name="_member_id" value="'.$_id.'">';
            $output .= '</tr>';
            $output .= '</tbody></table></figure>';
            
            /** 
             * member_badges footer
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

        public static function badge_edit_mode( $_mode=null , $_id=null ) {

            if ($_mode==null){
                $_mode='Create';
                $_id=0;
            }

            if ($_id==null){
                if ($_mode=='Create') {} else return 'id is required';                
            }

            if( isset($_POST['submit_action']) ) {
                if( $_POST['submit_action']=='Create' ) {
        
                    global $wpdb;          
                    $table = $wpdb->prefix.'badges';
                    $data = array(
                        'badge_title' => $_POST['_badge_title'],
                        'badge_link' => $_POST['_badge_link'],
                        'image_link' => $_POST['_image_link'],
                    );
                    $format = array('%s', '%s', '%s');
                    $insert_id = $wpdb->insert($table, $data, $format);
    /*
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
                        $table = $wpdb->prefix.'badges';
                        $data = array(
                            'txid' => $op_result['txid'], 
                        );
                        $where = array('course_id' => $insert_id);
                        $wpdb->update( $table, $data, $where );
                    }
*/                    
                }
    
                if( $_POST['submit_action']=='Update' ) {
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
                        $table = $wpdb->prefix.'badges';
                        $data = array(
                            'badge_title' => $_POST['_badge_title'],
                            'badge_link' => $_POST['_badge_link'],
                            'image_link' => $_POST['_image_link'],
                            //'txid' => $op_result['txid'], 
                        );
                        $where = array('badge_id' => $_id);
                        $wpdb->update( $table, $data, $where );
                    }
                }
            
                if( $_POST['submit_action']=='Delete' ) {
            
                    global $wpdb;
                    $table = $wpdb->prefix.'badges';
                    $where = array('badge_id' =>  $_id);
                    $deleted = $wpdb->delete( $table, $where );
                }

                ?><script>window.location=window.location.pathname</script><?php
            }

            /** 
             * edit_mode
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}badges WHERE badge_id = {$_id}", OBJECT );
            $output  = '<h2>證照維護</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_badge_title" value="'.$row->badge_title.'"></td></tr>';
            $output .= '<tr><td>'.'Link:'.'</td><td><input style="width: 100%" type="text" name="_badge_link" value="'.$row->badge_link.'"></td></tr>';
            $output .= '<tr><td>'.'Image:'.'</td><td><input style="width: 100%" type="text" name="_image_link" value="'.$row->image_link.'"></td></tr>';
            $output .= '</tbody></table></figure>';
    
            if( $_mode=='Create' ) {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</form>';
            return $output;
        }

        public static function member_edit_mode( $_mode=null , $_id=null ) {

            if ($_mode==null){
                $_mode='Create';
                $_id=0;
            }

            if ($_id==null){
                if ($_mode=='Create') {} else return 'id is required';                
            }

            if( isset($_POST['submit_action']) ) {
                if( $_POST['submit_action']=='Create' ) {
        
                    global $wpdb;          
                    $table = $wpdb->prefix.'members';
                    $data = array(
                        'member_name' => $_POST['_member_name'],
                        'member_title' => $_POST['_member_title'],
                        'member_link' => $_POST['_member_link'],
                        'is_teacher' => rest_sanitize_boolean($_POST['_is_teacher']),
                    );
                    $format = array('%s', '%s', '%s', '%d');
                    $insert_id = $wpdb->insert($table, $data, $format);
    /*
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
                        $table = $wpdb->prefix.'badges';
                        $data = array(
                            'txid' => $op_result['txid'], 
                        );
                        $where = array('course_id' => $insert_id);
                        $wpdb->update( $table, $data, $where );
                    }
*/                    
                }
    
                if( $_POST['submit_action']=='Update' ) {
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
                        $table = $wpdb->prefix.'members';
                        $data = array(
                            'member_name' => $_POST['_member_name'],
                            'member_title' => $_POST['_member_title'],
                            'member_link' => $_POST['_member_link'],
                            'is_teacher' => rest_sanitize_boolean($_POST['_is_teacher']),
                            //'txid' => $op_result['txid'], 
                        );
                        $where = array('member_id' => $_id);
                        $wpdb->update( $table, $data, $where );
                    }
                }
            
                if( $_POST['submit_action']=='Delete' ) {
            
                    global $wpdb;
                    $table = $wpdb->prefix.'members';
                    $where = array('member_id' =>  $_id);
                    $deleted = $wpdb->delete( $table, $where );
                }

                ?><script>window.location=window.location.pathname</script><?php
            }

            /** 
             * edit_mode
             */
            global $wpdb;
            $row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}members WHERE member_id = {$_id}", OBJECT );
            $output  = '<h2>人員維護</h2>';
            $output .= '<form method="post">';
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>'.'Name:'.'</td><td><input style="width: 100%" type="text" name="_member_name" value="'.$row->member_name.'"></td></tr>';
            $output .= '<tr><td>'.'Title:'.'</td><td><input style="width: 100%" type="text" name="_member_title" value="'.$row->member_title.'"></td></tr>';
            $output .= '<tr><td>'.'Link:'.'</td><td><input style="width: 100%" type="text" name="_member_link" value="'.$row->member_link.'"></td></tr>';
            $output .= '<tr><td>'.'is Teacher:'.'<td><input type="checkbox" name="_is_teacher"';
            if ($row->is_teacher) $output .= ' value="true" checked';
            $output .= '></td>';
            $output .= '</tbody></table></figure>';
    
            if( $_mode=='Create' ) {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Cancel" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            } else {
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Update" name="submit_action">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Delete" name="submit_action">';
                $output .= '</div>';
                $output .= '</div>';
            }
            $output .= '</form>';
            return $output;
        }

        public static function teacher_list_mode() {
            $isTeacher = '1';
            return self::list_mode( $isTeacher );
        }

        public static function student_list_mode() {
            $isTeacher = '0';
            return self::list_mode( $isTeacher );
        }

        public static function list_mode( $isTeacher ) {
/*
            if( isset($_GET['edit_mode']) ) {
                if ($_GET['edit_mode']=='Create Badge') return self::badge_edit_mode();
                if ($_GET['edit_mode']=='edit_badge') return self::badge_edit_mode( $_GET['edit_mode'], $_GET['_id'] );
                if ($_GET['edit_mode']=='Create Member') return self::member_edit_mode();
                if ($_GET['edit_mode']=='edit_member') return self::member_edit_mode( $_GET['edit_mode'], $_GET['_id'] );
                if ($_GET['edit_mode']=='member_badges') return self::member_badges_mode( $_GET['_id'] );
            }            
*/
            if( isset($_POST['edit_mode']) ) {
                if ($_POST['edit_mode']=='Create Badge') return self::badge_edit_mode();
                if ($_POST['edit_mode']=='edit_badge') return self::badge_edit_mode( $_POST['edit_mode'], $_POST['_id'] );
                if ($_POST['edit_mode']=='Create Member') return self::member_edit_mode();
                if ($_POST['edit_mode']=='edit_member') return self::member_edit_mode( $_POST['edit_mode'], $_POST['_id'] );
                if ($_POST['edit_mode']=='member_badges') return self::member_badges_mode( $_POST['_id'] );
            }            

            /**
             * List Mode
             */
            global $wpdb;
            $badges = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}badges", OBJECT );
            $members = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}members WHERE is_teacher={$isTeacher}", OBJECT );
            if ( $isTeacher=='1' ) {
                $output  = '<h2>教師考取相關證照紀錄</h2>';
            } else {
                $output  = '<h2>學生考取相關證照紀錄</h2>';
            }
            $output .= '<figure class="wp-block-table"><table><tbody>';
            $output .= '<tr><td>證照紀錄</td>';
            foreach ($badges as $index => $badge) {
                $output .= '<td><a href="?edit_mode=edit_badge&_id='.$badge->badge_id.'">'.$badge->badge_title.'</a></td>';
            }
            $output .= '</tr>';
            foreach ($members as $index => $member) {
                $output .= '<tr>';
                $output .= '<td><a href="?edit_mode=edit_member&_id='.$member->member_id.'">'.$member->member_name.'</a>';
                $output .= '(<a href="?edit_mode=member_badges&_id='.$member->member_id.'">'.$member->member_title.'</a>)</td>';
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
            
            $user = wp_get_current_user();
            $allowed_roles = array('editor', 'administrator', 'author');
            if( array_intersect($allowed_roles, $user->roles ) ) {

                //$output .= '<form method="get">';
                $output .= '<form method="post">';
                $output .= '<div class="wp-block-buttons">';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create Member" name="edit_mode">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<input class="wp-block-button__link" type="submit" value="Create Badge" name="edit_mode">';
                $output .= '</div>';
                $output .= '<div class="wp-block-button">';
                $output .= '<a class="wp-block-button__link" href="/">Cancel</a>';
                $output .= '</div>';
                $output .= '</div>';
                $output .= '</form>';
            }

            return $output;
        }
        
        public static function select_badges( $default_id=null ) {

            global $wpdb;
            $badges = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}badges", OBJECT );
        
            $output = '<option value="no_select">-- Select an option --</option>';
            foreach ($badges as $index => $badge) {
                if ( $badge->badge_id == $default_id ) {
                    $output .= '<option value="'.$badge->badge_id.'" selected>';
                } else {
                    $output .= '<option value="'.$badge->badge_id.'">';
                }
                $output .= $badge->badge_title;
                $output .= '</option>';        
            }
            $output .= '<option value="delete_select">-- Remove this --</option>';

            return $output;
        }

        public static function create_tables() {
        
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $sql = "CREATE TABLE `{$wpdb->prefix}badges` (
                badge_id int NOT NULL AUTO_INCREMENT,
                badge_title varchar(255),
                badge_link varchar(255),
                image_link varchar(255),
                txid varchar(255),
                PRIMARY KEY  (badge_id)
            ) $charset_collate;";        
            dbDelta($sql);

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

            $sql = "CREATE TABLE `{$wpdb->prefix}member_badges` (
                m_b_id int NOT NULL AUTO_INCREMENT,
                member_id int NOT NULL,
                badge_id int NOT NULL,
                txid varchar(255),
                PRIMARY KEY  (m_b_id)
            ) $charset_collate;";        
            dbDelta($sql);
        }        
    }
    new badges();
}
?>