<?php
/*
Plugin Name: Instant Contact Form
Plugin URI: https://jgbuilt.com/instant-contact-form/
Description: Simple contact form with database backup
Author: JGbuilt
Version: 1.0.3
Author URI: https://jgbuilt.com
Copyright 2022 Instant Contact Form (email : jerry@jgbuilt.com)
*/
//Script version
$jgblticf_version = '1.0.3'; 
//Paths
defined('ABSPATH') or die('No script kiddies please!');
//Include pluggable path for user permissions and nounce
include_once(ABSPATH . 'wp-includes/pluggable.php');
//Activation 
global $wpdb;
register_activation_hook( __FILE__, 'jgblticf__instantcontactform_activate' );
register_uninstall_hook( __FILE__, 'jgblticf_instantcontactform_uninstall' );
global $jgblticf_table;
$jgblticf_table = $wpdb->prefix."jgblt_instantcontactform";
//Create Table if it does not exist
function jgblticf__instantcontactform_activate(){
	global $wpdb;
	global $jgblticf_table;
	$sql = "CREATE TABLE $jgblticf_table (
        `ID` BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
		`name` varchar(255),
		`email` varchar(255), 
        `subject` varchar(255), 
        `body` TEXT NOT NULL, 
        `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE = MyISAM;";
	jgblticf_instantcontactform_db($jgblticf_table, $sql); 
}
//See if table exists
function jgblticf_instantcontactform_db($theTable, $sql){
    global $wpdb;
    if($wpdb->get_var("show tables like '". $theTable . "'") != $theTable) { 
	$wpdb->query($sql); 
    }
}
//Uninstall db and delete options upon delete
function jgblticf_instantcontactform_uninstall(){
	global $wpdb;
	global $jgblticf_table;
	$wpdb->query("DROP TABLE IF EXISTS $jgblticf_table");
	delete_option('jgblticf_new_email');
}
//Create Admin Menu
add_action('admin_menu', 'jgblticf_instantcontactform_adminmenu');
function jgblticf_instantcontactform_adminmenu(){
	$appName = 'Instant Contact Form';
	$appID = 'instant_form_plugin';
	add_menu_page($appName, $appName, 'administrator', $appID, 'jgblticf_instantcontactform_admin');
	add_submenu_page($appID, $appName,'Options', 'administrator', $appID . '-options', 'jgblticf_instantcontactform_options');
}
//custom
function jgblticf_instantcontactformCustom(){
	global $jgblticf_admin;

	$jgblticf_admin = get_option('jgblticf_new_email');
	
	if(!$jgblticf_admin){
		$jgblticf_admin = get_option('admin_email');
	}
}	
//header
function jgblticf_instantcontactformHeader(){
	global $wpdb;
	global $jgblticf_table;
	global $jgblticf_version;
	global $jgblticf_db_emails;
	global $jgblticf_rows;
	global $jgblticf_admin;
	jgblticf_instantcontactformCustom();
	$jgblticf_db_emails = $wpdb->get_results("SELECT * FROM $jgblticf_table");
	$jgblticf_rows = count($jgblticf_db_emails);
?>
	<h1>Instant Contact Form</h1>
	<p><span class="tsmobilemenu">Messages: <?php echo esc_html($jgblticf_rows); ?> | Recipient: <?php echo esc_html($jgblticf_admin); ?> | <a href="https://jgbuilt.com/instant-contact-form/?utm_source=plugin&utm_medium=instructions&utm_campaign=icf-plugin" target="_blank" />Instructions</a> | Version: <?php echo esc_html($jgblticf_version); ?> by</span> <a href="https://jgbuilt.com" target="_blank">jgbuilt.com</a></p>
<?php
}
//reset table
if(isset($_REQUEST['DeleteICFRecords'])) {
	jgblticf_delete_records();
}
function jgblticf_delete_records(){
// check nonce
	if (!isset( $_POST['jgblticf_nonce_delete_field'] ) || !wp_verify_nonce( $_POST['jgblticf_nonce_delete_field'], 'jgblticf_delete_action' )
	) {
		die( __('Security check', 'textdomain' ));
	}else{ 
		global $wpdb;
		global $jgblticf_table;
		$wpdb->query("TRUNCATE TABLE $jgblticf_table");
	}
}
//delete single message
if(isset($_REQUEST['DeleteICFsingleRecord'])) {
	jgblticf_delete_single_record();
}
function jgblticf_delete_single_record(){
// check nonce
	if (!isset( $_POST['jgblticf_nonce_delete_field'] ) || !wp_verify_nonce( $_POST['jgblticf_nonce_delete_field'], 'jgblticf_delete_action' )
	) {
		die( __('Security check', 'textdomain' ));
	}else{ 
		global $wpdb;
		global $jgblticf_table;
		$deletemsg = $_REQUEST['ICFsingleRecordID'];
		$wpdb->query("DELETE FROM $jgblticf_table WHERE `ID` = $deletemsg");
	}
}

//New email address
if(isset($_REQUEST['submitICFnew_email'])) {
    jgblticf_update_email();
}
function jgblticf_update_email() {
// check nonce
    if (!isset( $_POST['icf_new_email_nonce_class_field'] ) || !wp_verify_nonce( $_POST['icf_new_email_nonce_class_field'], 'icf_new_email_class_action' )
    ) {
        die( __('Security check', 'textdomain' ));
    }else{ 

		if(filter_var(sanitize_text_field($_REQUEST['icf_new_send_email']), FILTER_VALIDATE_EMAIL)){
        	update_option('jgblticf_new_email',sanitize_text_field($_REQUEST['icf_new_send_email']));
		}
    }
}
/*----------------ADMIN PAGE FUNCTION------------------*/ 
function jgblticf_instantcontactform_admin(){
	wp_enqueue_style('instantcontactform-css',plugins_url('_inc/instantcontactform-css.css', __FILE__));
	global $wpdb;
	global $jgblticf_table;
	global $jgblticf_version;
	global $jgblticf_db_emails;
	global $jgblticf_rows;
	jgblticf_instantcontactformHeader();
	?>
	<div class="icfWrap">
	<?php
	if($jgblticf_rows > 0){
	?>
		<br />
		<form method="post">	
			<?php echo wp_nonce_field( 'jgblticf_delete_action', 'jgblticf_nonce_delete_field' ); ?>
			<input class="button-primary icfDelAll" type="submit" name="DeleteICFRecords" value="Delete ALL Emails" onclick="return confirm('Are you sure?');" />
		</form>
		<br />
		<table class="ts">
			<tr>
			<th>Delete</th><th>Date</th><th>Name</th><th>Email</th><th>Subject</th><th>Message</th>
			</tr>
			<tr>	
	<?php
		foreach($jgblticf_db_emails as $jgblticf_db_email){
	?>		
			<td>
			<form method="post">	
			<?php echo wp_nonce_field( 'jgblticf_delete_action', 'jgblticf_nonce_delete_field' ); ?>
			<input type="hidden" id="custId" name="ICFsingleRecordID" value="<?php echo esc_html($jgblticf_db_email->ID); ?>">
			<input class="button-primary icfDelSngl" type="submit" name="DeleteICFsingleRecord" value="Delete" onclick="return confirm('Are you sure?');" />
			</form>
			</td><td>
			<p><?php echo esc_html(date("m-d-Y g:ia", strtotime($jgblticf_db_email->date))); ?></p>
			</td><td>
			<p><?php echo esc_html($jgblticf_db_email->name); ?></p>
			</td><td>
			<p><?php echo esc_html($jgblticf_db_email->email); ?></p>
			</td><td>
			<p><?php echo esc_html($jgblticf_db_email->subject); ?></p>
			</td><td>
			<p><?php echo wp_kses_post(wpautop(wp_unslash($jgblticf_db_email->body))); ?></p>
			</td></tr>
	<?php
		}
	}else{
	?>	
		<p>You have no emails</p>
	<?php
	}
	?>	
	</table>
	</div>
<?php
}
/*----------------END ADMIN PAGE FUNCTION------------------*/ 
/*----------------OPTIONS PAGE FUNCTION------------------*/ 

function jgblticf_instantcontactform_options(){
	wp_enqueue_style('instantcontactform-css',plugins_url('_inc/instantcontactform-css.css', __FILE__));
	global $jgblticf_admin;
	jgblticf_instantcontactformHeader();
?>	
	<div class="icfWrap">

		<form method="post">
		<p><strong>Optional</strong>: Instant Contact Form is sending messages to <strong><?php echo esc_html($jgblticf_admin); ?></strong>. If you would like it to send them to a different email address, enter it here:</p>	
		<input type="email" name="icf_new_send_email" placeholder="email@domain.com" required="">
		<?php wp_nonce_field( 'icf_new_email_class_action', 'icf_new_email_nonce_class_field' ); ?>
		<input class="button-primary" type="submit" name="submitICFnew_email" value="Save" />
		</form>
	</div>
<?php
}
/*----------------END OPTIONS PAGE FUNCTION------------------*/
//Data handling
function jgblticf_instantcontactform_processform(){
	global $wpdb;
	global $jgblticf_table;
	global $jgblticf_alert;
	global $jgblticf_first_name;
	global $jgblticf_last_name;
	global $jgblticf_email;
	global $jgblticf_subject;
	global $jgblticf_message;
	global $jgblticf_r1;
	global $jgblticf_r2;
	global $jgblticf_admin;
	jgblticf_instantcontactformCustom();

	if (!isset($_POST['jgblticf_nonce_process_field']) || !wp_verify_nonce($_POST['jgblticf_nonce_process_field'], 'jgblticf_process_action')){
        die( __('Security check', 'textdomain' ));
    }else{
		//assign
		$jgblticf_first_name = sanitize_text_field($_POST['jgblticf_firstname']);
		$jgblticf_last_name = sanitize_text_field($_POST['jgblticf_lastname']);
		$jgblticf_email = sanitize_text_field($_POST['jgblticf_email']);
		$jgblticf_subject = sanitize_text_field($_POST['jgblticf_subject']);
		$jgblticf_message = sanitize_textarea_field($_POST['jgblticf_message']);
		$jgblticf_r1f = sanitize_text_field($_POST['jgblticf_r1f']);
		$jgblticf_r2f = sanitize_text_field($_POST['jgblticf_r2f']);
		$jgblticf_c = sanitize_text_field($_POST['jgblticf_c']);
		
		//validation
		if(	(isset($jgblticf_first_name) && $jgblticf_first_name != '') && 
			(isset($jgblticf_last_name) && $jgblticf_last_name != '') &&
			(isset($jgblticf_email) && $jgblticf_email != '' && filter_var($jgblticf_email, FILTER_VALIDATE_EMAIL)) &&
			(isset($jgblticf_subject) && $jgblticf_subject != '') &&
			(isset($jgblticf_message) && $jgblticf_message != '') &&
			(isset($jgblticf_c) && $jgblticf_c == ($jgblticf_r1f + $jgblticf_r2f)) ){

				$jgblticf_websitename = get_option('blogname');
				$jgblticf_name = $jgblticf_first_name.' '.$jgblticf_last_name;
				$jgblticf_body = '<strong>Name:</strong> '.$jgblticf_name.'<br /><br />';
				$jgblticf_body .= '<strong>Email:</strong> '.$jgblticf_email.'<br /><br />';
				$jgblticf_body .= '<strong>Subject:</strong> '.$jgblticf_subject.'<br /><br />';
				$jgblticf_body .= '<strong>Message:</strong> '.nl2br($jgblticf_message);

				//process
				$to = $jgblticf_admin;
				$subject = $jgblticf_websitename.' contact form';
				$body = $jgblticf_body;
				$headers = array(
					'MIME-Version: 1.0',
					'Content-Type: text/html; charset=UTF-8',
					'From: '.$jgblticf_admin.'',
					'Reply-To: '.$jgblticf_name.' <'.$jgblticf_email.'>'
				);

				//send
				$jgblticf_mail = wp_mail($to, $subject, $body, implode("\r\n", $headers));
				if($jgblticf_mail){//all good
					$jgblticf_alert = true;
					//database
					global $post, $wpdb; //wordpress post and wpdb global object
					$currentdata['name'] = $jgblticf_name;
					$currentdata['email'] = $jgblticf_email;
					$currentdata['subject'] = $jgblticf_subject;
					$currentdata['body'] = $jgblticf_message;
					$currentdata['date'] = wp_date("Y-m-d H:i:s", null, wp_timezone());
					$wpdb->insert($jgblticf_table, $currentdata);//save the captured values
					//done
					$jgblticf_name = '';
					$jgblticf_first_name = '';
					$jgblticf_last_name = '';
					$jgblticf_email = '';
					$jgblticf_subject = '';
					$jgblticf_message = '';
					
				}else{//did not send
					$jgblticf_alert = false;								
				}
		}else{//did not validate
			$jgblticf_alert = false;
		}
	}	
}
/*----------------FRONT END------------------*/
add_shortcode('instant_contact_form', 'jgblticf_instantcontactform_sc');

function jgblticf_instantcontactform_sc(){
	wp_enqueue_style('instantcontactform-css',plugins_url('_inc/instantcontactform-css.css', __FILE__));
	global $jgblticf_alert;
	global $jgblticf_first_name;
	global $jgblticf_last_name;
	global $jgblticf_email;
	global $jgblticf_subject;
	global $jgblticf_message;
	global $jgblticf_r1;
	global $jgblticf_r2;
	$jgblticf_r1 = mt_rand(0,10);
	$jgblticf_r2 = mt_rand(0,10);
	
	if(isset($_POST['submiticfmessage'])){
		jgblticf_instantcontactform_processform();
	}
	ob_start(); ?>
	<div class="wrap">
<?php	
	if(isset($jgblticf_alert)){
		if($jgblticf_alert){
?>	
		<p class="icfSent">Message sent</p>
<?php
		}
		if(!$jgblticf_alert){
?>	
		<p class="icfFailed">Error: Message not sent</p>
<?php
		}
	}	
		//show form
?>
		<form method="post" class="icfForm">
		<label for="fname">First Name</label>
		<input type="text" id="jgblticf_fname" name="jgblticf_firstname" value="<?php if(isset($jgblticf_first_name)){echo $jgblticf_first_name;} ?>" placeholder="" required>
		<label for="lname">Last Name</label>
		<input type="text" id="jgblticf_lname" name="jgblticf_lastname" value="<?php if(isset($jgblticf_last_name)){echo $jgblticf_last_name;} ?>" placeholder="" required>
		<label for="lname">Email</label>
		<input type="email" id="jgblticf_email" name="jgblticf_email" value="<?php if(isset($jgblticf_email)){echo $jgblticf_email;} ?>" placeholder="" required>
		<label for="subject">Subject</label>
		<input type="text" id="jgblticf_subject" name="jgblticf_subject" value="<?php if(isset($jgblticf_subject)){echo $jgblticf_subject;} ?>" placeholder="" required>
		<label for="message">Message</label>
		<textarea id="jgblticf_message" name="jgblticf_message" placeholder="" style="height:200px;margin-bottom:10px;" required><?php if(isset($jgblticf_message)){echo $jgblticf_message;} ?></textarea>
		<label for="icf_c">What is <?php echo esc_html($jgblticf_r1); ?>+<?php echo esc_html($jgblticf_r2); ?></label>
		<input type="hidden" id="jgblticf_r1f" name="jgblticf_r1f" value="<?php echo esc_html($jgblticf_r1); ?>">
		<input type="hidden" id="jgblticf_r2f" name="jgblticf_r2f" value="<?php echo esc_html($jgblticf_r2); ?>">
		<input type="text" id="jgblticf_c" name="jgblticf_c" placeholder=""  maxlength="2" required>
		<?php echo wp_nonce_field('jgblticf_process_action', 'jgblticf_nonce_process_field'); ?>
		<input type="submit" name="submiticfmessage" value="Submit">
		</form>
		<?php return ob_get_clean(); ?>
	</div>
<?php	
}
/*----------------END FRONT END------------------*/ 	
?>