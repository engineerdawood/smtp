<?php
namespace App\Custom\Exceptions;

use Exception;

/**
 * verifyEmail exception handler
 */
class VerifyEmailException extends Exception {

	/**
	 * Prettify error message output
	 * @return string
	 */
	public function errorMessage() {
		//$errorMsg = '<strong>' . $this->getMessage() . "</strong><br />\n";
		$errorMsg = $this->getMessage();
		return $errorMsg;
	}

}

?>