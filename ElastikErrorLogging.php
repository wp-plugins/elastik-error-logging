<?php
/*
Plugin Name: elastik-error-logging
Plugin URI: http://wordpress.org/extend/plugins/elastik-error-logging/
Description: Logs errors with full information to an Elastik Ticket System. Errors can be tracked and emails can be sent to devs from there.L
Version: 0.1
Author: James
Author URI: http://jarofgreen.co.uk
License: BSD
*/

class ElastikErrorLogging {
	public static function activate() {
		add_option('ElastikErrorLoggingRemoteHost', '');
		add_option('ElastikErrorLoggingRemoteID', '');
		add_option('ElastikErrorLoggingRemoteSecurityKey', '');

		add_option('ElastikErrorLoggingIncludeCookies', 'yes');

		add_option('ElastikErrorLoggingReportWarning', 'yes');
		add_option('ElastikErrorLoggingReportNotice', 'yes');
		add_option('ElastikErrorLoggingReportUserError', 'yes');
		add_option('ElastikErrorLoggingReportUserWarning', 'yes');
		add_option('ElastikErrorLoggingReportUserNotice', 'yes');
		add_option('ElastikErrorLoggingReportRecoverableError', 'yes');
		add_option('ElastikErrorLoggingReportUserDeprecated', 'yes');
		add_option('ElastikErrorLoggingReportDeprecated', '');
		add_option('ElastikErrorLoggingReportStrict', '');

		add_option('ElastikErrorLoggingErrorMessage', 'We are sorry, but an error occured. It has been logged. Please try again later.');
	}

	private static $errorMessageForUserEscapeContent = '\'">--></script></table>';

	public static function init_admin_menu() {
		add_options_page('Elastik Error Logging Options', 'Elastik Error Logging',
				'manage_options', 'ElastikErrorLoggingOptionsPage', 'ElastikErrorLogging::options_page');

	}

	public static function deactivate() {

	}


	public static function uninstall() {

	}

	public static function init() {
		if (get_option('ElastikErrorLoggingRemoteHost') && get_option('ElastikErrorLoggingRemoteID') && get_option('ElastikErrorLoggingRemoteSecurityKey')) {
			set_exception_handler('ElastikErrorLogging::report_exception');
			set_error_handler('ElastikErrorLogging::report_error');
			register_shutdown_function('ElastikErrorLogging::shut_down_function');
		}
	}

	public static function options_page() {
		$SERVER_PINGED = false;
		if (isset($_POST['PingServer']) && $_POST['PingServer'] == 'please') {
			self::ping_server();
			$SERVER_PINGED = true;
		}
		$ERROR_TYPES = array(
				'ElastikErrorLoggingReportWarning'=>'Warnings',
				'ElastikErrorLoggingReportNotice'=>'Notices',
				'ElastikErrorLoggingReportUserError'=>'User Error',
				'ElastikErrorLoggingReportUserWarning'=>'User Warning',
				'ElastikErrorLoggingReportUserNotice'=>'User Notice',
				'ElastikErrorLoggingReportRecoverableError'=>'Recoverable Error',
				'ElastikErrorLoggingReportUserDeprecated'=>'User Deprecated',
				'ElastikErrorLoggingReportDeprecated'=>'Deprecated',
				'ElastikErrorLoggingReportStrict'=>'Strict',
			);
		require dirname(__FILE__).'/'.'options.php';
	}

	public static function ping_server() {
		$data = array("Request"=>"Ping");
		self::send_data($data);
	}

	public static function report_error($errno, $errstr, $errfile=null,  $errline=null, $errcontext=null ) {
		if (!self::report_this_error_type($errno)) return;
		$data = array("Request"=>"ReportException");
		$data["ErrorTypeConstantInteger"] = $errno; // The ErrorTypeConstantInteger option is used by Elastik 0.3.1 and above
		$data["ErrorType"] = self::$errorCodes[$errno];  // The ErrorType option is used by Elastik 0.3.0 and below
		$data["POSTVarsSerialized"] = serialize($_POST);
		if (get_option('ElastikErrorLoggingIncludeCookies') == 'yes') {
			$data["CookiesSerialized"] = serialize($_COOKIE);
		}
		$data["ServerVarsSerialized"] = serialize(self::get_server_vars());
		$x = debug_backtrace();
		array_shift($x); // remove this function from the backtrace.
		array_unshift($x, array('function'=>'?','file'=>$errfile,'line'=>$errline)); // add where we were called from tho.
		$data["BackTraceSerialized"] = serialize($x);
		$data["ErrorMessage"] = $errstr;
		ElastikErrorLogging::send_data($data);
		print self::$errorMessageForUserEscapeContent . get_option('ElastikErrorLoggingErrorMessage');
		die();
	}

	public static function report_exception(Exception $exception) {
		$data = array("Request"=>"ReportException");
		$data["POSTVarsSerialized"] = serialize($_POST);
		if (get_option('ElastikErrorLoggingIncludeCookies') == 'yes') {
			$data["CookiesSerialized"] = serialize($_COOKIE);
		}
		$data["ServerVarsSerialized"] = serialize(self::get_server_vars());
		$x = $exception->getTrace();
		array_unshift($x, array('file'=>$exception->getFile(),'line'=>$exception->getLine())); // add where we were called from.
		$data["BackTraceSerialized"] = serialize($x);
		$data["ErrorMessage"] = $exception->getMessage();
		$data["ErrorType"] = get_class($exception);
		ElastikErrorLogging::send_data($data);
		print self::$errorMessageForUserEscapeContent . get_option('ElastikErrorLoggingErrorMessage');
		die();
	}

	public static function shut_down_function() {
		$error = error_get_last();
		if ($error) {
			if (!self::report_this_error_type($error['type'])) return;
			$data = array("Request"=>"ReportException");
			$data["POSTVarsSerialized"] = serialize($_POST);
			if (get_option('ElastikErrorLoggingIncludeCookies') == 'yes') {
				$data["CookiesSerialized"] = serialize($_COOKIE);
			}
			$data["ServerVarsSerialized"] = serialize(self::get_server_vars());
			$data["BackTraceSerialized"] = serialize(array(array('function'=>'?','file'=>$error['file'],'line'=>$error['line'])));
			$data["ErrorMessage"] = $error['message'];
			$data["ErrorTypeConstantInteger"] = $error['type'];  // The ErrorTypeConstantInteger option is used by Elastik 0.3.1 and above
			$data["ErrorType"] = self::$errorCodes[$error['type']];  // The ErrorType option is used by Elastik 0.3.0 and below
			ElastikErrorLogging::send_data($data);
		}
	}

	private static function get_server_vars() {
		$o = $_SERVER; // we need to copy the array so we can edit it;
		if (isset($o['HTTP_COOKIE'])) unset($o['HTTP_COOKIE']); // Cookies are sent seperately, if the user has choosen to do so.
		return $o;
	}


	/** We use 8192 for E_DEPRECATED & 16384 for E_USER_DEPRECATED so this code runs on PHP 5.2 where those constants aren't defined. **/
	private static $errorCodes = array(
			E_ERROR => "ERROR",
			E_WARNING  => "WARNING" ,
			E_PARSE => "PARSE",
			E_NOTICE  => "NOTICE" ,
			E_CORE_ERROR => "CORE_ERROR",
			E_CORE_WARNING => "CORE_WARNING",
			E_COMPILE_ERROR => "COMPILE_ERROR",
			E_COMPILE_WARNING => "COMPILE_WARNING",
			E_USER_ERROR  => "USER_ERROR" ,
			E_USER_WARNING  => "USER_WARNING" ,
			E_USER_NOTICE  => "USER_NOTICE" ,
			E_STRICT => "STRICT",
			E_RECOVERABLE_ERROR  => "RECOVERABLE_ERROR" ,
			8192 => "DEPRECATED",
			16384  => "USER_DEPRECATED" ,
		);

	private static function report_this_error_type($errno) {

		if ($errno == E_STRICT) return  (bool)(get_option('ElastikErrorLoggingReportStrict') == 'yes');
		if ($errno == E_WARNING) return (bool)(get_option('ElastikErrorLoggingReportWarning') == 'yes');
		if ($errno == E_NOTICE) return (bool)(get_option('ElastikErrorLoggingReportNotice') == 'yes');
		if ($errno == E_USER_ERROR) return (bool)(get_option('ElastikErrorLoggingReportUserError') == 'yes');
		if ($errno == E_USER_WARNING) return (bool)(get_option('ElastikErrorLoggingReportUserWarning') == 'yes');
		if ($errno == E_USER_NOTICE) return (bool)(get_option('ElastikErrorLoggingReportUserNotice') == 'yes');
		if ($errno == E_RECOVERABLE_ERROR) return (bool)(get_option('ElastikErrorLoggingReportRecoverableError') == 'yes');
		if ($errno == 8192) return (bool)(get_option('ElastikErrorLoggingReportDeprecated') == 'yes');
		if ($errno == 16384) return (bool)(get_option('ElastikErrorLoggingReportUserDeprecated') == 'yes');
		return true;
	}


	private static function send_data($data) {
		$data['Time'] = time();
		$data['SiteID'] = intval(get_option('ElastikErrorLoggingRemoteID'));
		$data['SiteSecurityKey'] = get_option('ElastikErrorLoggingRemoteSecurityKey');
		//print_r($data);

		$url = "http://".get_option('ElastikErrorLoggingRemoteHost')."/mod.ErrorReportingService/api.php";

		// Method 1: CURL
		if (extension_loaded('curl')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_exec($ch);
			curl_close($ch);
			return true;
		}

		// Method 2: fopen a URL
		if (ini_get('allow_url_fopen')) {
			$params = array('http' => array(
					'method' => 'POST',
					'content' => http_build_query($data),
					'header'=> "Content-type: application/x-www-form-urlencoded\r\n",
				));
			$ctx = stream_context_create($params);
			$fp = fopen($url, 'rb', false, $ctx);
			if (!$fp) {
				print "FOPEN ERROR!";
				die();
			}
			fclose($fp);
			return true;
		}

		// Method 3: Raw Sockets
		if (true) { // as far as I can tell, these functions should always be available.
			$URLInfo=parse_url($url);
			if(!isset($URLInfo["port"])) $URLInfo["port"] = 80;
			$values = array();
			foreach($data as $key=>$value) $values[]="$key=".urlencode($value);
			$dataString = implode("&",$values);
			$request = "POST ".$URLInfo["path"]." HTTP/1.1\n";
			$request .= "Host: ".$URLInfo["host"]."\n";
			$request .= "Referer: \n";
			$request .= "Content-type: application/x-www-form-urlencoded\n";
			$request .= "Content-length: ".strlen($dataString)."\n";
			$request .= "Connection: close\n";
			$request .= "\n";
			$request.= $dataString."\n";
			$result = '';  // We don't do anything with the result ... but we collect it to ensure the webserver proccesses our request fully.
			if ($fp = fsockopen("tcp://" . $URLInfo["host"], $URLInfo["port"],$errno, $errstr, 2.0)) {
				fputs($fp, $request);
				$started = time();
				while(!feof($fp) && (time() - $started) < 5) $result .= @fgets($fp, 128);
				fclose($fp);
			}
			return true;
		}

		print "WE COULD NOT SEND DATA AS NO SUITABLE MECHANISM WAS FOUND!";
		die();

	}

}

if (function_exists('add_action')) {
	add_action('init', 'ElastikErrorLogging::init');
	add_action('admin_menu', 'ElastikErrorLogging::init_admin_menu');
}

if (function_exists('register_activation_hook')) {
	register_activation_hook( __FILE__, 'ElastikErrorLogging::activate' );
}

if (function_exists('register_deactivation_hook')) {
	register_deactivation_hook( __FILE__, 'ElastikErrorLogging::deactivate');
}

if (function_exists('register_uninstall_hook')) {
	register_uninstall_hook( __FILE__, 'ElastikErrorLogging::uninstall');
}
