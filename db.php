<?php

/**
 * This file contains the db class, this class is responsible for
 * conducting all database transactions, return values vary depending
 * on the purpose of the function
 * 
 * author: Jeffrey Minton <jeffrey.minton@gmail.com>
 * version: 1.0
 */

require "dbinfo.php";

/**
 * db class, contains methods to perform al database transactions
 */
class db
{
	/**
	 * Connect()
	 * connect to a databse using the credentials from the dbinfo file
	 */
	function connect()
	{
		global $connection;

		$connection = mysql_connect(HOST, USER, PASS)
			or die("Cannot connect to $host as $user:" . mysql_error());
   
		mysql_select_db(DBNAME)
			or die ("Cannot open $dbName:" . mysql_error());

	}
	
	/**
	 * add a user to the database
	 */
	function addUser()
	{
		$uid = mysql_real_escape_string($_POST["uid"]);
		$pwd = $_POST["pwd1"];
		$email = mysql_real_escape_string($_POST["email"]);
		$fname = mysql_real_escape_string($_POST["fname"]);
		$minit = mysql_real_escape_string($_POST["minit"]);
		$lname = mysql_real_escape_string($_POST["lname"]);

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

	/**
	 * Check to see if a user exists with the requested uid
	 * 
	 * Return: true if user found, false if user not found
	 */
	function userExists()
	{
		$uid = mysql_real_escape_string($_POST["uid"]);

		$query = "
			SELECT *
			FROM user
			WHERE uid='$uid';";

		$result = mysql_query($query) or die("db access error" . mysql_error());

		$exists = (mysql_numrows($result) == 1) ? true : false;

		return $exists;
	}

	/**
	 * Check if uid and pwd provided by user a valid
	 * 
	 * Return: true if valid, false if not
	 */
	function checkUser()
	{
		$uid = mysql_real_escape_string($_POST["uid"]);
		
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
	
	/**
	 * Get list of games a player is in
	 * 
	 * Return: list of games
	 */
	function getPlayersGames($page)
	{
		$uid = $_SESSION["uid"];
		$games = array();
		
		
		$query = ($page == "ord") ? 
			"SELECT DISTINCT g.gid, g.year, g.season, g.players, g.running
			FROM games g, in_game i
			WHERE g.gid = i.gid
			AND i.uid = '$uid'
			AND i.uid <>
			ALL (
				SELECT DISTINCT o.uid
				FROM games g1, orders o
				WHERE g1.gid = g.gid
				AND g.gid = o.gid
				AND g.year = o.year
				AND g.season = o.season
				);" 
			:
			"SELECT DISTINCT g.gid, g.year, g.season, g.players, g.running
			FROM games g, in_game i
			WHERE g.gid = i.gid
			AND i.uid = '$uid';";
			
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		for($i = 0; $i < mysql_num_rows($result); $i++)
		{
			array_push($games, mysql_fetch_assoc($result));
		}
		
		return $games;
	}
	
	
	/**
	 * Get list of games that have not yet started and are still
	 * waiting for more players
	 * 
	 * Return: list of games
	 */
	function getWaitingGames()
	{
		$uid = $_SESSION["uid"];
		
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
	
	
	/**
	 * Add a new game to the database
	 */
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
	
	
	/**
	 * Add a players starting units to the table that represents
	 * the current state of the map
	 */
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
				VALUES($gid, '$city', '$uid', '$type', 1901, 's');";
			
			$result = mysql_query($query) or die("db access error" . mysql_error());
		}
	}
	
	
	/**
	 * Get the current state of the map for a given game
	 * 
	 * Retrun: the resulting table
	 */
	function getMap()
	{
		if(isset($_POST['gid']))
			$gid = mysql_real_escape_string($_POST['gid']);
		else
			$gid = mysql_real_escape_string($_GET['gid']);
		
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
	
	
	/**
	 * Reset a game
	 */
	function resetGame()
	{
		$gid = $_GET['gid'];
		
		$query = "DELETE FROM orders
					WHERE gid=$gid;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$query = "DELETE FROM curr_map
					WHERE gid=$gid and (year<>1901 or season<>'s');";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$query = "UPDATE games
					SET season='s', year=1901
					WHERE gid=$gid;";
		
		$result = mysql_query($query) or die("db access error" . mysql_error());
	}
	
	/**
	 * Get the orders entered that made causeed the map to be in its
	 * current state
	 * 
	 * Return: associative array that maps players to orders
	 */
	function getPrevOrders()
	{
		$gid = mysql_real_escape_string($_GET['gid']);
		
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
	
	
	/**
	 * Add a palyer to a game that is waiting to start and add the 
	 * players starting units
	 */
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
						SET running=true, deadline='$datetime'
						WHERE gid=$gid;";
			$result = mysql_query($query) or die("db access error" . mysql_error());
		}
	}
	
	
	/**
	 * enter players orders into order table
	 */
	function enterOrders($orders)
	{
		$uid = $_SESSION['uid'];
		$gid = $_POST['gid'];
		
		$query = "SELECT *
					FROM games
					WHERE gid=$gid;";
		$result = mysql_query($query) or die("db access error" . mysql_error());
		
		$year = mysql_result($result, 0, "year");
		$season = mysql_result($result, 0, "season");
		
		//$orders = mysql_real_escape_string($_POST['orders']);
		
		$query = "INSERT INTO orders(gid, uid, orders, year, season)
					VALUES($gid, '$uid', '$orders', $year, '$season');";
					
		$result = mysql_query($query) or die("db access error" . mysql_error());
	}
	
	
	/**
	 * get a list of countries currently taken by players in a game
	 * 
	 * Return: associative array that maps player id's to countries
	 */
	function getCountries()
	{
		
		$gid = mysql_real_escape_string($_GET['gid']);
		
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
