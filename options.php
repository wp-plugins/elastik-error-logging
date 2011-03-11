<div class="wrap">
	<h2>Elastik Error Logging Options</h2>

	<form method="post" action="options.php">
		<?php wp_nonce_field('update-options'); ?>

		<h3>Your site details</h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">Remote Site ID</th>
				<td><input type="text" name="ElastikErrorLoggingRemoteID" value="<?php echo get_option('ElastikErrorLoggingRemoteID'); ?>" /></td>
			</tr>

			<tr valign="top">
				<th scope="row">Remote Security Key</th>
				<td><input type="text" name="ElastikErrorLoggingRemoteSecurityKey" value="<?php echo get_option('ElastikErrorLoggingRemoteSecurityKey'); ?>" /></td>
			</tr>

		</table>

		<h3>Communication with Elastik</h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">Remote Host Name</th>
				<td><input type="text" name="ElastikErrorLoggingRemoteHost" value="<?php echo get_option('ElastikErrorLoggingRemoteHost'); ?>" /></td>
			</tr>

		</table>

		<h3>Error collection</h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">Include Cookie Data</th>
				<td><input type="checkbox" name="ElastikErrorLoggingIncludeCookies" value="yes" <?php echo (get_option('ElastikErrorLoggingIncludeCookies') == 'yes') ? 'checked="checked"' : ''; ?> /></td>
			</tr>

			<?php foreach($ERROR_TYPES as $var=>$label) { ?>
				<tr valign="top">
					<th scope="row">Stop on <?php echo $label ?></th>
					<td><input type="checkbox" name="<?php echo $var ?>" value="yes" <?php echo (get_option($var) == 'yes') ? 'checked="checked"' : ''; ?> /></td>
				</tr>
			<?php } ?>

		</table>

		<h3>Display</h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row">Message to show to user</th>
				<td><input type="text" name="ElastikErrorLoggingErrorMessage" value="<?php echo get_option('ElastikErrorLoggingErrorMessage'); ?>" /></td>
			</tr>

		</table>


		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="<?php echo implode(",",  array_keys($ERROR_TYPES)) ?>,ElastikErrorLoggingErrorMessage,ElastikErrorLoggingIncludeCookies,ElastikErrorLoggingRemoteHost,ElastikErrorLoggingRemoteID,ElastikErrorLoggingRemoteSecurityKey" />

		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
		</p>

	</form>


	<h3>Test Communication with Elastik</h3>

	<?php if ($SERVER_PINGED) { ?>
		<p>We attempted to ping the server. Please check the server to see if the ping was recorded.</p>
	<?php } else { ?>
		<p>You can ping Elastik to see if the above options are correct.
		<form method="post" action="">
			<input type="hidden" name="PingServer" value="please">
			<input type="submit" class="button-primary" value="Ping Elastik" />
		</form>
	<?php } ?>

</div>