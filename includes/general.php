<? /*
    LibreSSL - CAcert web application
    Copyright (C) 2004-2008  CAcert Inc.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*/
	session_name("cacert");
	session_start();

	session_register("_config");
	session_register("profile");
	session_register("signup");
	session_register("lostpw");
//	if($_SESSION['profile']['id'] > 0)
//		session_regenerate_id();

	$pageLoadTime_Start = microtime(true);

	$junk = array(_("Face to Face Meeting"), _("Trusted Third Parties"), _("Thawte Points Transfer"), _("Administrative Increase"),
			_("CT Magazine - Germany"), _("Temporary Increase"), _("Unknown"));

	$_SESSION['_config']['errmsg']="";

	$id = 0; if(array_key_exists("id",$_REQUEST)) $id=intval($_REQUEST['id']);
	$oldid = 0; if(array_key_exists("oldid",$_REQUEST)) $oldid=intval($_REQUEST['oldid']);

	$_SESSION['_config']['filepath'] = "/www";

	require_once($_SESSION['_config']['filepath']."/includes/mysql.php");

	if(array_key_exists('HTTP_HOST',$_SERVER) &&
			$_SERVER['HTTP_HOST'] != $_SESSION['_config']['normalhostname'] &&
			$_SERVER['HTTP_HOST'] != $_SESSION['_config']['securehostname'] &&
			$_SERVER['HTTP_HOST'] != $_SESSION['_config']['tverify'] &&
			$_SERVER['HTTP_HOST'] != "stamp.cacert.org")
	{
		if(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS'] == "on")
			header("location: https://".$_SESSION['_config']['normalhostname']);
		else
			header("location: http://".$_SESSION['_config']['normalhostname']);
		exit;
	}

	if(array_key_exists('HTTP_HOST',$_SERVER) && 
			($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'] ||
			$_SERVER['HTTP_HOST'] == $_SESSION['_config']['tverify']))
	{
		if(array_key_exists('HTTPS',$_SERVER) && $_SERVER['HTTPS'] == "on")
		{
		}
		else
		{
			if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['securehostname'])
			header("location: https://". $_SESSION['_config']['securehostname']);
			if($_SERVER['HTTP_HOST'] == $_SESSION['_config']['tverify'])
			header("location: https://".$_SESSION['_config']['tverify']);
			exit;
		}
	}

	$lang = "";
	if(array_key_exists("lang",$_REQUEST))
	  $lang=mysql_escape_string(substr(trim($_REQUEST['lang']), 0, 5));
	if($lang != "")
		$_SESSION['_config']['language'] = $lang;

	//if($_SESSION['profile']['id'] == 1 && 1 == 2)
	//	echo $_SESSION['_config']['language'];

	$_SESSION['_config']['translations'] = array(
				"ar_JO" => "&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;",
				"bg_BG" => "&#1041;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080;",
				"cs_CZ" => "&#268;e&scaron;tina",
				"da_DK" => "Dansk",
				"de_DE" => "Deutsch",
				"el_GR" => "&Epsilon;&lambda;&lambda;&eta;&nu;&iota;&kappa;&#940;",
				"en_AU" => "English",
				"eo_EO" => "Esperanto",
				"es_ES" => "Espa&#xf1;ol",
				"fa_IR" => "Farsi",
				"fi_FI" => "Suomi",
				"fr_FR" => "Fran&#xe7;ais",
				"he_IL" => "&#1506;&#1489;&#1512;&#1497;&#1514;",
				"hr_HR" => "Hrvatski",
				"hu_HU" => "Magyar",
				"is_IS" => "&Iacute;slenska",
				"it_IT" => "Italiano",
				"ja_JP" => "&#26085;&#26412;&#35486;",
				"ka_GE" => "Georgian",
				"nl_NL" => "Nederlands",
				"pl_PL" => "Polski",
				"pt_PT" => "Portugu&#xea;s",
				"pt_BR" => "Portugu&#xea;s Brasileiro",
				"ru_RU" => "&#x420;&#x443;&#x441;&#x441;&#x43a;&#x438;&#x439;",
				"ro_RO" => "Rom&acirc;n&#259;",
				"sv_SE" => "Svenska",
				"tr_TR" => "T&#xfc;rk&#xe7;e",
				"zh_CN" => "&#x4e2d;&#x6587;(&#x7b80;&#x4f53;)");

        $value=array();

	if(!(array_key_exists('language',$_SESSION['_config']) && $_SESSION['_config']['language'] != ""))
	{
		$bits = explode(",", strtolower(str_replace(" ", "", mysql_real_escape_string(array_key_exists('HTTP_ACCEPT_LANGUAGE',$_SERVER)?$_SERVER['HTTP_ACCEPT_LANGUAGE']:""))));
		foreach($bits as $lang)
		{
			$b = explode(";", $lang);
			if(count($b)>1 && substr($b[1], 0, 2) == "q=")
				$c = floatval(substr($b[1], 2));
			else
				$c = 1;
			$value["$c"] = trim($b[0]);
		}

		krsort($value);

		reset($value);

		foreach($value as $key => $val)
		{
			$val = substr(escapeshellarg($val), 1, -1);
			$short = substr($val, 0, 2);
			if($val == "en" || $short == "en")
			{
				$_SESSION['_config']['language'] = "en";
				break;
			}
			if(file_exists($_SESSION['_config']['filepath']."/locale/$val/LC_MESSAGES/messages.mo"))
			{
				$_SESSION['_config']['language'] = $val;
				break;
			}
			if(file_exists($_SESSION['_config']['filepath']."/locale/$short/LC_MESSAGES/messages.mo"))
			{
				$_SESSION['_config']['language'] = $short;
				break;
			}
		}
	}
	if(!array_key_exists('_config',$_SESSION) || !array_key_exists('language',$_SESSION['_config']) || strlen($_SESSION['_config']['language']) != 5)
	{
		$lang = array_key_exists('language',$_SESSION['_config'])?$_SESSION['_config']['language']:"";
		$_SESSION['_config']['language'] = "en_AU";
		foreach($_SESSION['_config']['translations'] as $key => $val)
		{
			if(substr($lang, 0, 2) == substr($key, 0, 2))
			{
				$_SESSION['_config']['language'] = $val;
				break;
			}
		}
	}

	$_SESSION['_config']['recode'] = "html..latin-1";
	if($_SESSION['_config']['language'] == "zh_CN")
	{
		$_SESSION['_config']['recode'] = "html..gb2312";
	} else if($_SESSION['_config']['language'] == "pl_PL" || $_SESSION['_config']['language'] == "hu_HU") {
		$_SESSION['_config']['recode'] = "html..ISO-8859-2";
	} else if($_SESSION['_config']['language'] == "ja_JP") {
		$_SESSION['_config']['recode'] = "html..SHIFT-JIS";
	} else if($_SESSION['_config']['language'] == "ru_RU") {
		$_SESSION['_config']['recode'] = "html..ISO-8859-5";
	} else if($_SESSION['_config']['language'] == "lt_LT") {
		$_SESSION['_config']['recode'] = "html..ISO-8859-13";
	}

	putenv("LANG=".$_SESSION['_config']['language']);
	setlocale(LC_ALL, $_SESSION['_config']['language']);
	$domain = 'messages';
	bindtextdomain($domain, $_SESSION['_config']['filepath']."/locale");
	textdomain($domain);

	//if($_SESSION['profile']['id'] == -1)
	//	echo $_SESSION['_config']['language']." - ".$_SESSION['_config']['filepath']."/locale";


        if(array_key_exists('profile',$_SESSION) && is_array($_SESSION['profile']) && array_key_exists('id',$_SESSION['profile']) && $_SESSION['profile']['id'] > 0)
	{
		$locked = mysql_fetch_assoc(mysql_query("select `locked` from `users` where `id`='".$_SESSION['profile']['id']."'"));
		if($locked['locked'] == 0)
		{
			$query = "select sum(`points`) as `total` from `notary` where `to`='".$_SESSION['profile']['id']."' group by `to`";
			$res = mysql_query($query);
			$row = mysql_fetch_assoc($res);
			$_SESSION['profile']['points'] = $row['total'];
		} else {
			$_SESSION['profile'] = "";
			unset($_SESSION['profile']);
		}
	}

	function loadem($section = "index")
	{
		if($section != "index" && $section != "account" && $section != "tverify")
		{
			$section = "index";
		}

		if($section == "account")
			include_once($_SESSION['_config']['filepath']."/includes/account_stuff.php");

		if($section == "index")
			include_once($_SESSION['_config']['filepath']."/includes/general_stuff.php");

		if($section == "tverify")
			include_once($_SESSION['_config']['filepath']."/includes/tverify_stuff.php");
	}

	function includeit($id = "0", $section = "index")
	{
		$id = intval($id);
		if($section != "index" && $section != "account" && $section != "wot" && $section != "help" && $section != "gpg" && $section != "disputes" && $section != "tverify" && $section != "advertising")
		{
			$section = "index";
		}

		if($section == "tverify" && file_exists($_SESSION['_config']['filepath']."/tverify/index/$id.php"))
			include_once($_SESSION['_config']['filepath']."/tverify/index/$id.php");
		else if(file_exists($_SESSION['_config']['filepath']."/pages/$section/$id.php"))
			include_once($_SESSION['_config']['filepath']."/pages/$section/$id.php");
		else {
			$id = "0";

			if(file_exists($_SESSION['_config']['filepath']."/pages/$section/$id.php"))
				include_once($_SESSION['_config']['filepath']."/pages/$section/$id.php");
			else {

				$section = "index";
				$id = "0";

				if(file_exists($_SESSION['_config']['filepath']."/pages/$section/$id.php"))
					include_once($_SESSION['_config']['filepath']."/pages/$section/$id.php");
				else
					include_once($_SESSION['_config']['filepath']."/www/error404.php");
			}
		}
	}

	function checkpw($pwd, $email, $fname, $mname, $lname, $suffix)
	{
		$points = 0;

		if(strlen($pwd) > 15)
			$points++;
		if(strlen($pwd) > 20)
			$points++;
		if(strlen($pwd) > 25)
			$points++;
		if(strlen($pwd) > 30)
			$points++;

		//echo "Points due to length: $points<br/>";

		if(preg_match("/\d/", $pwd))
			$points++;

		if(preg_match("/[a-z]/", $pwd))
			$points++;

		if(preg_match("/[A-Z]/", $pwd))
			$points++;

		if(preg_match("/\W/", $pwd))
			$points++;

		if(preg_match("/\s/", $pwd))
			$points++;

		//echo "Points due to length and charset: $points<br/>";

		if(@strstr(strtolower($pwd), strtolower($email)))
			$points--;

		if(@strstr(strtolower($email), strtolower($pwd)))
			$points--;

		if(@strstr(strtolower($pwd), strtolower($fname)))
			$points--;

		if(@strstr(strtolower($fname), strtolower($pwd)))
			$points--;

		if($mname)
		if(@strstr(strtolower($pwd), strtolower($mname)))
			$points--;

		if($mname)
		if(@strstr(strtolower($mname), strtolower($pwd)))
			$points--;

		if(@strstr(strtolower($pwd), strtolower($lname)))
			$points--;

		if(@strstr(strtolower($lname), strtolower($pwd)))
			$points--;

		if($suffix)
		if(@strstr(strtolower($pwd), strtolower($suffix)))
			$points--;

		if($suffix)
		if(@strstr(strtolower($suffix), strtolower($pwd)))
			$points--;

		//echo "Points due to name matches: $points<br/>";

		$do = `grep '$pwd' /usr/share/dict/american-english`;
		if($do)
			$points--;

		//echo "Points due to wordlist: $points<br/>";

		return($points);
	}

	function extractit()
	{
		$bits = explode(": ", $_SESSION['_config']['subject'], 2);
		$bits = str_replace(", ", "|", str_replace("/", "|", array_key_exists('1',$bits)?$bits['1']:""));
		$bits = explode("|", $bits);    

		$_SESSION['_config']['cnc'] = $_SESSION['_config']['subaltc'] = 0;
		$_SESSION['_config']['OU'] = "";

		if(is_array($bits))
		foreach($bits as $val)
		{
			if(!strstr($val, "="))
				continue;

			$split = explode("=", $val);

			$k = $split[0];
			$split['1'] = trim($split['1']);
			if($k == "CN" && $split['1'])
			{
				$k = $_SESSION['_config']['cnc'].".".$k;
				$_SESSION['_config']['cnc']++;
				$_SESSION['_config'][$k] = $split['1'];
			}
			if($k == "OU" && $split['1'] && $_SESSION['_config']['OU'] == "")
			{
				$_SESSION['_config']['OU'] = $split['1'];
			}
			if($k == "subjectAltName" && $split['1'])
			{
				$k = $_SESSION['_config']['subaltc'].".".$k;
				$_SESSION['_config']['subaltc']++;
				$_SESSION['_config'][$k] = $split['1'];
			}
		}
	}

	function getcn()
	{
		unset($_SESSION['_config']['rows']);
		unset($_SESSION['_config']['rowid']);
		unset($_SESSION['_config']['rejected']);
		$rows=array();
		$rowid=array();
		for($cnc = 0; $cnc < $_SESSION['_config']['cnc']; $cnc++)
		{
			$CN = $_SESSION['_config']["$cnc.CN"];
			$bits = explode(".", $CN);
			$dom = "";
			$cnok = 0;
			for($i = count($bits) - 1; $i >= 0; $i--)
			{
				if($dom)
					$dom = $bits[$i].".".$dom;
				else
					$dom = $bits[$i];
				$_SESSION['_config']['row'] = "";
				$dom = mysql_real_escape_string($dom);
				$query = "select * from domains where `memid`='".$_SESSION['profile']['id']."' and `domain` like '$dom' and `deleted`=0 and `hash`=''";
				$res = mysql_query($query);
				if(mysql_num_rows($res) > 0)
				{
					$cnok = 1;
					$_SESSION['_config']['row'] = mysql_fetch_assoc($res);
					$rowid[] = $_SESSION['_config']['row']['id'];
					break;
				}
			}

			if($cnok == 0)
				$_SESSION['_config']['rejected'][] = $CN;

			if($_SESSION['_config']['row'] != "")
				$rows[] = $CN;
		}
//		if(count($rows) <= 0)
//		{
//			echo _("There were no valid CommonName fields on the CSR, or I was unable to match any of these against your account. Please review your CSR, or add and verify domains contained in it to your account before trying again.");
//			exit;
//		}

		$_SESSION['_config']['rows'] = $rows;
		$_SESSION['_config']['rowid'] = $rowid;
	}

	function getalt()
	{
		unset($_SESSION['_config']['altrows']);
		unset($_SESSION['_config']['altid']);
		$altrows=array();
		$altid=array();
		for($altc = 0; $altc < $_SESSION['_config']['subaltc']; $altc++)
		{
			$subalt = $_SESSION['_config']["$altc.subjectAltName"];
			if(substr($subalt, 0, 4) == "DNS:")
				$alt = substr($subalt, 4);
			else
				continue;

			$bits = explode(".", $alt);
			$dom = "";
			$altok = 0;
			for($i = count($bits) - 1; $i >= 0; $i--)
			{
				if($dom)
					$dom = $bits[$i].".".$dom;
				else
					$dom = $bits[$i];
				$_SESSION['_config']['altrow'] = "";
				$dom = mysql_real_escape_string($dom);
				$query = "select * from domains where `memid`='".$_SESSION['profile']['id']."' and `domain` like '$dom' and `deleted`=0 and `hash`=''";
				$res = mysql_query($query);
				if(mysql_num_rows($res) > 0)
				{
					$altok = 1;
					$_SESSION['_config']['altrow'] = mysql_fetch_assoc($res);
					$altid[] = $_SESSION['_config']['altrow']['id'];
					break;
				}
			}

			if($altok == 0)
				$_SESSION['_config']['rejected'][] = $alt;

			if($_SESSION['_config']['altrow'] != "")
				$altrows[] = $subalt;
		}
		$_SESSION['_config']['altrows'] = $altrows;
		$_SESSION['_config']['altid'] = $altid;
	}

	function getcn2()
	{
		$rows=array();
		$rowid=array();
		for($cnc = 0; $cnc < $_SESSION['_config']['cnc']; $cnc++)
		{
			$CN = $_SESSION['_config']["$cnc.CN"];
			$bits = explode(".", $CN);
			$dom = "";
			for($i = count($bits) - 1; $i >= 0; $i--)
			{
				if($dom)
					$dom = $bits[$i].".".$dom;
				else
					$dom = $bits[$i];
				$_SESSION['_config']['row'] = "";
				$dom = mysql_real_escape_string($dom);
				$query = "select *, `orginfo`.`id` as `id` from `orginfo`,`orgdomains`,`org` where
						`org`.`memid`='".$_SESSION['profile']['id']."' and
						`org`.`orgid`=`orginfo`.`id` and
						`orgdomains`.`orgid`=`orginfo`.`id` and
						`orgdomains`.`domain`='$dom'";
				$res = mysql_query($query);
				if(mysql_num_rows($res) > 0)
				{
					$_SESSION['_config']['row'] = mysql_fetch_assoc($res);
					$rowid[] = $_SESSION['_config']['row']['id'];
					break;
				}
			}

			if($_SESSION['_config']['row'] != "")
				$rows[] = $CN;
		}
//		if(count($rows) <= 0)
//		{
//			echo _("There were no valid CommonName fields on the CSR, or I was unable to match any of these against your account. Please review your CSR, or add and verify domains contained in it to your account before trying again.");
//			exit;
//		}
		$_SESSION['_config']['rows'] = $rows;
		$_SESSION['_config']['rowid'] = $rowid;
	}

	function getalt2()
	{
		$altrows=array();
		$altid=array();
		for($altc = 0; $altc < $_SESSION['_config']['subaltc']; $altc++)
		{
			$subalt = $_SESSION['_config']["$altc.subjectAltName"];
			if(substr($subalt, 0, 4) == "DNS:")
				$alt = substr($subalt, 4);
			else
				continue;

			$bits = explode(".", $alt);
			$dom = "";
			for($i = count($bits) - 1; $i >= 0; $i--)
			{
				if($dom)
					$dom = $bits[$i].".".$dom;
				else
					$dom = $bits[$i];
				$_SESSION['_config']['altrow'] = "";
				$dom = mysql_real_escape_string($dom);
				$query = "select * from `orginfo`,`orgdomains`,`org` where
						`org`.`memid`='".$_SESSION['profile']['id']."' and
						`org`.`orgid`=`orginfo`.`id` and
						`orgdomains`.`orgid`=`orginfo`.`id` and
						`orgdomains`.`domain`='$dom'";
				$res = mysql_query($query);
				if(mysql_num_rows($res) > 0)
				{
					$_SESSION['_config']['altrow'] = mysql_fetch_assoc($res);
					$altid[] = $_SESSION['_config']['altrow']['id'];
					break;
				}
			}

			if($_SESSION['_config']['altrow'] != "")
				$altrows[] = $subalt;
		}
		$_SESSION['_config']['altrows'] = $altrows;
		$_SESSION['_config']['altid'] = $altid;
	}

	function checkownership($hostname)
	{
		$bits = explode(".", $hostname);
		$dom = "";
		for($i = count($bits) - 1; $i >= 0; $i--)
		{
			if($dom)
				$dom = $bits[$i].".".$dom;
			else
				$dom = $bits[$i];
			$dom = mysql_real_escape_string($dom);
			$query = "select * from `org`,`orgdomains`,`orginfo`
					where `org`.`memid`='".$_SESSION['profile']['id']."'
					and `orgdomains`.`orgid`=`org`.`orgid`
					and `orginfo`.`id`=`org`.`orgid`
					and `orgdomains`.`domain`='$dom'";
			$res = mysql_query($query);
			if(mysql_num_rows($res) > 0)
			{
				$_SESSION['_config']['row'] = mysql_fetch_assoc($res);
				return(true);
			}
		}
		return(false);
	}

	function maxpoints($id = 0)
	{
		if($id <= 0)
			$id = $_SESSION['profile']['id'];

		$query = "select sum(`points`) as `points` from `notary` where `to`='$id' group by `to`";
		$row = mysql_fetch_assoc(mysql_query($query));
		$points = $row['points'];

		$dob = date("Y-m-d", mktime(0,0,0,date("m"),date("d"),date("Y")-18));
		$query = "select * from `users` where `id`='".$_SESSION['profile']['id']."' and `dob` < '$dob'";
		if(mysql_num_rows(mysql_query($query)) < 1)
		{
			if($points >= 100)
				return(10);
			else
				return(0);
		}

		if($points >= 300)
			return(200);
		if($points >= 200)
			return(150);
		if($points >= 150)
			return(35);
		if($points >= 140)
			return(30);
		if($points >= 130)
			return(25);
		if($points >= 120)
			return(20);
		if($points >= 110)
			return(15);
		if($points >= 100)
			return(10);
		return(0);
	}

	function hex2bin($data)
	{
		while(strstr($data, "\\x"))
		{
			$pos = strlen($data) - strlen(strstr($data, "\\x"));
			$before = substr($data, 0, $pos);
			$char = chr(hexdec(substr($data, $pos + 2, 2)));
			$after = substr($data, $pos + 4);
			$data = $before.$char.$after;
		}
		return(utf8_decode($data));
	}

	function screenshot($img)
	{
		if(file_exists("../screenshots/".$_SESSION['_config']['language']."/$img"))
			return("/screenshots/".$_SESSION['_config']['language']."/$img");
		else
			return("/screenshots/en/$img");
	}

	function signmail($to, $subject, $message, $from, $replyto = "")
	{
		if($replyto == "")
			$replyto = $from;
		$tmpfname = tempnam("/tmp", "CSR");
		$fp = fopen($tmpfname, "w");
		fputs($fp, $message);
		fclose($fp);
		$do = `/usr/bin/gpg --homedir /home/gpg --clearsign "$tmpfname"|/usr/sbin/sendmail "$to"`;
		@unlink($tmpfname);
	}

	function checkEmail($email)
	{
		$myemail = mysql_real_escape_string($email);
		if(preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\+\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/" , $email))
		{
			list($username,$domain)=split('@',$email);
			$dom = escapeshellarg($domain);
			$line = trim(`dig +short MX $dom 2>&1`);
#echo $email."-$dom-$line-\n";
#echo `dig +short mx heise.de 2>&1`."-<br>\n";

			$list = explode("\n", $line);
			foreach($list as $row)
				list($pri, $mxhosts[]) = explode(" ", substr(trim($row), 0, -1));
			$mxhosts[] = $domain;
#print_r($mxhosts); die;
			foreach($mxhosts as $key => $domain)
			{
				$fp = @fsockopen($domain,25,$errno,$errstr,5);
				if($fp)
				{
				
					$line = fgets($fp, 4096);
                                        while(substr($line, 0, 4) == "220-")
                                               $line = fgets($fp, 4096);
					if(substr($line, 0, 3) != "220")
						continue;
					fputs($fp, "HELO www.cacert.org\r\n");
					$line = fgets($fp, 4096);
					while(substr($line, 0, 3) == "220")
						$line = fgets($fp, 4096);
					if(substr($line, 0, 3) != "250")
						continue;
					fputs($fp, "MAIL FROM: <returns@cacert.org>\r\n");
					$line = fgets($fp, 4096);

					if(substr($line, 0, 3) != "250")
						continue;
					fputs($fp, "RCPT TO: <$email>\r\n");
					$line = trim(fgets($fp, 4096));
					fputs($fp, "QUIT\r\n");
					fclose($fp);

					$line = mysql_real_escape_string(trim(strip_tags($line)));
					$query = "insert into `pinglog` set `when`=NOW(), `email`='$myemail', `result`='$line'";
					if(is_array($_SESSION['profile'])) $query.=", `uid`='".$_SESSION['profile']['id']."'";
					mysql_query($query);

					if(substr($line, 0, 3) != "250")
						return $line;
					else
						return "OK";
				}
			}
		}
		$query = "insert into `pinglog` set `when`=NOW(), `uid`='".$_SESSION['profile']['id']."',
				`email`='$myemail', `result`='Failed to make a connection to the mail server'";
		mysql_query($query);
		return _("Failed to make a connection to the mail server");
	}

	function waitForResult($table, $certid, $id = 0, $show = 1)
	{
		$found = $trycount = 0;
		if($certid<=0)
		{
			if($show) showheader(_("My CAcert.org Account!"));
			echo _("ERROR: The new Certificate ID is wrong. Please contact support.\n");
			if($show) showfooter();
			if($show) exit;
			return;
		}
		while($trycount++ <= 40)
		{
			if($table == "gpg")
				$query = "select * from `$table` where `id`='".intval($certid)."' and `crt` != ''";
			else
				$query = "select * from `$table` where `id`='".intval($certid)."' and `crt_name` != ''";
			$res = mysql_query($query);
			if(mysql_num_rows($res) > 0)
			{
				$found = 1;
				break;
			}
			sleep(3);
		}

		if(!$found)
		{
			if($show) showheader(_("My CAcert.org Account!"));
			$query = "select * from `$table` where `id`='".intval($certid)."' ";
			$res = mysql_query($query);
			$body="";
			$subject="";
			if(mysql_num_rows($res) > 0)
			{
				printf(_("Your certificate request is still queued and hasn't been processed yet. Please wait, and go to Certificates -> View to see it's status."));
				$subject="[CAcert.org] Certificate TIMEOUT";
				$body = "A certificate has timed out!\n\n";
			}
			else
			{
				printf(_("Your certificate request has failed to be processed correctly, see %sthe WIKI page%s for reasons and solutions.")." certid:$table:".intval($certid), "<a href='http://wiki.cacert.org/wiki/FAQ/CertificateRenewal'>", "</a>");
				$subject="[CAcert.org] Certificate FAILURE";
				$body = "A certificate has failed: $table $certid $id $show\n\n";
			}

			$body .= _("Best regards")."\n"._("CAcert.org Support!");

			sendmail("philipp@cacert.org", $subject, $body, "returns@cacert.org", "", "", "CAcert Support");

			if($show) showfooter();
			if($show) exit;
		}
	}



	function generateTicket()
	{
		$query = "insert into tickets (timestamp) values (now()) ";
		mysql_query($query);
		$ticket = mysql_insert_id();
		return $ticket;
	}

	function sanitizeHTML($input) 
	{
		return htmlentities(strip_tags($input), ENT_QUOTES);
		//In case of problems, please use the following line again:
		//return htmlentities(strip_tags(utf8_decode($input)), ENT_QUOTES);
		//return htmlspecialchars(strip_tags($input));
	}

	function make_hash()
	{
		if(function_exists("dio_open"))
		{
			$rnd = dio_open("/dev/urandom",O_RDONLY);
			$hash = md5(dio_read($rnd,64));
			dio_close($rnd);
		} else {
			$rnd = fopen("/dev/urandom", "r");
			$hash = md5(fgets($rnd, 64));
			fclose($rnd);
		}
		return($hash);
	}

	function csrf_check($nam, $show=1)
        {
		if(!array_key_exists('csrf',$_REQUEST) || !array_key_exists('csrf_'.$nam,$_SESSION))
		{
			showheader(_("My CAcert.org Account!"));
			echo _("CSRF Hash is missing. Please try again.")."\n";
			showfooter();
			exit();
		}
		if(strlen($_REQUEST['csrf'])!=32)
		{
			showheader(_("My CAcert.org Account!"));
			echo _("CSRF Hash is wrong. Please try again.")."\n";
			showfooter();
			exit();
		}
		if(!array_key_exists($_REQUEST['csrf'],$_SESSION['csrf_'.$nam]))
		{
			showheader(_("My CAcert.org Account!"));
			echo _("CSRF Hash is wrong. Please try again.")."\n";
			showfooter();
			exit();
		}
        }
        function make_csrf($nam)
        {
                $hash=make_hash();
                $_SESSION['csrf_'.$nam][$hash]=1;
                return($hash);
        }

	function clean_csr($CSR)
	{
		$newcsr = str_replace("\r\n","\n",trim($CSR));
		$newcsr = str_replace("\n\n","\n",$newcsr);
		return(preg_replace("/[^A-Za-z0-9\n\r\-\:\=\+\/ ]/","",$newcsr));
	}
	function clean_gpgcsr($CSR)
	{
		return(preg_replace("/[^A-Za-z0-9\n\r\-\:\=\+\/ ]/","",trim($CSR)));
	}

	function sanitizeFilename($text)
	{
		$text=preg_replace("/[^\w-.@]/","",$text);
		return($text);
	}

	function fix_assurer_flag($userID)
	{
		// Update Assurer-Flag on users table if 100 points. Should the number of points be SUM(points) or SUM(awarded)?
		$query = mysql_query('UPDATE `users` AS `u` SET `assurer` = 1 WHERE `u`.`id` = \''.(int)intval($userID).
			 '\' AND EXISTS(SELECT 1 FROM `cats_passed` AS `tp`, `cats_variant` AS `cv` WHERE `tp`.`variant_id` = `cv`.`id` AND `cv`.`type_id` = 1 AND `tp`.`user_id` = `u`.`id`)'.
			 ' AND (SELECT SUM(`points`) FROM `notary` AS `n` WHERE `n`.`to` = `u`.`id` AND `expire` < now()) >= 100'); // Challenge has been passed and non-expired points >= 100
	 
		// Reset flag if requirements are not met
		$query = mysql_query('UPDATE `users` AS `u` SET `assurer` = 0 WHERE `u`.`id` = \''.(int)intval($userID).
			'\' AND (NOT EXISTS(SELECT 1 FROM `cats_passed` AS `tp`, `cats_variant` AS `cv` WHERE `tp`.`variant_id` = `cv`.`id` AND `cv`.`type_id` = 1 AND `tp`.`user_id` = `u`.`id`)'.
			 ' OR (SELECT SUM(`points`) FROM `notary` AS `n` WHERE `n`.`to` = `u`.`id` AND `n`.`expire` < now()) < 100)');
	}
	
	// returns 0 if $userID is an Assurer
	// Otherwise :
	//	 Bit 0 is always set
	//	 Bit 1 is set if 100 Assurance Points are not reached
	//	 Bit 2 is set if Assurer Test is missing
	//	 Bit 3 is set if the user is not allowed to be an Assurer (assurer_blocked > 0)
	function get_assurer_status($userID)
	{
		$Result = 0;
		$query = mysql_query('SELECT * FROM `cats_passed` AS `tp`, `cats_variant` AS `cv` '.
			'  WHERE `tp`.`variant_id` = `cv`.`id` AND `cv`.`type_id` = 1 AND `tp`.`user_id` = \''.(int)intval($userID).'\'');
		if(mysql_num_rows($query) < 1)
		{
			$Result |= 5;
		}
		
		$query = mysql_query('SELECT SUM(`points`) AS `points` FROM `notary` AS `n` WHERE `n`.`to` = \''.(int)intval($userID).'\' AND `n`.`expire` < now()');
		$row = mysql_fetch_assoc($query);
		if ($row['points'] < 100) {
			$Result |= 3;
		}
		
		$query = mysql_query('SELECT `assurer_blocked` FROM `users` WHERE `id` = \''.(int)intval($userID).'\'');
		$row = mysql_fetch_assoc($query);
		if ($row['assurer_blocked'] > 0) {
			$Result |= 9;
		}
		
		return $Result;
	}
	
	// returns text message to be shown to the user given the result of is_no_assurer
	function no_assurer_text($Status)
	{
		if ($Status == 0) {
			$Result = _("You have passed the Assurer Challenge and collected at least 100 Assurance Points, you are an Assurer.");
		} elseif ($Status == 3) {
			$Result = _("You have passed the Assurer Challenge, but to become an Assurer you still have to reach 100 Assurance Points!");
		} elseif ($Status == 5) {
			$Result = _("You have at least 100 Assurance Points, if you want to become an assurer try the").' <a href="https://cats.cacert.org/">'._("Assurer Challenge").'</a>!';
		} elseif ($Status == 7) {
			$Result = _("To become an Assurer you have to collect 100 Assurance Points and pass the").' <a href="https://cats.cacert.org/">'._("Assurer Challenge").'</a>!';
		} elseif ($Status & 8 > 0) {
			$Result = _("Sorry, you are not allowed to be an Assurer. Please contact").' <a href="mailto:cacert-support@lists.cacert.org">cacert-support@lists.cacert.org</a>'._(" if you feel that this is not corect.");
		} else {
			$Result = _("You are not an Assurer, but the reason is not stored in the database. Please contact").' <a href="mailto:cacert-support@lists.cacert.org">cacert-support@lists.cacert.org</a>.';
		}
		return $Result;
	}

	function is_assurer($userID)
	{
               if (get_assurer_status($userID))
                       return 0;
               else
                       return 1;
	}

	function get_assurer_reason($userID)
	{
               return no_assurer_text(get_assurer_status($userID));
	}

	function generatecertpath($type,$kind,$id)
	{
		$name="../$type/$kind-".intval($id).".$type";
		$newlayout=1;
		if($newlayout)
		{
			$name="../$type/$kind/".intval($id/1000)."/$kind-".intval($id).".$type";
			mkdir("../csr/$kind",0777);
			mkdir("../crt/$kind",0777);
			mkdir("../csr/$kind/".intval($id/1000));
			mkdir("../crt/$kind/".intval($id/1000));
		}
		return $name;
	}

	/**
	  * Run the sql query given in $sql.
	  * The resource returned by mysql_query is
	  * returned by this function.
	  *
	  * It should be safe to replace every mysql_query
	  * call by a mysql_extended_query call.
	  */
	function mysql_timed_query($sql)
	{
		global $sql_data_log;
		$query_start = microtime(true);
		$res = mysql_query($sql);
		$query_end = microtime(true);
		$sql_data_log[] = array("sql" => $sql, "duration" => $query_end - $query_start);
		return $res;
	}

?>