var units = Array();
var map;

function drawCannon(coord, country)
{
	var cannon = map.path(cannonPath);
	cannon.attr({'fill': colors[country.toLowerCase()]});
	cannon.moveTo(coord[0] - cannonOff[0], coord[1] - cannonOff[1]);
	cannon.scale(.08, .08)
}

function drawShip(coord, country)
{
	var ship = map.path(shipPath);
	ship.attr({'fill': colors[country.toLowerCase()]});
	ship.moveTo(coord[0] - shipOff[0], coord[1] - shipOff[1]);
	ship.scale(.08, .08)
}

function drawOwned(coord, country)
{
	var owned = map.rect(coord[0], coord[1], 15, 15);
	owned.attr({'fill': colors[country.toLowerCase()]});
}

window.onload = function ()
{ 
	map = Raphael(document.getElementById("map"), 1142, 965);
	var mapImg = map.image("map/std_bit.png", 0, 0, 1142, 965);
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
}
	
