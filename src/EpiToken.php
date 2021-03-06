<?php
/** 
  * EpiToken - for CSRF prevention and prevention of form resubmissions
  * 
  * Implementation based on https://www.owasp.org/index.php/PHP_CSRF_Guard
  * @author Geoffery Miller <geoffery.miller@gmail.com>
  * @version 1.0
  * @package EpiCode
  */
class EpiToken
{
	const COOKIE_METHOD = 'EpiToken_Cookie';
	const SINGLE_SESSION = 'EpiToken_SingleSession';
	const MULTIPLE_SESSION = 'EpiToken_MultipleSesson';

	private static $employ = self::COOKIE_METHOD; // need to implement
	// currently this code makes use of a single session token for all forms per session

	private static $fieldname = "nonce-name";
	private static $fieldtoken = "nonce-token";
	
	public function generateToken($tokenName)	{
		$token = hash("sha512",mt_rand(0,mt_getrandmax()));
		getSession()->set($tokenName, $token);
		return $token;
	}
	public function validateToken($tokenName, $tokenValue)	{
		$token = getSession()->get($tokenName);
		$result = false;
		if ($token == $tokenValue)	{
			$result = true;
			getSession()->set($tokenName,null);
		}
		return $result;
	}
	public function validateForm($form)	{
		$fname = self::$fieldname;
		$ftoken = self::$fieldtoken;
		if (empty($form[$fname]) || empty($form[$ftoken]) )
			return false;
		$name = $form[$fname];
		$token = $form[$ftoken];
		return $this->validateToken($name, $token);
	}
	public function addToken($e = true)	{
		$name = null;
		while(!isset($name))	{
			$name = "token_" . mt_rand(0,mt_getrandmax());
			$check = getSession()->get($name);
			if(!isset($check))	{
				$name = null;
			}
		}
		$token = $this->generateToken($name);
		if ($e) {
			$fname = self::$fieldname;
			$ftoken = self::$fieldtoken;
			echo "<input type=\"hidden\" name=\"$fname\" value=\"$name\"/>";
			echo "<input type=\"hidden\" name=\"$ftoken\" value=\"$token\"/>";
		}
		else	{
			$a = array("name"=>$name, "token"=>$token);
			return $a;
		}
	}
	public static function employ()	{
		if (func_num_args() === 1)
			self::$employ = func_get_arg(0);
		return self::$employ;
	}
}

interface EpiTokenInterface {
	public function addToken($e = true);
	public function validateForm($form);
	public function validateToken($tokenName, $tokenValue);
	public function generateToken($tokenName); 
}

function getToken()
{
  static $token;
  if(!$token)
    $token = new EpiToken();

  return $token;
}
