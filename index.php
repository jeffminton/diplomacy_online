<?php
/**
 *index.php
 *
 *This file determimnes all starting information for the page
 *it sets up session variagbles that tell other modules how to
 *process data  and what information to display
 *@author Jeffrey Minton <jeffrey.minton@gmail.com>
 *@version 0.1
 *@package diplomacyonline        
 */
session_start();
require_once "htmlManager.php";
require_once "db.php";

/**
 *Main class always called when accessing website.
 *
 *self instantiates and runs at end of file  
 *
 *@package diplomacyonline
 *@subpackage classes   
 */
class main
{
	/**
	 *Run code for main class
	 *@param none
	 *@return none	 	
	 */	
	function run()
	{
		/**
		 *object of type htmlManager. used to set display params
		 *and display templates
		 *@var 	htmlManager	 		 		
		 */		
		$html = new htmlManager();
		/**
		 *object of type db. used to carry out all db transactions
		 *
		 *@var db		 		 		
		 */		
		$db = new db();
		$db->connect();
		
		/**
		 *Array to hold names of great powers
		 *
		 *@var gp		 		 		
		 */		
		$gp = array("Austria", "England", "France", "Germany", "Italy", "Russia", "Turkey");
				
		
		////////////////////////////////////////////////////////////////////////
		//  not logged in
		////////////////////////////////////////////////////////////////////////
		if(!isset($_SESSION['uid']))
		{
			////////////////////////////////////////////////////////////////////////
			//  Login atempt
			////////////////////////////////////////////////////////////////////////
			if(isset($_GET["log"]))
			{
				if($db->checkUser() == true)
				{
					$html->setVal("title", "Diplomacy: Menu");
					$_SESSION["uid"] = $_POST["uid"];
					$html->setVal("page", "menu");
				}
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
		////////////////////////////////////////////////////////////////////////
		//    Add user to system
		////////////////////////////////////////////////////////////////////////		
		elseif(isset($_GET["add"]))
		{
			//if the user already exists
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
		////////////////////////////////////////////////////////////////////////
		//  Submit Orders
		////////////////////////////////////////////////////////////////////////
		elseif(isset($_GET["ord"]))
		{
			//  submit orders for selected game
			if(isset($_GET['gid']))
			{
				$maplist = array();
				$countries = array();
				$map = $db->getMap();
				//print_r($map);
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
				
				//print_r($maplist);
				//print_r($countries);
				$html->setVal("title", "Diplomacy: Submit Orders");
				$html->setVal("page", "orderEntry");
				$html->setVal("gid", $_GET['gid']);
				$html->setVal("maplist", $maplist);
				$html->setVal("countries", $countries);
			}
			//  orders have been entered
			elseif(isset($_GET['ent']))
			{
				$db->enterOrders();
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
				system("python processOrders.py");
			}
			//  list games player is in
			else
			{
				$html->setVal("title", "Diplomacy: Select Game");
				$gameArr = $db->getPlayersGames();
				$html->setVal("games", $gameArr);
				$html->setVal("page", "games");
				$html->setVal("link", "ord");
			}
		}
		////////////////////////////////////////////////////////////////
		//     Game Status selected
		////////////////////////////////////////////////////////////////
		elseif(isset($_GET['st']))
		{
			//game selected
			if(isset($_GET['gid']))
			{
				$maplist = array();
				$countries = array();
				$map = $db->getMap();
				$orders = $db->getPrevOrders();
				//print_r($map);

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
				
				//print_r($maplist);
				//print_r($countries);
				$html->setVal("title", "Diplomacy: Submit Orders");
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
		////////////////////////////////////////////////////////////////////////
		//  Create a game
		////////////////////////////////////////////////////////////////////////
		elseif(isset($_GET['create']))
		{
			if(isset($_GET['co']))
			{
				$db->addGame();
				//set the page title
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
			}
			else
			{
				$html->setVal("title", "Diplomacy: Choose Country");
				$html->setVal("page", "create");
				$html->setVal("gp", $gp);
			}
		}
		////////////////////////////////////////////////////////////////////////
		//  Register new user
		////////////////////////////////////////////////////////////////////////
		elseif(isset($_GET["reg"]))
		{
			//set the page title
			$html->setVal("title", "Diplomacy: Register");
			$html->setVal("class", "error.hidden");
			$html->setVal("error", "");
			$html->setVal("page", "register");
		}
		////////////////////////////////////////////////////////////////////////
		//  join game selected
		////////////////////////////////////////////////////////////////////////
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
        ////////////////////////////////////////////////////////////////////////
		//  logout selected
		////////////////////////////////////////////////////////////////////////
		elseif(isset($_GET['unlog']))
		{
			session_destroy();
			$html->setVal("title", "Doplomacy: Login");
			$html->setVal("page", "login");
		}
		////////////////////////////////////////////////////////////////////////
		//  default
		////////////////////////////////////////////////////////////////////////
		else
		{
			//set the page title
			$html->setVal("title", "Diplomacy: Menu");
			$html->setVal("page", "menu");
		}
		
		//show the page
		//print_r ($_SESSION);
		//$html->showVals();
		if(isset($_SESSION['uid']))
		{
			$html->setVal("uid", $_SESSION['uid']);
		}
		else
		{
			$html->setVal("uid", "");
		}
		$html->showPage();
	}
}

//
$main = new main();

$main->run();
?>
