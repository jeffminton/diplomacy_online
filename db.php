<?php
//db.php database class

class db
{
	function connect()
	{
		global $connection;
		
		$dbName = "diplomacy";
		$host = "localhost";
		$user = "diplomacy";
		$pass = "hhhbbthhAG88773dip";

		$connection = mysql_connect($host, $user, $pass)
			or die("Cannot connect to $host as $user:" . mysql_error());
   
		mysql_select_db($dbName)
			or die ("Cannot open $dbName:" . mysql_error());

	}

	function addUser()
	{
		$uid = $_POST["uid"];
		$pwd = $_POST["pwd1"];
		$email = $_POST["email"];
		$fname = $_POST["fname"];
		$minit = $_POST["minit"];
		$lname = $_POST["lname"];

		$salt = mt_rand();
		$salt = (string) $salt;

		$pwd = md5(md5($pwd) . $salt);

		//echo $pwd;

		$query = "
			INSERT INTO user (uid, salt, pwd, f_name, l_name, m_init, email)
			VALUES ('$uid', $salt, '$pwd', '$fname', '$lname', '$minit', '$email');";

		$result = mysql_query($query) or die("db access error" . mysql_error());

		return $result;
	}

	function userExists()
	{
		$uid = $_POST["uid"];

		$query = "
			SELECT *
			FROM user
			WHERE uid='$uid';";

		$result = mysql_query($query) or die("db access error" . mysql_error());

		$exists = (mysql_numrows($result) == 1) ? true : false;

		return $exists;
	}

	//return true if credentials match existing user, otherwise return false
	function checkUser()
	{
		$uid = $_POST["uid"];
		
		$query = "
			SELECT *
			FROM user
			WHERE uid='$uid';";
			
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$salt = mysql_result($result, 0, "salt");
		$pwd = $_POST["pwd"];
		$salt = (string) $salt;

		$pwd = md5(md5($pwd) . $salt);
		
		$query = "
			SELECT *
			FROM user
			WHERE uid='$uid' and pwd='$pwd';";
			
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$exists = (mysql_numrows($result) == 1) ? true : false;

		return $exists;
	}
	
	function getPlayersGames()
	{
		$uid = $_SESSION["uid"];
		$games = array();
		
		$query = "
			SELECT DISTINCT g.gid, g.year, g.season, g.players, g.running
			FROM games g, in_game i
			WHERE g.gid = i.gid
			AND i.uid = 'test6'
			AND i.uid <>
			ALL (

				SELECT DISTINCT o.uid
				FROM games g1, orders o
				WHERE g1.gid = g.gid
				AND g.gid = o.gid
				AND g.year = o.year
				AND g.season = o.season
				)";
			
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($games, mysql_fetch_assoc($result));
		}
		
		return $games;
	}
	
	function getWaitingGames()
	{
		$uid = $_SESSION['uid'];
		
		$query = "SELECT DISTINCT g.gid, g.year, g.season, g.players
					FROM games g, in_game i
					WHERE g.running=false and g.gid=i.gid and not i.uid='$uid';";
		
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$games = array();
		
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($games, mysql_fetch_assoc($result));
		}
		
		return $games;
	}
	
	function addGame()
    {	
		$uid = $_SESSION['uid'];
		$gid = time();
		$ct = $_POST['country'];
        $query = "INSERT INTO games(gid, year, season, running, players)
                    VALUES($gid, 1901, 's', false, 1);";
                    
        $result = mysql_query($query) or die("db access error" . mysql_error());
        
        $query = "INSERT INTO in_game(uid, gid, country)
                    VALUES('$uid', $gid, '$ct');";
                    
        $result = mysql_query($query) or die("db access error" . mysql_error());
        
        $this->addStartUnits($ct, $gid, $uid);
	}
	
	function addStartUnits($ct, $gid, $uid)
	{
		$startArr = array();
		$startArr["Austria"] = array("vie" => "a", "bud" => "a", "tri" => "f");
		$startArr["England"] = array("lon" => "f", "edi" => "f", "lvp" => "a");
		$startArr["France"] = array("par" => "a", "mar" => "a", "bre" => "f");
		$startArr["Germany"] = array("ber" => "a", "mun" => "a", "kie" => "f");
		$startArr["Italy"] = array("rom" => "a", "ven" => "a", "nap" => "f");
		$startArr["Russia"] = array("mos" => "a", "sev" => "f", "war" => "a", "stp" => "f");
		$startArr["Turkey"] = array("ank" => "f", "con" => "a", "smy" => "a");		
		
		$citys = $startArr[$ct];
		foreach($citys as $city => $type)
		{
			$query = "INSERT INTO curr_map (gid, aid, owner, type, year, season)
				VALUES($gid, '$city', '$uid', '$type', 1901, 'f');";
			
			$result = mysql_query($query) or die("db access error" . mysql_error());
		}
	}
	
	function getMap()
	{
		$gid = $_GET['gid'];
		
		$query = "SELECT i.uid, c.year, c.season, i.country, c.aid, c.type
					FROM in_game i, games g, curr_map c
					WHERE g.gid=i.gid and g.gid=$gid and c.gid=g.gid and i.uid=c.owner and g.year=c.year and g.season=c.season;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		$map = array();
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($map, mysql_fetch_assoc($result));
		}
		
		return $map;
	}
	
	function getPrevOrders()
	{
		$gid = $_GET['gid'];
		
		$query = "SELECT *
					FROM games
					WHERE gid=$gid;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$year = mysql_result($result, 0, "year");
		$season = mysql_result($result, 0, "season");
		
		if($season == "s")
		{
			$year--;
			$season = "f";
		}
		else
		{
			$season = "s";
		}
		
		$query = "SELECT *
					FROM orders
					WHERE gid=$gid and year=$year and season='$season';";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$orders = array();
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($orders, mysql_fetch_assoc($result));
		}  
		
		return $orders;
	}
	
	function addPlayer()
	{
		$uid = $_SESSION['uid'];
		$gid = $_POST['gid'];
		$ct = $_POST['country'];
		
		$query = "UPDATE games
					SET players=players+1
					WHERE gid=$gid;";
		
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$query = "INSERT INTO in_game(uid, gid, country)
					VALUES('$uid', $gid, '$ct');";
					
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$this->addStartUnits($ct, $gid, $uid);
		
		$query = "SELECT *
					FROM games
					WHERE gid='$gid';";
		
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$players = mysql_result($result, 0, "players");
		
		if($players == 7)
		{
			$date = date("Y-m-d H:i:s", time());
			$date = strtok($date, " ");
			$year = strtok($date, "-");
			$month = strtok("-");
			$day = strtok("-");
			$day = (int)$day + 7;
			$datetime = $year . "-" . $month . "-" . $day . " 00:00:00";
			$query = "UPDATE games
						SET running=true, deadline=$datetime
						WHERE gid=$gid;";
			$result = mysql_query($query) or die("db access error" . mysql_error());
		}
	}
	
	function enterOrders()
	{
		$uid = $_SESSION['uid'];
		$gid = $_POST['gid'];
		
		$query = "SELECT *
					FROM games
					WHERE gid=$gid;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$year = mysql_result($result, 0, "year");
		$season = mysql_result($result, 0, "season");
		
		$orders = $_POST['orders'];
		
		$query = "INSERT INTO orders(gid, uid, orders, year, season)
					VALUES($gid, '$uid', '$orders', $year, '$season');";
					
		$result = mysql_query($query) or die("db access error" . mysql_error());
	}
	
	function getCountries()
	{
		
		$gid = $_GET['gid'];
		
		$query = "SELECT DISTINCT country
					FROM in_game
					WHERE gid=$gid;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$countries = array();
		
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($countries, mysql_result($result, $i, "country"));
		}
		
		return $countries;
	}
}
?>