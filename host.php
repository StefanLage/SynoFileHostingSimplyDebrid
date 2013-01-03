	<?php


define('LOGIN_FAIL', 4);
define('USER_IS_FREE', 5);
define('USER_IS_PREMIUM', 6);
define('ERR_FILE_NO_EXIST', 114);
define('ERR_REQUIRED_PREMIUM', 115);
define('ERR_NOT_SUPPORT_TYPE', 116);
define('DOWNLOAD_STATION_USER_AGENT', "Mozilla/4.0 (compatible; MSIE 6.1; Windows XP)");
define('DOWNLOAD_URL', 'downloadurl'); // Real download url
define('DOWNLOAD_FILENAME', 'filename'); // Saved file name define('DOWNLOAD_COUNT', 'count'); // Number of seconds to wait
define('DOWNLOAD_ISQUERYAGAIN', 'isqueryagain'); // 1: Use the original url query from the user again. 2: Use php output url query again.
define('DOWNLOAD_ISPARALLELDOWNLOAD', 'isparalleldownload');//Task can download parallel flag.
define('DOWNLOAD_COOKIE', 'cookiepath');




class SynoFileHostingSimplyDebrid
{ 

	private $Url;
	private $Username;
	private $Password;
	private $HostInfo;
	private $SIMPLYDEBRID_COOKIE 					= '/tmp/z3raw.cookie';
	private $SIMPLYDEBRID_PHPSESSID					= '/tmp/z3raw_phpsessid.cookie';
	private $SIMPLYDEBRID_LOGIN_URL 				= 'http://z3raw.fr/login.php';
	private $SIMPLYDEBRID_ACCOUNT_URL 				= 'http://z3raw.fr';
	private $SIMPLYDEBRID_PREMIUM_ACCOUNT_KEYWORD 	= 'premium'; 
	
	public function __construct($Url, $Username, $Password, $HostInfo) 
	{ 
		$this->Url 									= 'http://z3raw.fr/inc/name.php?i='.$Url;
		$this->Username 							= $Username;
		$this->Password 							= $Password;
		$this->HostInfo 							= $HostInfo; 
	}
	
	//This function returns download url. 
	public function GetDownloadInfo() 
	{
		$ret 										= FALSE;
		$VerifyRet									= $this->Verify(FALSE);
		$ret 										= $this->DownloadPremium($this->CookieValue);
		
		// File already exist => delete it
		if (file_exists($this->SIMPLYDEBRID_COOKIE)) 
			unlink($this->SIMPLYDEBRID_COOKIE); 
		
		//echo($ret);
		return $ret; 

	}

	// Check if Customer have an account or free account
	public function Verify($ClearCookie)
	{
		$ret 										= LOGIN_FAIL;
		$this->CookieValue 							= FALSE;
		
		if (!empty($this->Username) && !empty($this->Password)) 
			$this->CookieValue 						= $this->SimplyDebridLogin($this->Username, 
																			   $this->Password);

		// Error => go to end
		if ($this->CookieValue == FALSE) 
			goto End;
		/*if ($this->IsFreeAccount()) 
		{
			$ret = USER_IS_FREE; 
		} */
		else 
			$ret 									= USER_IS_PREMIUM; 

		End:

		return $ret; 
	}


	private function SimplyDebridLogin($Username, $Password) 
	{
		$ret 										= FALSE;
		$queryUrl 									= $this->SIMPLYDEBRID_LOGIN_URL;
		//$PostData = http_build_query($PostData);

		if (!file_exists($this->SIMPLYDEBRID_PHPSESSID)) 
		{// No PHPSESSID so we request the website to get it

			$curlsession 							= curl_init();

			curl_setopt($curlsession, 
						CURLOPT_USERAGENT, 
						DOWNLOAD_STATION_USER_AGENT);

			curl_setopt($curlsession, 
						CURLOPT_COOKIEJAR, 
						$this->SIMPLYDEBRID_PHPSESSID);

			curl_setopt($curlsession, 
						CURLOPT_HEADER, 
						TRUE); 
			//curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($curlsession, 
						CURLOPT_URL, 
						$queryUrl); 
			$t 										= curl_exec($curlsession);
			curl_close($curlsession);
		}

		$curl 										= curl_init();
		curl_setopt($curl, 
					CURLOPT_POST, 
					TRUE);

		curl_setopt($curl, 
					CURLOPT_POSTFIELDS, 
					'username='.$Username.'&password='.$Password.'&submit=Envoyer'); 

		curl_setopt($curl, 
					CURLOPT_COOKIEFILE, 
					$this->SIMPLYDEBRID_PHPSESSID);

		curl_setopt($curl, 
					CURLOPT_COOKIEJAR, 
					$this->SIMPLYDEBRID_COOKIE);

		curl_setopt($curl, 
					CURLOPT_HEADER, 
					TRUE); 

		curl_setopt($curl, 
					CURLOPT_RETURNTRANSFER, 
					TRUE); 

		curl_setopt($curl, 
					CURLOPT_URL, 
					$queryUrl); 
		$LoginInfo							 		= curl_exec($curl);
		curl_close($curl);
		

		if (FALSE != $LoginInfo && file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			$arrayCookie 							= extractCookies(file_get_contents($this->SIMPLYDEBRID_COOKIE));
			$arrayLogin 							= $arrayCookie[2];

			if(!is_null($arrayLogin['value']))
				$ret 								= $arrayLogin['value'];
			else
				$ret 								= FALSE; 

		}

		return $ret; 
	}

	private function DownloadPremium($CookieValue) 
	{	
		$page 										= $this->DownloadParsePage(TRUE); 

		// Split response to get link part
		$page 										= explode('http://', 
															  $page);
		// Cut last html part to get only download link
		$DownloadInfo 								= explode('<', 
															  $page[2]);
		$DownloadInfo 								= $DownloadInfo[0];
		
		return $DownloadInfo; 
	}

	private function DownloadParsePage($IsLoadCookie) 
	{
		$Option 									= array();
		$curl 										= curl_init();
		curl_setopt($curl, 
					CURLOPT_SSL_VERIFYPEER, 
					FALSE);

		curl_setopt($curl, 
					CURLOPT_USERAGENT, 
					DOWNLOAD_STATION_USER_AGENT);
		
		if ($IsLoadCookie) 
			curl_setopt($curl, 
						CURLOPT_COOKIEFILE, 
						$this->SIMPLYDEBRID_COOKIE);

		curl_setopt($curl, 
					CURLOPT_RETURNTRANSFER, 
					TRUE); 

		curl_setopt($curl, 
					CURLOPT_URL, 
					$this->Url); 
		$ret 										= curl_exec($curl);
		curl_close($curl);

		return $ret; 
	}

	private function extractCookies($string) 
	{
	    $cookies 									= array();
	    $lines 										= explode("\n", 
	    													  $string);
	 
	    // iterate over lines
	    foreach ($lines as $line) 
	    {
	        // we only care for valid cookie def lines
	        if (isset($line[0]) && substr_count($line, "\t") == 6) 
	        {
	            // get tokens in an array
	            $tokens 							= explode("\t", 
	            											  $line);
	 
	            // trim the tokens
	            $tokens 							= array_map('trim', 
	            												$tokens);
	            $cookie 							= array();
	 
	            // Extract the data
	            $cookie['domain'] 					= $tokens[0];
	            $cookie['flag'] 					= $tokens[1];
	            $cookie['path'] 					= $tokens[2];
	            $cookie['secure'] 					= $tokens[3];
	 
	            // Convert date to a readable format
	            $cookie['expiration'] 				= date('Y-m-d h:i:s', 
	            										   $tokens[4]);
	            $cookie['name'] 					= $tokens[5];
	            $cookie['value'] 					= $tokens[6];
	 
	            // Record the cookie.
	            $cookies[] 							= $cookie;
	        }
	    }
    
    	return $cookies;
	}

}





$t = new SynoFileHostingSimplyDebrid('http://uptobox.com/69hp63vvvnij', 'user','pwd', 'toto');

//$t->Verify(FALSE);

$t->GetDownloadInfo();

//echo 'toto2';


?>
	￼￼￼￼
