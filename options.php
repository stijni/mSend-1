<?php

/**

 * Options page and form.

 *

 * @package ProjectSend

 * @subpackage Options

 */









$load_scripts	= array(

			'jquery_tags_input',

			'spinedit',

		); 



$allowed_levels = array(9);

require_once('sys.includes.php');

$page_title = __('System options','cftp_admin');



$active_nav = 'options';

$cc_active_page = 'Options';

include('header.php');



if ($_POST) {

	/**

	 * Escape all the posted values on a single function.

	 * Defined on functions.php

	 */

	/** Values that can be empty */

	$allowed_empty_values	= array(

					'mail_copy_addresses',

					'mail_smtp_host',

					'mail_smtp_port',

					'mail_smtp_user',

					'mail_smtp_pass',

				);



	if ( empty( $_POST['google_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'google_client_id';

		$allowed_empty_values[] = 'google_client_secret';

	}

	if ( empty( $_POST['facebook_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'facebook_client_id';

		$allowed_empty_values[] = 'facebook_client_secret';

	}

	if ( empty( $_POST['twitter_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'twitter_client_id';

		$allowed_empty_values[] = 'twitter_client_secret';

	}

	if ( empty( $_POST['yahoo_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'yahoo_client_id';

		$allowed_empty_values[] = 'yahoo_client_secret';

	}

	if ( empty( $_POST['ldap_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'ldap_client_id';

		$allowed_empty_values[] = 'ldap_bind_dn';

		$allowed_empty_values[] = 'ldap_bind_password';

	}

	if ( empty( $_POST['office_365_signin_enabled'] ) ) {

		$allowed_empty_values[] = 'office_client_id';

	}

	if ( empty( $_POST['recaptcha_enabled'] ) ) {

		$allowed_empty_values[] = 'recaptcha_site_key';

		$allowed_empty_values[] = 'recaptcha_secret_key';

	}



	/** Checkboxes */

	$checkboxes	= array(

				'recaptcha_enabled',

				'clients_can_delete_own_files',

				'use_browser_lang',

				'clients_can_register',

				'clients_auto_approve',

				'clients_can_upload',

				'mail_copy_user_upload',

				'mail_copy_client_upload',

				'mail_drop_off_request',

				'mail_copy_main_user',

				'thumbnails_use_absolute',

				'pass_require_upper',

				'pass_require_lower',

				'pass_require_number',

				'pass_require_special',

			);

	foreach ($checkboxes as $checkbox) {

		$_POST[$checkbox] = (empty($_POST[$checkbox]) || !isset($_POST[$checkbox])) ? 0 : 1;

	}



	//echo "<pre>";print_r($_POST);exit();

	$keys = array_keys($_POST);

	 

	$options_total = count($keys);

	$options_filled = 0;

	$query_state = '0';



	/**

	 * Check if all the options are filled.

	 */

/*	for ($i = 0; $i < $options_total; $i++) {

		if (!in_array($keys[$i], $allowed_empty_values)) {

			if (empty($_POST[$keys[$i]]) && $_POST[$keys[$i]] != '0') {

				//echo "-----".$keys[$i]."</br>";

				$query_state = '3';

			}

			else {

				$options_filled++;

			}

		}

	}*/

	

	//exit();

	/** If every option is completed, continue */

	if ($query_state == '0') {

		$updated = 0;

		for ($j = 0; $j < $options_total; $j++) {

						$cc_val = $_POST[$keys[$j]];

			$cc_key = $keys[$j];

			if ($cc_key == 'google_client_id' || $cc_key == 'google_client_secret' || $cc_key == 'facebook_client_id' ||$cc_key == 'facebook_client_secret' || $cc_key == 'twitter_client_id' || $cc_key == 'twitter_client_secret' || $cc_key == 'yahoo_client_id' || $cc_key == 'yahoo_client_secret' || $cc_key == 'linkedin_client_id'|| $cc_key == 'linkedin_client_secret' || $cc_key == 'windows_client_id')  {

				if(!empty($cc_val)) {

					$aes = new AES($cc_val, ENCRYPTION_KEY, BLOCKSIZE);

					$cc_val = $aes->encrypt(); 

				}

			}

			$save = $dbh->prepare( "UPDATE " . TABLE_OPTIONS . " SET value=:value WHERE name=:name" );

			$save->bindParam(':value', $cc_val);

			$save->bindParam(':name', $cc_key);

			$save->execute();

			if ($save) {

				$updated++;

			}

		}

		if ($updated > 0){

			$query_state = '1';

		}

		else {

			$query_state = '2';

		}

	}



	/** Redirect so the options are reflected immediatly */

	while (ob_get_level()) ob_end_clean();

	$location = BASE_URI . 'options.php?status=' . $query_state;

	header("Location: $location");

	die();

}



/**

 * Replace | with , to use the tags system when showing

 * the allowed filetypes on the form. This value comes from

 * site.options.php

*/

$allowed_file_types = str_replace('|',',',$allowed_file_types);

/** Explode, sort, and implode the values to list them alphabetically */

$allowed_file_types = explode(',',$allowed_file_types);

sort($allowed_file_types);

$allowed_file_types = implode(',',$allowed_file_types);



// $guestallowed_file_types = str_replace('|',',',$guestallowed_file_types);

// /** Explode, sort, and implode the values to list them alphabetically */

// $guestallowed_file_types = explode(',',$guestallowed_file_types);

// sort($guestallowed_file_types);

// $guestallowed_file_types = implode(',',$guestallowed_file_types);



?>



<div id="main">

  <div id="content"> 

    <!-- Added by B) -------------------->

    <div class="container-fluid">

    <div class="row">

    <div class="col-md-12">

    <h1 class="page-title txt-color-blueDark"><i class="fa-fw fa fa-cog"></i><?php echo $page_title; ?></h1>

    <?php

		if (isset($_GET['status'])) {

			switch ($_GET['status']) {

				case '1':

					$msg = __('Options updated successfully.','cftp_admin');

					echo system_message('ok',$msg);

					break;

				case '2':

					$msg = __('There was an error. Please try again.','cftp_admin');

					echo system_message('error',$msg);

					break;

				case '3':

					$msg = __('Some fields were not completed. Options could not be saved.','cftp_admin');

					echo system_message('error',$msg);

					$show_options_form = 1;

					break;

			}

		}

	

	?>

    <script type="text/javascript">

			$(document).ready(function() {

				$('#notifications_max_tries').spinedit({

					minimum: 1,

					maximum: 100,

					step: 1,

					value: <?php echo NOTIFICATIONS_MAX_TRIES; ?>,

					numberOfDecimals: 0

				});



				$('#notifications_max_days').spinedit({

					minimum: 0,

					maximum: 365,

					step: 1,

					value: <?php echo NOTIFICATIONS_MAX_DAYS; ?>,

					numberOfDecimals: 0

				});



				$('#allowed_file_types').tagsInput({

					'width'			: '95%',

					'height'		: 'auto',

					'defaultText'	: '',

				});



				// $('#guestallowed_file_types').tagsInput({

				// 	'width'			: '95%',

				// 	'height'		: 'auto',

				// 	'defaultText'	: '',

				// });



				$("form").submit(function() {

					clean_form(this);



					is_complete_all_options(this,'<?php _e('Please complete all the fields.','cftp_admin'); ?>');



					// show the errors or continue if everything is ok

					if (show_form_errors() == false) { alert('<?php _e('Please complete all the fields.','cftp_admin'); ?>'); return false; }

				});

			});

		</script> 

        <form action="options.php" name="optionsform" method="post" class="form-horizontal">

    <ul class="nav nav-tabs" role="tablist">

      <li class="active"><a href="#tab_general" aria-controls="tab_general" role="tab" data-toggle="tab">

        <?php _e('General Options','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_clients" aria-controls="tab_clients" role="tab" data-toggle="tab">

        <?php _e('Clients','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_email" aria-controls="tab_email" role="tab" data-toggle="tab">

        <?php _e('E-mail notifications','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_security" aria-controls="tab_security" role="tab" data-toggle="tab">

        <?php _e('Security','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_thumbs" aria-controls="tab_thumbs" role="tab" data-toggle="tab">

        <?php _e('Thumbnails','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_logo" aria-controls="tab_logo" role="tab" data-toggle="tab">

        <?php _e('Company logo','cftp_admin'); ?>

        </a></li>

      <li><a href="#tab_sociallogin" aria-controls="tab_sociallogin" role="tab" data-toggle="tab">

        <?php _e('Social Login', 'cftp_admin'); ?>

        </a></li>

       <li class="cc-group-submit"> <button type="submit" class="btn btn-wide btn-primary empty">

        <?php _e('Update all options','cftp_admin'); ?>

        </button>

        </li>

    </ul>

   

      <div>

        <div id="outer_tabs_wrapper">

          <div class="tab-content">

            <div role="tabpanel" class="tab-pane fade in active" id="tab_general">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('General','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('Basic information to be shown around the site. The time format and zones values affect how the clients see the dates on their files lists.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="this_install_title" class="col-sm-4 control-label">

                        <?php _e('Site name','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="this_install_title" id="this_install_title" class="form-control" value="<?php echo html_output(THIS_INSTALL_SET_TITLE); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="selected_clients_template" class="col-sm-4 control-label">

                        <?php _e("Client's template",'cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="selected_clients_template" id="selected_clients_template">

                          <?php

														$templates = look_for_templates();

														foreach ($templates as $template) {

															echo '<option value="'.html_output($template['folder']).'"';

																if($template['folder'] == TEMPLATE_USE) {

																	echo ' selected="selected"';

																}

															echo '>'.html_output($template['name']).'</option>';

														}

													?>

                        </select>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="timezone" class="col-sm-4 control-label">

                        <?php _e('Timezone','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <?php

													/** 

													 * Generates a select field.

													 * Code is stored on a separate file since it's pretty long.

													 */

													include_once('includes/timezones.php');

												?>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="timeformat" class="col-sm-4 control-label">

                        <?php _e('Time format','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" class="form-control" name="timeformat" id="timeformat" value="<?php echo TIMEFORMAT_USE; ?>" />

                        <p class="field_note">

                          <?php _e('For example, d/m/Y h:i:s will result in something like','cftp_admin'); ?>

                          <strong><?php echo date('d/m/Y h:i:s'); ?></strong>.

                          <?php _e('For the full list of available values, visit','cftp_admin'); ?>

                          <a href="http://php.net/manual/en/function.date.php" target="_blank">

                          <?php _e('this page','cftp_admin'); ?>

                          </a>.</p>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('Language','cftp_admin'); ?>

                    </h3>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="use_browser_lang">

                        <input type="checkbox" value="1" name="use_browser_lang" id="use_browser_lang" class="checkbox_options" <?php echo (USE_BROWSER_LANG == 1) ? 'checked="checked"' : ''; ?> />

                        <?php _e("Detect user browser language",'cftp_admin'); ?>

                        <p class="field_note">

                          <?php _e("If available, will override the default one from the system configuration file. Affects all users and clients.",'cftp_admin'); ?>

                        </p>

                        </label>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('System location','cftp_admin'); ?>

                    </h3>

                    <p class="text-warning">

                      <?php _e('These options are to be changed only if you are moving the system to another place. Changes here can cause ProjectSend to stop working.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="base_uri" class="col-sm-4 control-label">

                        <?php _e('System URI','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" class="form-control" name="base_uri" id="base_uri" value="<?php echo BASE_URI; ?>" />

                      </div>

                    </div>

                    <div class="options_divide"></div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_clients">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('New registrations','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('Used only on self-registrations. These options will not apply to clients registered by system administrators.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="clients_can_register">

                          <input type="checkbox" value="1" name="clients_can_register" id="clients_can_register" class="checkbox_options" <?php echo (CLIENTS_CAN_REGISTER == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Clients can register themselves','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="clients_auto_approve">

                          <input type="checkbox" value="1" name="clients_auto_approve" id="clients_auto_approve" class="checkbox_options" <?php echo (CLIENTS_AUTO_APPROVE == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Auto approve new accounts','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="clients_auto_group" class="col-sm-4 control-label">

                        <?php _e('Add clients to this group:','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="clients_auto_group" id="clients_auto_group">

                          <option value="0">

                          <?php _e('None (does not enable this feature)','cftp_admin'); ?>

                          </option>

                          <?php

														/** Fill the groups array that will be used on the form */

														$groups = array();

														$sql = $dbh->prepare( "SELECT id, name FROM " . TABLE_GROUPS . " ORDER BY name ASC" );

														$sql->execute();

			

														$sql->setFetchMode(PDO::FETCH_ASSOC);

														while ( $grow = $sql->fetch() ) {

															?>

                          <option value="<?php echo filter_var($grow["id"],FILTER_VALIDATE_INT); ?>"

																	<?php

																		if (CLIENTS_AUTO_GROUP == $grow["id"]) {

																			echo 'selected="selected"';

																		}

																	?>

																	><?php echo html_output($grow["name"]); ?> </option>

                          <?php

														}

													?>

                        </select>

                        <p class="field_note">

                          <?php _e('New clients will automatically be assigned to the group you have selected.','cftp_admin'); ?>

                        </p>

                      </div>

                    </div>

					<!-- Code added by rj to limit download files and expiry date START -->

                    <div class="options_divide"></div>

					<h3>

                      <?php _e('File Expiration and Download Limit','cftp_admin'); ?>

                    </h3>

					<div class="form-group">

						<label for="download_limit_max_tries" class="col-sm-4 control-label">

							<?php _e('Maximum Download Limit','cftp_admin'); ?>

						</label>

						<div class="col-sm-8">

							<input type="text" name="download_limit_max_tries" id="download_limit_max_tries" class="form-control" value="<?php echo DOWNLOAD_MAX_TRIES; ?>" />

							<p class="field_note">

								<?php _e('Define Maximum Download limit for the files.','cftp_admin'); ?>

							

							<br />

							<strong>

							<?php _e('Set to 0 to disable.','cftp_admin'); ?>

							</strong></p>

                      </div>

                    </div>

                    <div class="form-group">

						<label for="file_expiry_max_days" class="col-sm-4 control-label">

							<?php _e('Days before Files expiring','cftp_admin'); ?>

						</label>

						<div class="col-sm-8">

							<input type="text" name="file_expiry_max_days" id="file_expiry_max_days" class="form-control" value="<?php echo EXPIRY_MAX_DAYS; ?>" />

							<p class="field_note">

							<?php _e('Notifications older than this will not be available for download.','cftp_admin'); ?>

							<br />

							<strong>

							<?php _e('Set to 0 to disable.','cftp_admin'); ?>

							</strong></p>

						</div>

                    </div>

					<!-- Code added by rj to limit download files and expiry date ENDS -->

					<div class="options_divide"></div>

					

                    <h3>

                      <?php _e('Files','cftp_admin'); ?>

                    </h3>

                    <?php

											/*<p><?php _e('Options related to the files that clients upload themselves.','cftp_admin'); ?></p>

											*/

										?>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="clients_can_upload">

                          <input type="checkbox" value="1" name="clients_can_upload" id="clients_can_upload" class="checkbox_options" <?php echo (CLIENTS_CAN_UPLOAD == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Clients can upload files','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="clients_can_delete_own_files">

                          <input type="checkbox" value="1" name="clients_can_delete_own_files" id="clients_can_delete_own_files" class="checkbox_options" <?php echo (CLIENTS_CAN_DELETE_OWN_FILES == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Clients can delete their own uploaded files','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="expired_files_hide" class="col-sm-4 control-label">

                        <?php _e('When a file expires:','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="expired_files_hide" id="expired_files_hide">

                          <option value="1" <?php echo (EXPIRED_FILES_HIDE == '1') ? 'selected="selected"' : ''; ?>>

                          <?php _e("Don't show it on the files list",'cftp_admin'); ?>

                          </option>

                          <option value="0" <?php echo (EXPIRED_FILES_HIDE == '0') ? 'selected="selected"' : ''; ?>>

                          <?php _e("Show it anyway, but prevent download.",'cftp_admin'); ?>

                          </option>

                        </select>

                        <p class="field_note">

                          <?php _e('This only affects clients. On the admin side, you can still get the files.','cftp_admin'); ?>

                        </p>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_email">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('"From" information','cftp_admin'); ?>

                    </h3>

                    <div class="form-group">

                      <label for="admin_email_address" class="col-sm-4 control-label">

                        <?php _e('E-mail address','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="admin_email_address" id="admin_email_address" class="form-control" value="<?php echo html_output(ADMIN_EMAIL_ADDRESS); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="mail_from_name" class="col-sm-4 control-label">

                        <?php _e('Name','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="<?php echo html_output(MAIL_FROM_NAME); ?>" />

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('Send copies','cftp_admin'); ?>

                    </h3>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="mail_copy_user_upload">

                          <input type="checkbox" value="1" name="mail_copy_user_upload" id="mail_copy_user_upload" <?php echo (COPY_MAIL_ON_USER_UPLOADS == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('When a system user uploads files','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="mail_copy_client_upload">

                          <input type="checkbox" value="1" name="mail_copy_client_upload" id="mail_copy_client_upload" <?php echo (COPY_MAIL_ON_CLIENT_UPLOADS == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('When a client uploads files','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="mail_drop_off_request">

                          <input type="checkbox" value="1" name="mail_drop_off_request" id="mail_drop_off_request" <?php echo (MAIL_DROP_OFF_REQUEST == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('When a client request files','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="options_nested_note">

                      <p>

                        <?php _e('Define here who will receive copies of these emails. These are sent as BCC so neither recipient will see the other addresses.','cftp_admin'); ?>

                      </p>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="mail_copy_main_user">

                          <input type="checkbox" value="1" name="mail_copy_main_user" class="mail_copy_main_user" <?php echo (COPY_MAIL_MAIN_USER == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Address supplied above (on "From")','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="mail_copy_addresses" class="col-sm-4 control-label">

                        <?php _e('Also to this addresses','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="mail_copy_addresses" id="mail_copy_addresses" class="mail_data empty form-control" value="<?php echo html_output(COPY_MAIL_ADDRESSES); ?>" />

                        <p class="field_note">

                          <?php _e('Separate e-mail addresses with a comma.','cftp_admin'); ?>

                        </p>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('Expiration','cftp_admin'); ?>

                    </h3>

                    <div class="form-group">

                      <label for="notifications_max_tries" class="col-sm-4 control-label">

                        <?php _e('Maximum sending attemps','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="notifications_max_tries" id="notifications_max_tries" class="form-control" value="<?php echo NOTIFICATIONS_MAX_TRIES; ?>" />

                        <p class="field_note">

                          <?php _e('Define how many times will the system attempt to send each notification.','cftp_admin'); ?>

                        </p>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="notifications_max_days" class="col-sm-4 control-label">

                        <?php _e('Days before expiring','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="notifications_max_days" id="notifications_max_days" class="form-control" value="<?php echo NOTIFICATIONS_MAX_DAYS; ?>" />

                        <p class="field_note">

                          <?php _e('Notifications older than this will not be sent.','cftp_admin'); ?>

                          <br />

                          <strong>

                          <?php _e('Set to 0 to disable.','cftp_admin'); ?>

                          </strong></p>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('E-mail sending options','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('Here you can select which mail system will be used when sending the notifications. If you have a valid e-mail account, SMTP is the recommended option.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="mail_system_use" class="col-sm-4 control-label">

                        <?php _e('Mailer','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="mail_system_use" id="mail_system_use">

                          <option value="mail" <?php echo (MAIL_SYSTEM == 'mail') ? 'selected="selected"' : ''; ?>>PHP Mail (basic)</option>

                          <option value="smtp" <?php echo (MAIL_SYSTEM == 'smtp') ? 'selected="selected"' : ''; ?>>SMTP</option>

                          <option value="gmail" <?php echo (MAIL_SYSTEM == 'gmail') ? 'selected="selected"' : ''; ?>>Gmail</option>

                          <option value="sendmail" <?php echo (MAIL_SYSTEM == 'sendmail') ? 'selected="selected"' : ''; ?>>Sendmail</option>

                        </select>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('SMTP & Gmail shared options','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('You need to include your username (usually your e-mail address) and password if you have selected either SMTP or Gmail as your mailer.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="mail_smtp_user" class="col-sm-4 control-label">

                        <?php _e('Username','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="mail_smtp_user" id="mail_smtp_user" class="mail_data empty form-control" value="<?php echo html_output(SMTP_USER); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="mail_smtp_pass" class="col-sm-4 control-label">

                        <?php _e('Password','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="password" name="mail_smtp_pass" id="mail_smtp_pass" class="mail_data empty form-control" value="<?php echo html_output(SMTP_PASS); ?>" />

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('SMTP options','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('If you selected SMTP as your mailer, please complete these options.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="mail_smtp_host" class="col-sm-4 control-label">

                        <?php _e('Host','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="mail_smtp_host" id="mail_smtp_host" class="mail_data empty form-control" value="<?php echo html_output(SMTP_HOST); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="mail_smtp_port" class="col-sm-4 control-label">

                        <?php _e('Port','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="mail_smtp_port" id="mail_smtp_port" class="mail_data empty form-control" value="<?php echo html_output(SMTP_PORT); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="mail_smtp_auth" class="col-sm-4 control-label">

                        <?php _e('Authentication','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="mail_smtp_auth" id="mail_smtp_auth">

                          <option value="none" <?php echo (SMTP_AUTH == 'none') ? 'selected="selected"' : ''; ?>>

                          <?php _e('None','cftp_admin'); ?>

                          </option>

                          <option value="ssl" <?php echo (SMTP_AUTH == 'ssl') ? 'selected="selected"' : ''; ?>>SSL</option>

                          <option value="tls" <?php echo (SMTP_AUTH == 'tls') ? 'selected="selected"' : ''; ?>>TLS</option>

                        </select>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_security">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('Allowed file extensions','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('Be careful when changing these options. They could affect not only the system, but the whole server it is installed on.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <label for="file_types_limit_to" class="col-sm-4 control-label">

                        <?php _e('Limit file types uploading to','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <select class="form-control" name="file_types_limit_to" id="file_types_limit_to">

                          <option value="noone" <?php echo (FILE_TYPES_LIMIT_TO == 'noone') ? 'selected="selected"' : ''; ?>>

                          <?php _e('No one','cftp_admin'); ?>

                          </option>

                          <option value="all" <?php echo (FILE_TYPES_LIMIT_TO == 'all') ? 'selected="selected"' : ''; ?>>

                          <?php _e('Everyone','cftp_admin'); ?>

                          </option>

                          <option value="clients" <?php echo (FILE_TYPES_LIMIT_TO == 'clients') ? 'selected="selected"' : ''; ?>>

                          <?php _e('Clients only','cftp_admin'); ?>

                          </option>

                        </select>

                      </div>

                    </div>

                    <p>

                      <strong>

                      <?php _e('Important','cftp_admin'); ?>

                      </strong>:

                      <?php _e('Separate allowed file types with a comma.','cftp_admin'); ?>

                    </p>

                    <strong><?php _e('Allowed file extensions for Others','cftp_admin'); ?></strong>

                    <div class="form-group">

                      <input name="allowed_file_types" id="allowed_file_types" value="<?php echo $allowed_file_types; ?>" />

                    </div>

                     <strong><?php _e('Allowed file extensions for Guest','cftp_admin'); ?></strong>

                    <!-- <div class="form-group">

                      <input name="guestallowed_file_types" id="guestallowed_file_types" value="<?php //echo $guestallowed_file_types; ?>" />

                    </div> -->

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('Passwords','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('When setting up a password for an account, require at least:','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="pass_require_upper">

                          <input type="checkbox" value="1" name="pass_require_upper" id="pass_require_upper" class="checkbox_options" <?php echo (PASS_REQ_UPPER == 1) ? 'checked="checked"' : ''; ?> />

                          <?php echo $validation_req_upper; ?> </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="pass_require_lower">

                          <input type="checkbox" value="1" name="pass_require_lower" id="pass_require_lower" class="checkbox_options" <?php echo (PASS_REQ_LOWER == 1) ? 'checked="checked"' : ''; ?> />

                          <?php echo $validation_req_lower; ?> </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="pass_require_number">

                          <input type="checkbox" value="1" name="pass_require_number" id="pass_require_number" class="checkbox_options" <?php echo (PASS_REQ_NUMBER == 1) ? 'checked="checked"' : ''; ?> />

                          <?php echo $validation_req_number; ?> </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="pass_require_special">

                          <input type="checkbox" value="1" name="pass_require_special" id="pass_require_special" class="checkbox_options" <?php echo (PASS_REQ_SPECIAL == 1) ? 'checked="checked"' : ''; ?> />

                          <?php echo $validation_req_special; ?> </label>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e('reCAPTCHA','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e('Helps prevent SPAM on your registration form.','cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="recaptcha_enabled">

                          <input type="checkbox" value="1" name="recaptcha_enabled" id="recaptcha_enabled" class="checkbox_options" <?php echo (RECAPTCHA_ENABLED == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e('Use reCAPTCHA','cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="recaptcha_site_key" class="col-sm-4 control-label">

                        <?php _e('Site key','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control empty" value="<?php echo html_output(RECAPTCHA_SITE_KEY); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <label for="recaptcha_secret_key" class="col-sm-4 control-label">

                        <?php _e('Secret key','cftp_admin'); ?>

                      </label>

                      <div class="col-sm-8">

                        <input type="text" name="recaptcha_secret_key" id="recaptcha_secret_key" class="form-control empty" value="<?php echo html_output(RECAPTCHA_SECRET_KEY); ?>" />

                      </div>

                    </div>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <?php

			$doc_link_recaptcha = 'https://developers.google.com/recaptcha/docs/start';

												?>

                        <a href="<?php echo $doc_link_recaptcha; ?>" class="external_link" target="_blank">

                        <?php _e('How do I obtain these credentials?','cftp_admin'); ?>

                        </a> 

			</div>

                    </div>

			<div class="options_divide"></div>

			<div class="options_divide"></div>

			<h3>

				<?php _e('Settings for orphan deletion','cftp_admin'); ?>

			</h3>

			<div class="form-group">

				<label for="orphan_deletion_settings" class="col-sm-4 control-label">

					<?php _e('Select a duration','cftp_admin'); ?>

				</label>

				<div class="col-sm-8 ">

		                  <select id="orphan_deletion_settings" name="orphan_deletion_settings" >

		                    <option value="0" <?php if(ORPHAN_DELETION_SETTINGS=='0'){ echo "selected"; }?>>--Select and option--</option>

		                    <option value="d" <?php if(ORPHAN_DELETION_SETTINGS=='d'){ echo "selected"; }?>>Daily</option>

		                    <option value="w" <?php if(ORPHAN_DELETION_SETTINGS=='w'){ echo "selected"; }?>>Weekly</option>

		                    <option value="m" <?php if(ORPHAN_DELETION_SETTINGS=='m'){ echo "selected"; }?>>Monthly</option>

		                  </select>

				</div>

			</div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_thumbs">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('Thumbnails','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e("Thumbnails are used on files lists. It is recommended to keep them small, unless you are using the system to upload only images, and will change the default client's template accordingly.",'cftp_admin'); ?>

                    </p>

                    <div class="options_column">

                      <div class="options_col_left">

                        <div class="form-group">

                          <label for="max_thumbnail_width" class="col-sm-6 control-label">

                            <?php _e('Max width','cftp_admin'); ?>

                          </label>

                          <div class="col-sm-6">

                            <input type="text" name="max_thumbnail_width" id="max_thumbnail_width" class="form-control" value="<?php echo html_output(THUMBS_MAX_WIDTH); ?>" />

                          </div>

                        </div>

                        <div class="form-group">

                          <label for="max_thumbnail_height" class="col-sm-6 control-label">

                            <?php _e('Max height','cftp_admin'); ?>

                          </label>

                          <div class="col-sm-6">

                            <input type="text" name="max_thumbnail_height" id="max_thumbnail_height" class="form-control" value="<?php echo html_output(THUMBS_MAX_HEIGHT); ?>" />

                          </div>

                        </div>

                      </div>

                      <div class="options_col_right">

                        <div class="form-group">

                          <label for="thumbnail_default_quality" class="col-sm-6 control-label">

                            <?php _e('JPG Quality','cftp_admin'); ?>

                          </label>

                          <div class="col-sm-6">

                            <input type="text" name="thumbnail_default_quality" id="thumbnail_default_quality" class="form-control" value="<?php echo html_output(THUMBS_QUALITY); ?>" />

                          </div>

                        </div>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                    <h3>

                      <?php _e("File's path", 'cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e("If thumbnails are not showing (your company logo and file's preview on the branding page and client's files lists) try setting this option ON. It they still don't work, a folders permission issue might be the cause.",'cftp_admin'); ?>

                    </p>

                    <div class="form-group">

                      <div class="col-sm-8 col-sm-offset-4">

                        <label for="thumbnails_use_absolute">

                          <input type="checkbox" value="1" name="thumbnails_use_absolute" id="thumbnails_use_absolute" <?php echo (THUMBS_USE_ABSOLUTE == 1) ? 'checked="checked"' : ''; ?> />

                          <?php _e("Use file's absolute path",'cftp_admin'); ?>

                        </label>

                      </div>

                    </div>

                    <div class="options_divide"></div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_logo">

              <div class="row">

                <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 white-box">

                  <div class="white-box-interior">

                    <h3>

                      <?php _e('Size settings','cftp_admin'); ?>

                    </h3>

                    <p>

                      <?php _e("Like the thumbnails options, these have to be changed taking into account the client's template design, since it can be shown there. The default template uses a fixed width for the logo, however the Gallery template uses this setting to show the image on top.",'cftp_admin'); ?>

                    </p>

                    <div class="options_column">

                      <div class="options_col_left">

                        <div class="form-group">

                          <label for="max_logo_width" class="col-sm-6 control-label">

                            <?php _e('Max width','cftp_admin'); ?>

                          </label>

                          <div class="col-sm-6">

                            <input type="text" name="max_logo_width" id="max_logo_width" class="form-control" value="<?php echo html_output(LOGO_MAX_WIDTH); ?>" />

                          </div>

                        </div>

                      </div>

                      <div class="options_col_right">

                        <div class="form-group">

                          <label for="max_logo_height" class="col-sm-6 control-label">

                            <?php _e('Max height','cftp_admin'); ?>

                          </label>

                          <div class="col-sm-6">

                            <input type="text" name="max_logo_height" id="max_logo_height" class="form-control" value="<?php echo html_output(LOGO_MAX_HEIGHT); ?>" />

                          </div>

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab_sociallogin">

              <div class="row"> 

                <!-- Google Start -->

                <div class="col-xs-12 col-md-6 ">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-google" aria-hidden="true"></i> &nbsp;<?php _e('Google','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="google_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="google_signin_enabled" id="google_signin_enabled" class="form-control">

                            <option value="1" <?php echo (GOOGLE_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (GOOGLE_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="google_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="google_client_id" id="google_client_id" class="form-control empty" value="<?php echo html_output(GOOGLE_CLIENT_ID); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="google_client_secret" class="col-sm-4 control-label">

                          <?php _e('Client Secret','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="google_client_secret" id="google_client_secret" class="form-control empty" value="<?php echo html_output(GOOGLE_CLIENT_SECRET); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <div class="col-sm-8 col-sm-offset-4">

                          <?php

														$doc_link_google = 'https://developers.google.com/identity/protocols/OpenIDConnect';

													?>

                          <a href="<?php echo $doc_link_google; ?>" class="external_link" target="_blank">

                          <?php _e('How do I obtain this credentials?','cftp_admin'); ?>

                          </a> </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- Google End --> 

                <!-- Facebook start -->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-facebook" aria-hidden="true"></i> &nbsp;<?php _e('Facebook','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="facebook_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="facebook_signin_enabled" id="facebook_signin_enabled" class="form-control">

                            <option value="1" <?php echo (FACEBOOK_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (FACEBOOK_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="facebook_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="facebook_client_id" id="facebook_client_id" class="form-control empty" value="<?php echo html_output(FACEBOOK_CLIENT_ID); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="facebook_client_secret" class="col-sm-4 control-label">

                          <?php _e('Client Secret','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="facebook_client_secret" id="facebook_client_secret" class="form-control empty" value="<?php echo html_output(FACEBOOK_CLIENT_SECRET); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <div class="col-sm-8 col-sm-offset-4">

                          <?php

														$doc_link_facebook = 'https://developers.facebook.com/apps/';

													?>

                          <a href="<?php echo $doc_link_facebook; ?>" class="external_link" target="_blank">

                          <?php _e('How do I obtain this credentials?','cftp_admin'); ?>

                          </a> </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- Facebook End --> 

                <!-- twitter Start -->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-twitter" aria-hidden="true"></i> &nbsp;<?php _e('twitter','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="twitter_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="twitter_signin_enabled" id="twitter_signin_enabled" class="form-control">

                            <option value="1" <?php echo (TWITTER_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (TWITTER_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="twitter_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="twitter_client_id" id="twitter_client_id" class="form-control empty" value="<?php echo html_output(TWITTER_CLIENT_ID); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="twitter_client_secret" class="col-sm-4 control-label">

                          <?php _e('Client Secret','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="twitter_client_secret" id="twitter_client_secret" class="form-control empty" value="<?php echo html_output(TWITTER_CLIENT_SECRET); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <div class="col-sm-8 col-sm-offset-4">

                          <?php

														$doc_link_twitter = 'https://developers.twitter.com/identity/protocols/OpenIDConnect';

													?>

                          <a href="#" class="external_link" target="_blank">

                          <?php _e('How do I obtain this credentials?','cftp_admin'); ?>

                          </a> </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- twitter End --> 

                <!-- yahoo Start -->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-yahoo" aria-hidden="true"></i> &nbsp;<?php _e('yahoo','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="yahoo_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="yahoo_signin_enabled" id="yahoo_signin_enabled" class="form-control">

                            <option value="1" <?php echo (YAHOO_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (YAHOO_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="yahoo_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="yahoo_client_id" id="yahoo_client_id" class="form-control empty" value="<?php echo html_output(YAHOO_CLIENT_ID); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="yahoo_client_secret" class="col-sm-4 control-label">

                          <?php _e('Client Secret','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="yahoo_client_secret" id="yahoo_client_secret" class="form-control empty" value="<?php echo html_output(YAHOO_CLIENT_SECRET); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <div class="col-sm-8 col-sm-offset-4">

                          <?php

														$doc_link_yahoo = 'https://developers.yahoo.com/identity/protocols/OpenIDConnect';

													?>

                          <a href="#" class="external_link" target="_blank">

                          <?php _e('How do I obtain this credentials?','cftp_admin'); ?>

                          </a> </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- yahoo End --> 

                <!-- linkedin Start -->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-linkedin-square" aria-hidden="true"></i> &nbsp;<?php _e('linkedin','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="linkedin_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="linkedin_signin_enabled" id="linkedin_signin_enabled" class="form-control">

                            <option value="1" <?php echo (LINKEDIN_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (LINKEDIN_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="linkedin_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="linkedin_client_id" id="linkedin_client_id" class="form-control empty" value="<?php echo html_output(LINKEDIN_CLIENT_ID); ?>" />

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="linkedin_client_secret" class="col-sm-4 control-label">

                          <?php _e('Client Secret','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="linkedin_client_secret" id="linkedin_client_secret" class="form-control empty" value="<?php echo html_output(LINKEDIN_CLIENT_SECRET); ?>" />

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- linkedin End --> 

                

                <!-- office 365 Start -->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

                    <h3>

                      <i class="fa fa-windows" aria-hidden="true"></i> &nbsp;<?php _e('Office 365','cftp_admin'); ?>

                    </h3>

                    <div class="options_column">

                      <div class="form-group">

                        <label for="windows_signin_enabled" class="col-sm-4 control-label">

                          <?php _e('Enabled','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <select name="windows_signin_enabled" id="windows_signin_enabled" class="form-control">

                            <option value="1" <?php echo (WINDOWS_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

                            <option value="0" <?php echo (WINDOWS_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

                          </select>

                        </div>

                      </div>

                      <div class="form-group">

                        <label for="windows_client_id" class="col-sm-4 control-label">

                          <?php _e('Client ID','cftp_admin'); ?>

                        </label>

                        <div class="col-sm-8">

                          <input type="text" name="windows_client_id" id="windows_client_id" class="form-control empty" value="<?php echo html_output(WINDOWS_CLIENT_ID); ?>" />

                        </div>

                      </div>

                    </div>

                  </div>

                </div>

                <!-- office 365  End -->

                <!-- ldap Start  --> 

				<div class="col-xs-12 col-md-6" style="clear:both">

                  <div class="white-box-interior white-box">

										<h3><?php _e('ldap','cftp_admin'); ?></h3>

										<div class="options_column">

											<div class="form-group">

												<label for="ldap_signin_enabled" class="col-sm-4 control-label"><?php _e('Enabled','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<select name="ldap_signin_enabled" id="ldap_signin_enabled" class="form-control">

														<option value="1" <?php echo (LDAP_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

														<option value="0" <?php echo (LDAP_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

													</select>

												</div>

											</div>

											<div class="form-group">

												<label for="ldap_client_id" class="col-sm-4 control-label"><?php _e('Server','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="ldap_server_url" value="<?php echo html_output(LDAP_SERVER); ?>" id="ldap_server_url" class="form-control empty" placeholder="Server" />

												</div>

											</div>

											<div class="form-group">

												<label for="ldap_bind_port" class="col-sm-4 control-label"><?php _e('Port','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="ldap_bind_port" id="ldap_bind_port" value="<?php echo html_output(LDAP_PORT); ?>" class="form-control empty" placeholder="Port" />

												</div>

											</div>

                                            <div class="form-group">

												<label for="ldap_bind_dn" class="col-sm-4 control-label"><?php _e('Bind DN','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="ldap_bind_dn" id="ldap_bind_dn" value="<?php echo html_output(LDAP_BIND_DN); ?>" class="form-control empty" placeholder="Bind DN" />

												</div>

											</div>

											<div class="form-group">

												<label for="ldap_bind_password" class="col-sm-4 control-label"><?php _e('Bind Password','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="ldap_bind_password" value="<?php echo html_output(LDAP_BIND_PASS); ?>" id="ldap_bind_password" class="form-control empty" placeholder="Bind Password"/>

												</div>

											</div>

										</div>

									</div>

								</div>

				<!-- ldap End -->  

                <!-- saml ----->

                <div class="col-xs-12 col-md-6">

                  <div class="white-box-interior white-box">

										<h3><?php _e('SAML','cftp_admin'); ?></h3>

										<div class="options_column">

											<div class="form-group">

												<label for="saml_signin_enabled" class="col-sm-4 control-label"><?php _e('Enabled','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<select name="saml_signin_enabled" id="saml_signin_enabled" class="form-control">

														<option value="1" <?php echo (SAML_SIGNIN_ENABLED == '1') ? 'selected="selected"' : ''; ?>>Yes</option>

														<option value="0" <?php echo (SAML_SIGNIN_ENABLED == '0') ? 'selected="selected"' : ''; ?>>No</option>

													</select>

												</div>

											</div>

											<div class="form-group">

												<label for="saml_baseurl_path" class="col-sm-4 control-label"><?php _e('Base url path','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="saml_baseurl_path" value="<?php echo html_output(SAML_BASE_URL); ?>" id="saml_baseurl_path" class="form-control empty" placeholder="Server" />

												</div>

											</div>

                                            <div class="form-group">

												<label for="saml_entity_id" class="col-sm-4 control-label"><?php _e('Entity ID','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="saml_entity_id" value="<?php echo html_output(SAML_ENTITY_ID); ?>" id="saml_entity_id" class="form-control empty" placeholder="Entity ID" />

												</div>

											</div>

                                            <div class="form-group">

												<label for="saml_idp" class="col-sm-4 control-label"><?php _e('IDP','cftp_admin'); ?></label>

												<div class="col-sm-8">

													<input type="text" name="saml_idp" value="<?php echo html_output(SAML_IDP); ?>" id="saml_idp" class="form-control empty" placeholder="Entity ID" />

												</div>

											</div>

										</div>

									</div>

								</div>

                <!-- saml ----->

                

              </div>

            </div>

          </div>

        </div>

      </div>

      </div>

<!--      <div class="after_form_buttons">

        <button type="submit" class="btn btn-wide btn-primary empty">

        <?php //_e('Update all options','cftp_admin'); ?>

        </button>

      </div>-->

      </div>

      </div>

    </form>

  </div>

</div>

</div>

</div>

</div>

<?php

	include('footer.php');

?>

