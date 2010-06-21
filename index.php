<?php
/**
 * This file determimnes all starting information for the page
 * it sets up session variagbles that tell other modules how to
 * process data  and what information to display
 * 
 * author: Jeffrey Minton <jeffrey.minton@gmail.com>
 * version: 1.0        
 */
session_start();
require_once "htmlManager.php";
require_once "db.php";

/**Main class always contains function run()
 * which is executed every time site is accessed
 * self instantiates and runs at end of file  
 */
class dip
{
	public function run()
	{
		/**
		 * object of type htmlManager. used to set display params
		 * and display templates		 		
		 */		
		$html = new htmlManager();
		/**
		 * object of type db. used to carry out all db transactions
		 */		
		$db = new db();
		$db->connect();
		
		/**
		 * Array to hold names of great powers
		 */		
		$gp = array("Austria", "England", "France", "Germany", "Italy", "Russia", "Turkey");
				
		
		
		/**********************************************************************/
		/**
		 * What Follows here are the if statements that determine what
		 * should be output by the website based on the current state or
		 * what the user has selected
		 */
		/**********************************************************************/
		
		/**************************************************************/
		// user not logged in
		// $_SESSION['uid'] always holds the user id of the user
		// that is currently logged in
		/**************************************************************/
		if(!isset($_SESSION['uid']))
		{
			/**********************************************************/
			// Login atempt
			/**********************************************************/
			if(isset($_GET["log"]))
			{
				//call checkUser method of db class, returns true if
				//users credentials are correct
				
				//if true, set session vars and show main menu
				if($db->checkUser() == true)
				{
					$html->setVal("title", "Diplomacy: Menu");
					$_SESSION["uid"] = mysql_real_escape_string($_POST["uid"]);
					$html->setVal("page", "menu");
				}
				//if false show login page
				else
				{
					$html->setVal("title", "Diplomacy: Login Failed");
					$html->setVal("class", "error.show");
					$html->setVal("page", "login");
				}
			}
			else
			{
				//set the page title
				$html->setVal("title", "Diplomacy: Login");
				$html->setVal("page", "login");
			}
		}
		/**************************************************************/
		// Add user to system
		// user has selected to register a new account
		/**************************************************************/
		elseif(isset($_GET["add"]))
		{
			//call userExists method of db class
			// returns true if a user with requested uid already exists
			
			//if true show registration page and display error
			if($db->userExists() == true)
			{
				//set the page title and other information
				$html->setVal("title", "Diplomacy: User already exists");
				$html->setVal("class", "error.show");
				$html->setVal("error", "That user already exists, please try again");
				$html->setVal("page", "register");
			}
			//user doesn't exist
			else
			{
				//set the page title
				$html->setVal("title", "Diplomacy: Add User");
				//add user to the db
				$db->addUser();
				//display the login page
				$html->setVal("page", "login");
			}
		}
		/**************************************************************/
		// user requested to submit orders for a game
		/**************************************************************/
		elseif(isset($_GET["ord"]))
		{
			//game id selected, show order entry page for that game
			if(isset($_GET['gid']))
			{
				$maplist = array();
				$countries = array();
				
				//get current state of the map from the db
				$map = $db->getMap();
				
				//a little conversion work
				//using table retrieved from db, create associative arrays
				//that store player names mapped to countries
				//and player names mapped to arrays of area info
				//that contain aid map to 3 char area code and type mapped to unit type
				for($i = 0; $i < count($map); $i++)
				{
					$row = $map[$i];
					if(!array_key_exists($row['uid'], $maplist))
					{
						$maplist[$row['uid']] = array();
						$countries[$row['uid']] = $row['country'];
					}
					
					array_push($maplist[$row['uid']], array("type" => $row['type'], "aid" => $row['aid']));
				}
				
				//set al template values
				$html->setVal("title", "Diplomacy: Submit Orders");
				$html->setVal("page", "orderEntry");
				$html->setVal("gid", $_GET['gid']);
				$html->setVal("maplist", $maplist);
				$html->setVal("countries", $countries);
				$html->setVal("loadJava", true);
			}
			//orders have been entered
			elseif(isset($_GET['ent']))
			{
				//enter orders into db
				$db->enterOrders();
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
				system("python processOrders.py");
			}
			//list games player is in
			else
			{
				$html->setVal("title", "Diplomacy: Select Game");
				$gameArr = $db->getPlayersGames();
				$html->setVal("games", $gameArr);
				$html->setVal("page", "games");
				$html->setVal("link", "ord");
			}
		}
		/**************************************************************/
		// Game Status selected
		/**************************************************************/
		elseif(isset($_GET['st']))
		{
			//game selected show map status
			if(isset($_GET['gid']))
			{
				$maplist = array();
				$countries = array();
				
				//get current state of map from db
				$map = $db->getMap();
				//get previous set of orders
				$orders = $db->getPrevOrders();

				//a little conversion work
				//using table retrieved from db, create associative arrays
				//that store player names mapped to countries
				//and player names mapped to arrays of area info
				//that contain aid map to 3 char area code and type mapped to unit type
				for($i = 0; $i < count($map); $i++)
				{
					$row = $map[$i];
					if(!array_key_exists($row['uid'], $maplist))
					{
						$maplist[$row['uid']] = array();
						$countries[$row['uid']] = $row['country'];
					}
					
					array_push($maplist[$row['uid']], array("type" => $row['type'], "aid" => $row['aid']));
				}
				
				
				//set template values
				$html->setVal("title", "Diplomacy: Game Status");
				$html->setVal("page", "status");
				$html->setVal("gid", $_GET['gid']);
				$html->setVal("maplist", $maplist);
				$html->setVal("countries", $countries);
				$html->setVal("orders", $orders);
				$html->setVal("loadJava", true);
			}
			//show game list
			else
			{
				$html->setVal("title", "Diplomacy: Select Game");
				$gameArr = $db->getPlayersGames();
				$html->setVal("games", $gameArr);
				$html->setVal("page", "games");
				$html->setVal("link", "st");
			}
		}
		/**************************************************************/
		// User has selected to create a game
		/**************************************************************/
		elseif(isset($_GET['create']))
		{
			//user has selected a country, add the game to the db
			//and add user to the in_game table
			if(isset($_GET['co']))
			{
				$db->addGame();
				//set the page title
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
			}
			//ask user to choose a country
			else
			{
				$html->setVal("title", "Diplomacy: Choose Country");
				$html->setVal("page", "create");
				$html->setVal("gp", $gp);
			}
		}
		/**************************************************************/
		// User has requested to register with the site
		/**************************************************************/
		elseif(isset($_GET["reg"]))
		{
			//set the template data
			$html->setVal("title", "Diplomacy: Register");
			$html->setVal("class", "error.hidden");
			$html->setVal("error", "");
			$html->setVal("page", "register");
		}
		/**************************************************************/
		// User has selected to join a game
		/**************************************************************/
		elseif(isset($_GET['join']))
		{
			//country has been selected
			if(isset($_GET['co']))
			{
				$db->addPlayer();
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
			}
			//game has been selected, show country selection page
			elseif(isset($_GET['gid']))
			{
				$remainGP = array();
				$usedGP = $db->getCountries();
				for($i = 0; $i < count($gp); $i++)
				{
					$taken = false;
					for($j = 0; $j < count($usedGP); $j++)
					{
						if($gp[$i] == $usedGP[$j])
						{
							$taken = true; 
						}
					}
					if($taken == false)
					{
						array_push($remainGP, $gp[$i]);
					}
				}
				$html->setVal("title", "Diplomacy: Select Country");
				$html->setVal("gid", $_GET['gid']);
				$html->setVal("page", "country");
				$html->setVal("gp", $remainGP);
			}
			//show games to select
			else
			{
				$html->setVal("title", "Diplomacy: Join Game");
				$games = $db->getWaitingGames();
	            $html->setVal("games", $games);
				$html->setVal("page", "join");
			}	      
        }
        /**************************************************************/
		// logout selected
		/**************************************************************/
		elseif(isset($_GET['unlog']))
		{
			session_destroy();
			$html->setVal("title", "Doplomacy: Login");
			$html->setVal("page", "login");
		}
		/**************************************************************/
		// default
		/**************************************************************/
		else
		{
			//set the page title
			$html->setVal("title", "Diplomacy: Menu");
			$html->setVal("page", "menu");
		}
		
		//if uid is set, display it
		if(isset($_SESSION['uid']))
		{
			$html->setVal("uid", $_SESSION['uid']);
			setcookie("uid", $_SESSION['uid'], time()+(60 * 60));
		}
		
		//show the page
		$html->showPage();
	}
}

//Object if type dip, used to run main code
$dip = new dip();

//run main code
$dip->run();
?>
