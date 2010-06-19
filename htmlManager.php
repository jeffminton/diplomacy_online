<?php

require_once 'Savant3.php';

class htmlManager
{
	private $tpl = null;
	function __construct()
	{
		$this->tpl = new Savant3();
		$this->tpl->vals = array();
		$this->tpl->addPath('template', '/var/www/diplomacy_online/html/');
	}

	function showPage()//read and display the header.html file
	{
		$this->tpl->display("html/main.tpl.php");
	}

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
	
	function showVals()
	{
		print_r ($this->tpl->vals);
	}
}
?>
