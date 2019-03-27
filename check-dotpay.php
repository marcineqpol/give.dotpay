<?php 
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
	date_default_timezone_set(' Europe/Warsaw');
	$blogtime = current_time( 'mysql' ); 
 
	if($_POST['operation_number']){

		if($_POST['operation_status'] == 'completed'){

			$control = $_POST['control'];

			global $wpdb;
            $table = $wpdb->prefix."give_donationmeta";
            $result = $wpdb->get_results('SELECT * FROM '.$table.' WHERE meta_value = "'.$control.'" LIMIT 1');
	            foreach($result as $single_item){
	            	give_update_payment_status($single_item->donation_id, 'publish');
	            	echo 'OK';
	            }
			 

		}

		if($_POST['operation_status'] == 'rejected'){

			$control = $_POST['control'];

			global $wpdb;
            $table = $wpdb->prefix."give_donationmeta";
            $result = $wpdb->get_results('SELECT * FROM '.$table.' WHERE meta_value = "'.$control.'" LIMIT 1');
	            foreach($result as $single_item){
	            	give_update_payment_status($single_item->donation_id, 'failed');
	            	echo 'OK';
	            }
			 

		}
	}
?>