var countryObj = Object();
var map;

/**
 * Set a cookie with a name, value and expire tim
 */
function setCookie(c_name,value,expiredays)
{
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    
    if(typeof(value) == "number")
    {
        value = String(value);
    }
    
    document.cookie=c_name + "=" + escape(value) + ((expiredays==null) ? "" : ";expires=" + exdate.toGMTString());
}


/**
 * get data from a cookie with a given name
 */
function getCookie(c_name)//get a cookie with a name c_name
{
    if (document.cookie.length>0)
    {
        c_start=document.cookie.indexOf(c_name + "=");
        if (c_start!=-1)
        {
            c_start=c_start + c_name.length+1;
            c_end=document.cookie.indexOf(";",c_start);
            if (c_end==-1) c_end=document.cookie.length;
            return unescape(document.cookie.substring(c_start,c_end));
        }
    }
    return "";
}


/**
 * add an areas path to the map, so that it can be interected with
 */
function addCountryPath(country)
{
	countryObj[country] = map.path(countryPath[country]);
	countryObj[country].attr({'fill': "#000", 'opacity': 0.5});
}


/**
 * Draw a cannon at the given coordinates
 * 
 * Draws the cannon and fills it in with the color of the owner
 * scales the cannon down to .1 of original size
 */
function drawCannon(coord, country)
{
	var cannon = map.path(cannonPath);
	cannon.attr({'fill': colors[country.toLowerCase()]});
	cannon.moveTo(coord[0] - cannonOff[0], coord[1] - cannonOff[1]);
	cannon.scale(.08, .08)
}

/**
 * draw a ship at the given coordinates
 * 
 * Draws the ship and fills if in with the color of the owner
 * scales the ship down to .1 of original size
 */
function drawShip(coord, country)
{
	var ship = map.path(shipPath);
	ship.attr({'fill': colors[country.toLowerCase()]});
	ship.moveTo(coord[0] - shipOff[0], coord[1] - shipOff[1]);
	ship.scale(.08, .08)
}


/**
 * Draw a square to represent an owned but unoccupied
 * terrirtory
 * 
 * Fills the square with the color of the owner
 */
function drawOwned(coord, country)
{
	var owned = map.rect(coord[0], coord[1], 15, 15);
	owned.attr({'fill': colors[country.toLowerCase()]});
}


/**
 * run this function on the page load
 * 
 * displays the map and fills in units
 */
window.onload = function ()
{ 
	var uid = getCookie('uid');
	
	var width, height;
	width = window.innerWidth;
	height = window.innerHeight;
	
	map = Raphael(document.getElementById("map"), 1142, 964);
	var mapImg = map.image("map/std_bit.png", 0, 0, 1142, 964);
	mapImg.show();
	
	if(currMap != null)
	{
		//alert(currMap.length);
		for(var cid in currMap)
		{
			//alert("cid: " + cid);
			for(var i = 0; i < currMap[cid].length; i++)
			{
				//alert(currMap[cid][i]['aid']);
				var coord = unitCoords[currMap[cid][i]['aid']];
				switch(currMap[cid][i]['type'])
				{
					case 'f':
						drawShip(coord, countries[cid]);
						break;
					case 'a':
						drawCannon(coord, countries[cid]);
						break;
					default:
						drawOwned(coord, countries[cid]);
						break;
				}
			}
		}
	}
	
	//for(var i = 0; i < countrySym.length; i++)
	//{
		//addCountryPath(countrySym[i])
	//}
	
	//map.setSize(1142 * .9, 964 * .9);
}
	
