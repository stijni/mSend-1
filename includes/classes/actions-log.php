<?php
/**
 * Class that handles all the actions that are logged on the database.
 *
 * @package		ProjectSend
 * @subpackage	Classes
 *
 */


global $activities_references;
$activities_references = array(
							0	=> __('ProjecSend has been installed','cftp_admin'),
							1	=> __('Account logs in through the form','cftp_admin'),
							24	=> __('Account logs in through cookies','cftp_admin'),
							31	=> __('Account (user or client) logs out','cftp_admin'),
							2	=> __('A user creates a new user account','cftp_admin'),
							3	=> __('A user creates a new client account','cftp_admin'),
							4	=> __('A client registers an account for himself','cftp_admin'),
							5	=> __('A file is uploaded by an user','cftp_admin'),
							6	=> __('A file is uploaded by a client','cftp_admin'),
							7	=> __('A file is downloaded by a user (on "Client view" mode)','cftp_admin'),
							8	=> __('A file is downloaded by a client','cftp_admin'),
							9	=> __('A zip file was generated by a client','cftp_admin'),
							10	=> __('A file has been unassigned from a client.','cftp_admin'),
							11	=> __('A file has been unassigned from a group','cftp_admin'),
							12	=> __('A file has been deleted','cftp_admin'),
							13	=> __('A user was edited','cftp_admin'),
							14	=> __('A client was edited','cftp_admin'),
							15	=> __('A group was edited','cftp_admin'),
							16	=> __('A user was deleted','cftp_admin'),
							17	=> __('A client was deleted','cftp_admin'),
							18	=> __('A group was deleted','cftp_admin'),
							19	=> __('A client account was activated','cftp_admin'),
							20	=> __('A client account was deactivated','cftp_admin'),
							27	=> __('A user account was activated','cftp_admin'),
							28	=> __('A user account was deactivated','cftp_admin'),
							21	=> __('A file was marked as hidden','cftp_admin'),
							22	=> __('A file was marked as visible','cftp_admin'),
							23	=> __('A user creates a new group','cftp_admin'),
							25	=> __('A file is assigned to a client','cftp_admin'),
							26	=> __('A file is assigned to a group','cftp_admin'),
							27	=> __('A user account was marked as active','cftp_admin'), // TODO: check repetition
							28	=> __('A user account was marked as inactive','cftp_admin'),
							29	=> __('The logo on "Branding" was changed','cftp_admin'),
							30	=> __('ProjectSend was updated','cftp_admin'),
							32	=> __('A system user edited a file.','cftp_admin'),
							33	=> __('A client edited a file.','cftp_admin'),
							34	=> __('A system user  created a category.','cftp_admin'),
							35	=> __('A system user  edited a category.','cftp_admin'),
							36	=> __('A system user  deleted a category.','cftp_admin'),
							37	=> __('An anonymous user downloaded a public file.','cftp_admin'),
							38      => __('A file is uploaded and assigned by a guest','cftp_admin')
						);
 /**
 * More to be added soon.
 */

class LogActions
{

	var $action = '';

	/**
	 * Create a new client.
	 */
	function log_action_save($arguments)
	{
		global $dbh;
		global $global_name;
		$this->state = array();

		/** Define the account information */
		$this->action = $arguments['action'];
		$this->owner_id = $arguments['owner_id'];
		$this->owner_user = (!empty($arguments['owner_user'])) ? $arguments['owner_user'] : $global_name;
		$this->affected_file = (!empty($arguments['affected_file'])) ? $arguments['affected_file'] : '';
		$this->affected_account = (!empty($arguments['affected_account'])) ? $arguments['affected_account'] : '';
		$this->affected_file_name = (!empty($arguments['affected_file_name'])) ? $arguments['affected_file_name'] : '';
		$this->affected_account_name = (!empty($arguments['affected_account_name'])) ? $arguments['affected_account_name'] : '';
		$this->file_type = (!empty($arguments['file_type'])) ? $arguments['file_type'] : '';
// 		var_dump($this->file_type);die();
		
		/** Get the real name of the client or user */
		if (!empty($arguments['get_user_real_name'])) {
			$this->short_query = $dbh->prepare( "SELECT name FROM " . TABLE_USERS . " WHERE user =:user" );
			$params = array(
							':user'		=> $this->affected_account_name,
						);
			$this->short_query->execute( $params );
			$this->short_query->setFetchMode(PDO::FETCH_ASSOC);
			while ( $srow = $this->short_query->fetch() ) {
				$this->affected_account_name = $srow['name'];
			}
		}

		/** Get the title of the file on downloads */
		if (!empty($arguments['get_file_real_name'])) {
			$this->short_query = $dbh->prepare( "SELECT filename FROM " . TABLE_FILES . " WHERE url =:file" );
			$params = array(
							':file'		=> $this->affected_file_name,
						);
			$this->short_query->execute( $params );
			$this->short_query->setFetchMode(PDO::FETCH_ASSOC);
			while ( $srow = $this->short_query->fetch() ) {
				$this->affected_file_name = $srow['filename'];
			}
		}

		/** Insert the client information into the database */
		$lq = "INSERT INTO " . TABLE_LOG . " (action,owner_id,owner_user";
		
			if (!empty($this->affected_file)) { $lq .= ",affected_file"; }
			if (!empty($this->affected_account)) { $lq .= ",affected_account"; }
			if (!empty($this->affected_file_name)) { $lq .= ",affected_file_name"; }
			if (!empty($this->affected_account_name)) { $lq .= ",affected_account_name"; }
// 			if (!empty($this->file_type)) { $lq .= ",file_type"; }
		
		$lq .= ") VALUES (:action, :owner_id, :owner_user";

			$params = array(
							':action'		=> $this->action,
							':owner_id'		=> $this->owner_id,
							':owner_user'	=> $this->owner_user,
						);
		
			if (!empty($this->affected_file)) {			$lq .= ", :file";		$params['file'] = $this->affected_file; }
			if (!empty($this->affected_account)) {		$lq .= ", :account";	$params['account'] = $this->affected_account; }
			if (!empty($this->affected_file_name)) {	$lq .= ", :title";		$params['title'] = $this->affected_file_name; }
			if (!empty($this->affected_account_name)) {	$lq .= ", :name";		$params['name'] = $this->affected_account_name; }
// 			if (!empty($this->file_type)) {	$lq .= ", :name";		$params['name'] = $this->file_type; }

		$lq .= ")";

		$this->sql_query = $dbh->prepare( $lq );
// 		$this->sql_query->execute( $params );
		if($this->sql_query->execute( $params )){
		    $log_id = $dbh->lastInsertId();
		}
		return $log_id;
	}
}
?>