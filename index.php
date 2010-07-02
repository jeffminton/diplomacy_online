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
		//$this->randomOrders();
		
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
			else
			{
				//set the page title
				$html->setVal("title", "Diplomacy: Login");
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
				setcookie("page", "orders", time()+(60 * 60));
			}
			//orders have been entered
			elseif(isset($_GET['ent']))
			{
				//enter orders into db
				//$this->randomOrders();
				$orders = $_POST['orders'];
				$db->enterOrders($orders);
				$html->setVal("title", "Diplomacy: Menu");
				$html->setVal("page", "menu");
				system("/home/web/www/diplomacy_online/processOrders.py", $out);
			}
			//list games player is in
			else
			{
				$html->setVal("title", "Diplomacy: Select Game");
				$gameArr = $db->getPlayersGames("ord");
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
				setcookie("page", "status", time()+(60 * 60));
			}
			//show game list
			else
			{
				$html->setVal("title", "Diplomacy: Select Game");
				$gameArr = $db->getPlayersGames("st");
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
	
	function randomOrders()
	{
		$db = new db();
		$db->connect();
		
		//private $border = array();
		$border['tun'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('ion', 'tyn', 'wes', 'naf'));
		$border['sev'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('arm', 'bla', 'rum', 'ukr', 'mos'));
		$border['ser'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('bud', 'tri', 'bul', 'rum', 'gre', 'alb'));
		$border['vie'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('boh', 'tri', 'bud', 'gal', 'tyr'));
		$border['lon'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('yor', 'eng', 'nth', 'wal'));
		$border['edi'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('cly', 'lvp', 'yor', 'nrg', 'nth'));
		$border['alb'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('adr', 'ion', 'ser', 'tri', 'gre'));
		$border['nwy'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('nth', 'stp', 'ska', 'swe', 'fin'));
		$border['ank'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bla', 'con', 'arm', 'smy'));
		$border['pru'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('sil', 'war', 'lvn', 'bal', 'ber'));
		$border['mar'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('gol', 'pie', 'bur', 'gas', 'spa'));
		$border['spa'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('gol', 'mar', 'por', 'wes', 'mid', 'gas'));
		$border['bre'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('gas', 'par', 'mid', 'eng', 'pic'));
		$border['arm'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('ank', 'bla', 'smy', 'syr', 'sev'));
		$border['rom'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('nap', 'tyn', 'tus', 'apu'));
		$border['gol'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('pie', 'tyn', 'wes', 'spa', 'mar', 'tus'));
		$border['wal'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('lon', 'yor', 'iri', 'eng', 'lvp'));
		$border['naf'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('tun', 'mid', 'wes'));
		$border['smy'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('aeg', 'ank', 'con', 'eas', 'arm', 'syr'));
		$border['eng'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('bel', 'bre', 'lon', 'nth', 'pic', 'wal', 'mid', 'iri'));
		$border['tyr'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('boh', 'ven', 'vie', 'tri', 'mun', 'pie'));
		$border['mid'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('bre', 'eng', 'gas', 'iri', 'naf', 'por', 'spa', 'wes', 'nat'));
		$border['hol'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('kie', 'ruh', 'bel', 'nth', 'hel'));
		$border['swe'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bal', 'bar', 'fin', 'nrg', 'nwy', 'bot', 'ska', 'den'));
		$border['ukr'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('war', 'mos', 'sev', 'gal'));
		$border['wes'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('naf', 'tun', 'tyn', 'mid', 'spa', 'gol'));
		$border['iri'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('eng', 'lvp', 'wal', 'nat', 'mid'));
		$border['gre'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('aeg', 'alb', 'ion', 'ser', 'bul'));
		$border['ska'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('den', 'nth', 'nwy', 'swe'));
		$border['kie'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('ber', 'mun', 'ruh', 'bal', 'hel', 'hol'));
		$border['nat'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('cly', 'iri', 'lvp', 'mid', 'nrg'));
		$border['hel'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('hol', 'kie', 'bal', 'den', 'nth'));
		$border['mun'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('boh', 'bur', 'ruh', 'tyr', 'kie', 'ber', 'sil'));
		$border['fin'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('nwy', 'stp', 'bot', 'swe'));
		$border['war'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('lvn', 'pru', 'sil', 'mos', 'gal', 'ukr'));
		$border['sil'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('boh', 'mun', 'gal', 'war', 'ber', 'pru'));
		$border['ruh'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('bur', 'bel', 'hol', 'kie', 'mun'));
		$border['pic'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bre', 'par', 'eng', 'bel', 'bur'));
		$border['den'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('hel', 'kei', 'nth', 'swe', 'bal', 'ska'));
		$border['rum'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bla', 'bud', 'gal', 'ser', 'sev', 'bul', 'ukr'));
		$border['mos'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('lvn', 'sev', 'ukr', 'war', 'stp'));
		$border['gas'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('mar', 'par', 'spa', 'mid', 'bur', 'bre'));
		$border['tus'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('gol', 'rom', 'tyn', 'ven', 'pie'));
		$border['nrg'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('cly', 'edi', 'nat', 'nth', 'swe', 'bar'));
		$border['pie'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('tus', 'tyr', 'ven', 'mar', 'gol'));
		$border['syr'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('eas', 'smy', 'arm'));
		$border['gal'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('boh', 'sil', 'ukr', 'vie', 'war', 'rum', 'bud'));
		$border['bul'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('aeg', 'bla', 'gre', 'rum', 'ser', 'con'));
		$border['ven'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('apu', 'tri', 'pie', 'tus', 'adr', 'tyr'));
		$border['adr'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('apu', 'ven', 'alb', 'tri', 'ion'));
		$border['eas'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('ion', 'syr', 'smy', 'aeg'));
		$border['apu'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('nap', 'rom', 'ion', 'adr', 'ven'));
		$border['bud'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('gal', 'vie', 'rum', 'ser', 'tri'));
		$border['tri'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('adr', 'bud', 'tyr', 'ven', 'vie', 'alb', 'ser'));
		$border['bar'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('nrg', 'stp', 'swe'));
		$border['lvp'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('cly', 'wal', 'yor', 'nat', 'iri', 'edi'));
		$border['bel'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('hol', 'nth', 'pic', 'ruh', 'eng', 'bur'));
		$border['nth'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('edi', 'hel', 'hol', 'lon', 'yor', 'ska', 'den', 'nwy', 'nrg', 'eng', 'bel'));
		$border['tyn'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('nap', 'tus', 'gol', 'wes', 'tun', 'ion', 'rom'));
		$border['bot'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('bal', 'fin', 'swe', 'stp', 'lvn'));
		$border['bur'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('bel', 'gas', 'mar', 'par', 'pic', 'mun', 'ruh'));
		$border['ion'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('adr', 'apu', 'nap', 'tyn', 'gre', 'alb', 'aeg', 'tun', 'eas'));
		$border['stp'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bar', 'bot', 'fin', 'lvn', 'mos', 'nwy'));
		$border['aeg'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('bla', 'eas', 'ion', 'smy', 'con', 'bul', 'gre'));
		$border['ber'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('mun', 'pru', 'sil', 'bal', 'kie'));
		$border['bal'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('ber', 'den', 'hel', 'kie', 'pru', 'lvn', 'bot', 'swe'));
		$border['lvn'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('bal', 'bot', 'pru', 'stp', 'mos', 'war'));
		$border['con'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('aeg', 'bla', 'bul', 'smy', 'ank'));
		$border['boh'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('vie', 'tyr', 'mun', 'sil', 'gal'));
		$border['cly'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('lvp', 'nat', 'edi', 'nrg'));
		$border['yor'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('nth', 'lon', 'wal', 'lvp', 'edi'));
		$border['par'] = array(
			'fleet' => false,
			'army' => true,
			'borders' => array('bur', 'gas', 'bre', 'pic'));
		$border['nap'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('apu', 'ion', 'tyn', 'rom'));
		$border['por'] = array(
			'fleet' => true,
			'army' => true,
			'borders' => array('spa', 'mid'));
		$border['bla'] = array(
			'fleet' => true,
			'army' => false,
			'borders' => array('sev', 'arm', 'ank', 'con', 'aeg', 'bul', 'rum'));
		
		if(isset($_SESSION['uid']))
		{
			$realUid = $_SESSION['uid'];
			
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
			
			print_r($maplist);
			
			foreach($maplist as $otherUid => $area)
			{
				if($otherUid != $realUid)
				{
					$_SESSION['uid'] = $otherUid;
					$order = "";
					
					echo "UID: " . $otherUid . "<br />";
					echo "area: ";
					print_r($area);
					echo "<br />";
					for($i = 0; $i < count($area); $i++)
					{
						echo "check1<br />";
						$choice = rand(1, 2);
						
						if($area[$i]['type'] == 'f' || $area[$i]['type'] == 'a')
						{
							echo "check2<br />";
							if($choice == 1)
							{
								$order = $order . $area[$i]['type'] . " " . $area[$i]['aid'] . "-holds\r\n";
							}
							else
							{
								echo "check3<br />";
								$canGoTo = array();
								if($area[$i]['type'] == 'f')
								{
									echo "checkf<br />";
									$currAID = $border[$area[$i]['aid']];
									foreach($currAID['borders'] as $aidBorder)
									{
										if($border[$aidBorder]['fleet'] == true)
										{
											array_push($canGoTo, $aidBorder);
										}
									}
								}
								if($area[$i]['type'] == 'a')
								{
									echo "checka<br />";
									$currAID = $border[$area[$i]['aid']];
									foreach($currAID['borders'] as $aidBorder)
									{
										if($border[$aidBorder]['army'] == true)
										{
											array_push($canGoTo, $aidBorder);
										}
									}
								}
								
								$choice = rand(0, count($canGoTo) - 1);
									
								$order = $order . $area[$i]['type'] . " " . $area[$i]['aid'] . "-" . $canGoTo[$choice] . "\r\n";
							}
						}
					}
					$db->enterOrders($order);
				}
			}
			$_SESSION['uid'] = $realUid;
		}
	}
	
}

//Object if type dip, used to run main code
$dip = new dip();

//run main code
$dip->run();
?>
