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
		$this->Url = 'http://z3raw.fr/inc/name.php?i='.$Url;
		$this->Username = $Username;
		$this->Password = $Password;
		$this->HostInfo = $HostInfo; 
	}
	
	//This function returns download url. 
	public function GetDownloadInfo() 
	{
		$ret = FALSE;
		$VerifyRet = $this->Verify(FALSE);

		$ret = $this->DownloadPremium($this->CookieValue);
		
		if (file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			unlink($this->SIMPLYDEBRID_COOKIE); 
		}
		
		echo($ret);

		return $ret; 

	}

	// Check if Customer have an account or free account
	public function Verify($ClearCookie)
	{
		$ret = LOGIN_FAIL;
		$this->CookieValue = FALSE;
		
		if (!empty($this->Username) && !empty($this->Password)) 
			$this->CookieValue = $this->SimplyDebridLogin($this->Username, $this->Password);

		// Error => go to end
		if ($this->CookieValue == FALSE) 
			goto End;
		/*if ($this->IsFreeAccount()) 
		{
			$ret = USER_IS_FREE; 
		} */
		else 
		{
			$ret = USER_IS_PREMIUM; 
		}

		End:
		
		/*if ($ClearCookie && file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			unlink($this->SIMPLYDEBRID_COOKIE); 
		}

		if(file_exists($this->SIMPLYDEBRID_PHPSESSID))
			unlink($this->SIMPLYDEBRID_PHPSESSID); */

		return $ret; 
	}


	private function SimplyDebridLogin($Username, $Password) 
	{
		$ret = FALSE;

		//Save cookie file
		/*$PostData = array('username'	=> 'titeuf767676', 
						  'password'	=> 'proutprout',
						  'submit'		=> 'Envoyer');*/

		//$PostData = array('username='.$this->Username.'&password='.$this->Password);

		//$PostData = array();

		/*print_r($PostData);

		echo 'user : '. $Username;
		echo 'pass : '. $Password;*/


		$queryUrl = $this->SIMPLYDEBRID_LOGIN_URL;
		//$PostData = http_build_query($PostData);

		if (!file_exists($this->SIMPLYDEBRID_PHPSESSID)) 
		{
			
			$curlsession = curl_init();
			//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curlsession, CURLOPT_USERAGENT, array("User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/20100101 Firefox/17.0") );
			curl_setopt($curlsession, CURLOPT_COOKIEJAR, $this->SIMPLYDEBRID_PHPSESSID);
			curl_setopt($curlsession, CURLOPT_HEADER, TRUE); 
			//curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
			curl_setopt($curlsession, CURLOPT_URL, $queryUrl); 
			$t = curl_exec($curlsession);
			curl_close($curlsession);
		}



		$curl = curl_init();
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		//curl_setopt($curl, CURLOPT_USERAGENT, array("User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:17.0) Gecko/20100101 Firefox/17.0") );
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, 'username='.$Username.'&password='.$Password.'&submit=Envoyer'); 
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->SIMPLYDEBRID_PHPSESSID);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->SIMPLYDEBRID_COOKIE);
		curl_setopt($curl, CURLOPT_HEADER, TRUE); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_URL, $queryUrl); 
		$LoginInfo = curl_exec($curl);
		curl_close($curl);
		

		//file_put_contents('/tmp/generate.php', $LoginInfo);

		if (FALSE != $LoginInfo && file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			//$ret = parse_cookiefile($this->SIMPLYDEBRID_COOKIE); 


			//print_r(extractCookies(file_get_contents($this->SIMPLYDEBRID_COOKIE)));


			$arrayCookie = extractCookies(file_get_contents($this->SIMPLYDEBRID_COOKIE));

			$arrayLogin = $arrayCookie[2];

			if(!is_null($arrayLogin['value']))
				$ret = $arrayLogin['value'];
			else
				$ret = FALSE; 


			//echo $ret;

			//echo 'ret : '. $content .'<br>' ;

			/*$lines = file($this->SIMPLYDEBRID_COOKIE);

			// var to hold output
			$trows = '';

			// iterate over lines
			foreach($lines as $LoginInfo) {

			  // we only care for valid cookie def lines
			  if($line[0] != '#' && substr_count($line, "\t") == 6) {

			    // get tokens in an array
			    $tokens = explode("\t", $line);

			    // trim the tokens
			    $tokens = array_map('trim', $tokens);

			    // let's convert the expiration to something readable
			    $tokens[4] = date('Y-m-d h:i:s', $tokens[4]);

			    // we can do different things with the tokens, here we build a table row
			    $trows .= '<tr></td>' . implode('</td><td>', $tokens) . '</td></tr>' . PHP_EOL;

			    // another option, make arrays to do things with later,
			    // we'd have to define the arrays beforehand to use this
			    // $domains[] = $tokens[0];
			    // flags[] = $tokens[1];
			    // and so on, and so forth

  				}
  			}

  			echo '<table>'.PHP_EOL.'<tbody>'.PHP_EOL.$trows.'</tbody>'.PHP_EOL.'</table>';

			*/
			
			//$ret = parse_cookiefile($this->SIMPLYDEBRID_COOKIE); 
/*
			if (!empty($ret['login'])) 
			{
				$ret = $ret['login']; 
				
			} 
			else 
			{
				$ret = FALSE; 
			}*/
		}

		return $ret; 
	}

	private function DownloadPremium($CookieValue) 
	{
		/*$DownloadID = $this->GetMegauploadDownloadID($this->Url); 

		if (FALSE == $DownloadID) 
		{
			return FALSE; 
		}*/
		
		$page = $this->DownloadParsePage(TRUE); 

		/*if (FALSE != $page) 
		{
			preg_match('/Unfortunately, the link you have clicked is not available./', $page, $nofile);
			
			if (!empty($nofile[0]))
			{ 
				$DownloadInfo[DOWNLOAD_ERROR] = ERR_FILE_NO_EXIST;
			}
		}
		else if (empty($DownloadInfo[DOWNLOAD_ERROR])) 
		{
			$returl = 'http://www.megaupload.com/mgr_dl.php?d='.$DownloadID.'&u='.$CookieValu e;
			$DownloadInfo = array();
			$DownloadInfo[DOWNLOAD_URL] = trim($returl); 
		}
		else
		{*/

		// Split response to get link part
		$page = explode('http://', $page);
		// Cut last html part to get only download link
		$DownloadInfo = explode('<', $page[2]);
		$DownloadInfo = $DownloadInfo[0];

//echo $DownloadInfo;

		//}

		
		return $DownloadInfo; 
	}

	private function DownloadParsePage($IsLoadCookie) 
	{
		$Option = array();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		
		if ($IsLoadCookie) 
			curl_setopt($curl, CURLOPT_COOKIEFILE, $this->SIMPLYDEBRID_COOKIE);

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_URL, $this->Url); 
		$ret = curl_exec($curl);
		curl_close($curl);

		return $ret; 
	}


}
		/*if (LOGIN_FAIL == $VerifyRet) 
		{
			$ret = $this->DownloadWaiting(FALSE);
		} 
		else if (USER_IS_FREE == $VerifyRet) 
		{ 
			$ret = $this->DownloadWaiting(TRUE);
		} 
		else 
		{
			$ret = $this->DownloadPremium($this->CookieValue);
		}

		if (file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			unlink($this->SIMPLYDEBRID_COOKIE); 
		}


		echo 'toto : '.$ret;

		return $ret; 
	}
	
	//This function verifies and returns account type. 
	public function Verify($ClearCookie)
	{
		$ret = LOGIN_FAIL;
		$this->CookieValue = FALSE;
		if (!empty($this->Username) && !empty($this->Password)) 
		{
			$this->CookieValue = $this->SimplyDebridLogin($this->Username, $this->Password);
		}

		if (FALSE == $this->CookieValue) 
		{ 
			goto End;
		}
		if ($this->IsFreeAccount()) 
		{
			$ret = USER_IS_FREE; 
		} 
		else 
		{
			$ret = USER_IS_PREMIUM; 
		}

		End:
		if ($ClearCookie && file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			unlink($this->SIMPLYDEBRID_COOKIE); 
		}

		return $ret; 
	}

	//This function performs login action.
	private function SimplyDebridLogin($Username, $Password) 
	{
		$ret = FALSE;
		//Save cookie file
		$PostData = array('username'=>$this->Username, 
						  'password'=>$this->Password);

		$queryUrl = $this->SIMPLYDEBRID_LOGIN_URL;
		$PostData = http_build_query($PostData);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		curl_setopt($curl, CURLOPT_POST, TRUE);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $PostData); 
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->SIMPLYDEBRID_COOKIE);
		curl_setopt($curl, CURLOPT_HEADER, TRUE); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_URL, $queryUrl); 
		$LoginInfo = curl_exec($curl);
		curl_close($curl);

		if (FALSE != $LoginInfo && file_exists($this->SIMPLYDEBRID_COOKIE)) 
		{
			$ret = parse_cookiefile($this->SIMPLYDEBRID_COOKIE); 

			if (!empty($ret['login'])) 
			{
				$ret = $ret['login']; 
			} 
			else 
			{
				$ret = FALSE; 
			}
		}
		return $ret; 
	}


	private function DownloadWaiting($IsLoadCookie)
	{
		$DownloadInfo = FALSE;
		$page = $this->DownloadParsePage($IsLoadCookie);

		if (FALSE != $page) 
		{
			preg_match('/http:\/\/(.*)" class="down_butt1"/', $page, $urlmatch);
			preg_match('/Unfortunately, the link you have clicked is not available./', $page, $nofile);
			if (!empty($nofile[0])) 
			{ 
				$DownloadInfo[DOWNLOAD_ERROR] = ERR_FILE_NO_EXIST;
		￼￼￼￼}
		
			preg_match('/count=(.*);/', $page, $countmatch); 
			if (isset($urlmatch[1])) 
			{
				$Href = 'http://'.$urlmatch[1];
				$DownloadInfo[DOWNLOAD_URL] = trim($Href); 
			}

			if (isset($countmatch[1])) 
			{ 
				$DownloadInfo[DOWNLOAD_COUNT] = trim($countmatch[1]);
			}
		
			$DownloadInfo[INFO_NAME] = trim($this->HostInfo[INFO_NAME]); 
		}
		
		return $DownloadInfo; 
	}

	private function DownloadParsePage($IsLoadCookie) 
	{
		$Option = array();
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		
		if ($IsLoadCookie) 
		{
			curl_setopt($curl, CURLOPT_COOKIEFILE, $this->SIMPLYDEBRID_COOKIE);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_URL, $this->Url); 
		$ret = curl_exec($curl);
		curl_close($curl);
		
		return $ret; 
	}

	private function IsFreeAccount() 
	{
		$ret = TRUE;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->SIMPLYDEBRID_COOKIE); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($curl, CURLOPT_URL, $this->SIMPLYDEBRID_ACCOUNT_URL); 
		$AccountRet = curl_exec($curl);
		curl_close($curl);
		$replace = array("\r\n", "\n", "\r");
		$AccountRet = str_replace($replace,'',$AccountRet); 
		preg_match('/Account type:(.*)<\/b>/', $AccountRet, $match); 

		if (isset($match[0])) 
		{
			$compare = strtolower($match[0]);
			if (strstr($compare, $this->SIMPLYDEBRID_PREMIUM_ACCOUNT_KEYWORD)) 
			{
				$ret = FALSE; 
			}
		}
		return $ret;
	}

	private function DownloadPremium($CookieValue) 
	{
		/*$DownloadID = $this->GetMegauploadDownloadID($this->Url); 

		if (FALSE == $DownloadID) 
		{
			return FALSE; 
		}*/
		
		//$page = $this->DownloadParsePage($IsLoadCookie); 

		/*if (FALSE != $page) 
		{
			preg_match('/Unfortunately, the link you have clicked is not available./', $page, $nofile);
			
			if (!empty($nofile[0]))
			{ 
				$DownloadInfo[DOWNLOAD_ERROR] = ERR_FILE_NO_EXIST;
			}
		}
		else if (empty($DownloadInfo[DOWNLOAD_ERROR])) 
		{
			$returl = 'http://www.megaupload.com/mgr_dl.php?d='.$DownloadID.'&u='.$CookieValu e;
			$DownloadInfo = array();
			$DownloadInfo[DOWNLOAD_URL] = trim($returl); 
		}
		else
		{
			$toto = explode('href=\"', $DownloadInfo);
			$DownloadInfo = explode('">', $toto);
		//}

		
		return $DownloadInfo; 
	}
	
	private function GetMegauploadDownloadID($Url) 
	{
		$ret = FALSE;
		preg_match('/\?d=([a-zA-Z0-9]+)/', $Url, $DownloadID); 
		if (!empty($DownloadID[1])) 
		{
			$ret = $DownloadID[1]; 
		}
		
		return $ret; 
	}
} */



function extractCookies($string) {
    $cookies = array();
    
    $lines = explode("\n", $string);
 
    // iterate over lines
    foreach ($lines as $line) {
 
        // we only care for valid cookie def lines
        if (isset($line[0]) && substr_count($line, "\t") == 6) {
 
            // get tokens in an array
            $tokens = explode("\t", $line);
 
            // trim the tokens
            $tokens = array_map('trim', $tokens);
 
            $cookie = array();
 
            // Extract the data
            $cookie['domain'] = $tokens[0];
            $cookie['flag'] = $tokens[1];
            $cookie['path'] = $tokens[2];
            $cookie['secure'] = $tokens[3];
 
            // Convert date to a readable format
            $cookie['expiration'] = date('Y-m-d h:i:s', $tokens[4]);
 
            $cookie['name'] = $tokens[5];
            $cookie['value'] = $tokens[6];
 
            // Record the cookie.
            $cookies[] = $cookie;
        }
    }
    
    return $cookies;
}


$t = new SynoFileHostingSimplyDebrid('http://uptobox.com/69hp63vvvnij', 'user','pwd', 'toto');

//$t->Verify(FALSE);

$t->GetDownloadInfo();

//echo 'toto2';


?>
	￼￼￼￼
