<?php

/**
 * This file contains the html manager class, this is the class
 * responsible for setting up all the values for the page templates
 * 
 * author: Jeffrey Minton <jeffrey.minton@gmail.com>
 * version: 1.0
 */

require_once 'Savant3.php';

/**
 * htmlManager class, responsible for setting up values for and 
 * displaying templates
 */
class htmlManager
{
	/**
	 * class member 
	 * the savant3 template object
	 */
	private $tpl = null;
	
	/**
	 * Constructor
	 * sets up the savant object and the path to the templates
	 */
	function __construct()
	{
		$this->tpl = new Savant3();
		$this->tpl->vals = array();
		$this->tpl->addPath('template', '/var/www/diplomacy_online/html/');
	}

	/**
	 * show the main template page which contains calls to all
	 * other templates needed to be displayed
	 */
	function showPage()//read and display the header.html file
	{
		$this->tpl->display("html/main.tpl.php");
	}

	
	/**
	 * Put values into the vals dictionary, if maplist or country
	 * is sent, generate javascript for it
	 */
	function setVal($key, $value)
	{
		//array_push($this->tpl->vals, $key=>$value);
		if($key == "maplist")
		{
			$value = $this->genMapJS($value);
		}
		if($key == "countries")
		{
			$value = $this->genCountJS($value);
		}
		if($key == "page")
		{
			$value = $value . ".tpl.php";
		}
		$this->tpl->vals[$key] = $value;
		
	}
	
	
	/**
	 * Generate the javascript code for the array mapping
	 * player name to country
	 */
	function genCountJS($countries)
	{
		$array = "var countries = {";
		$first = true;
		foreach($countries as $cid => $country)
		{
			if($first == true)
			{
				$first = false;
			}
			else
			{
				$array = $array . ", ";
			}
			//echo $cid . ": " . $country . "<br/>";
			
			$array = $array . "'$cid':'$country'";
		}
		$array = $array . "};";
		
		//echo $array;
		
		return $array;
	}
	
	
	/**
	 * generate the js code for the array that represents the
	 * current map
	 */
	function genMapJS($map)
	{	
		$array = "var currMap = {";
		$first = true;
		foreach($map as $cid => $ownedArr)
		{
			if($first == true)
			{
				$first = false;
			}
			else
			{
				$array = $array . ", ";
			}
			
			$array = $array . "'$cid':[";
			
			for($i = 0; $i < count($ownedArr); $i++)
			{
				$type = $ownedArr[$i]['type'];
				$aid = $ownedArr[$i]['aid'];
				$array = $array . "{'type':'$type', 'aid':'$aid'}";
				if($i < count($ownedArr) - 1)
				{
					$array = $array . ", ";
				}
			}
			$array = $array . "]";
		}
		$array = $array . "};";
		
		return $array;
	}
}
?>
