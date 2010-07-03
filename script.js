var map;
var context;


/**
 * runs when the windows loads
 * 
 * instantiates the map object
 */
window.onload = function()
{
	map = new mapObj();
	map.show();
	blah();
}


/**
 * the main map object
 * 
 * contains the code to draw and change the map
 */
function mapObj()
{

	this.countryObj = new Object();
	this.map = null;
	this.active = new Array();
	this.orders = "";
	//this.context = null;
	this.ordered = new Array();
	this.uid = null;
	this.page = null;
	
	
	/**
	 * add an areas path to the map, so that it can be interected with
	 */
	this.addCountryPath = function(country)
	{
		this.countryObj[country] = this.map.path(countryPath[country]);
		this.countryObj[country].attr({'fill': "#000", 'opacity': 0.5});
	}
	
	
	/**
	 * Draw a cannon at the given coordinates
	 * 
	 * Draws the cannon and fills it in with the color of the owner
	 * scales the cannon down to .1 of original size
	 */
	this.drawCannon = function(coord, country)
	{
		var cannon = this.map.path(cannonPath);
		cannon.attr({'fill': colors[country.toLowerCase()]});
		//cannon.moveTo(coord[0] - cannonOff[0], coord[1] - cannonOff[1]);
		cannon.translate(coord[0] - cannonOff[0], coord[1] - cannonOff[1]);
		cannon.scale(.08, .08)
	}
	
	/**
	 * draw a ship at the given coordinates
	 * 
	 * Draws the ship and fills if in with the color of the owner
	 * scales the ship down to .1 of original size
	 */
	this.drawShip = function(coord, country)
	{
		var ship = this.map.path(shipPath);
		ship.attr({'fill': colors[country.toLowerCase()]});
		//ship.moveTo(coord[0] - shipOff[0], coord[1] - shipOff[1]);
		ship.translate(coord[0] - shipOff[0], coord[1] - shipOff[1]);
		ship.scale(.08, .08)
	}
	
	
	/**
	 * Draw a square to represent an owned but unoccupied
	 * terrirtory
	 * 
	 * Fills the square with the color of the owner
	 */
	this.drawOwned = function(coord, country)
	{
		var owned = this.map.rect(coord[0], coord[1], 15, 15);
		owned.attr({'fill': colors[country.toLowerCase()]});
	}
	
	
	/**
	 * Add an onclick property to an object and change its
	 * opacity
	 */
	this.addClick = function(data)
	{
		//alert(data.toString());
		this.countryObj[data[1]].attr({'opacity': 0.0});
		this.countryObj[data[1]].node.onclick = function(){
			map.action(data);};
	}
	
	
	/**
	 * remove an onclick property from an object
	 */
	this.rmClick = function(country)
	{
		this.countryObj[country].attr({'opacity': 0.5});
		this.countryObj[country].node.onclick = null;
	}
	
	
	/**
	 * determine the action that should be carried out when 
	 * something is clicked
	 */
	this.action = function(data)
	{
		//alert(data.toString());
		switch(data[0])
		{
			case "context":
				context.show(data);
				break;
			case "dest":
				this.addOrder(data);
		}
	}
	
	
	/**
	 * add a completed order to the order string and set the
	 * valuse of the hidden order field to the value
	 * of the oreder string
	 */
	this.addOrder = function(data)
	{
		var orderType = data.pop();
		this.ordered.push(data[2]);
		
		switch(orderType)
		{
			case "hold":
				this.orders = this.orders + data.pop() + " " + data.pop() + "-" + data.pop() + "\r\n";
				break;
			case "move":
				this.orders = this.orders + data.pop() + " " + data.pop() + "-" + data.pop() + "\r\n";
				break;
		}
		//alert(this.orders);
		document.getElementById("orders").value = this.orders;
		this.showClickable();
	}
	
	
	/**
	 * if the user selected to hold their army
	 */
	this.holdClicked = function(data)
	{
		context.hide();
		var country, from;
		var countrySym = data[1];
		var type = data[2];
		
		this.addOrder(["hold", "holds", countrySym, type, "hold"]);
	}
	
	
	/**
	 * if the user clicked move
	 * 
	 * shadow all countrieas and then make all possible
	 * movement locations brighter and clicakble
	 */
	this.moveClicked = function(data)
	{
		context.hide();
		var country, from, times = this.active.length;
		var countrySym = data[1];
		var type = data[2];
		
		//alert(countrySym);
		for(var i = 0; i < times; i++)
		{
			country = this.active.pop();
			this.rmClick(country);
		}
		
		from = border[countrySym];
		borders = from.borders;
		
		//alert(borders.toString());
		for(var i = 0; i < borders.length; i++)
		{
			//alert(borders[i]);
			if(type == 'f')
			{
				if(border[borders[i]].fleet == true)
				{
					/*[what this array contains, the country you are moving to
						, the country you are moving from, the unit type, type of order]*/
					this.addClick(["dest", borders[i], countrySym, type, "move"]);
					this.active.push(borders[i]);
				}
			}
			else
			{
				if(border[borders[i]].army == true)
				{
					/*[what this array contains, the country you are moving to
						, the country you are moving from, the unit type, type of order]*/
					this.addClick(["dest", borders[i], countrySym, type, "move"]);
					this.active.push(borders[i]);
				}
			}
		}
	}
	
	
	/**
	 * will be used for support moves
	 */
	this.supportClicked = function(data)
	{
	}
	
	
	/**
	 * will be used for convoy moves
	 */
	this.convoyClicked = function(data)
	{
	}
	
	/**
	 * run this function on the page load
	 * 
	 * displays the map and fills in units
	 */
	this.show = function ()
	{ 
		this.uid = getCookie('uid');
		this.page = getCookie('page');
		
		var width, height;
		width = window.innerWidth;
		height = window.innerHeight;
		
		this.map = Raphael(document.getElementById("map"), 1142, 964);
		var mapImg = this.map.image("map/std_bit.png", 0, 0, 1142, 964);
		mapImg.show();
		//alert("test 1");
		
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
							this.drawShip(coord, countries[cid]);
							break;
						case 'a':
							this.drawCannon(coord, countries[cid]);
							break;
						default:
							this.drawOwned(coord, countries[cid]);
							break;
					}
				}
			}
		}
		
		if(this.page == "orders")
		{		
			for(var i = 0; i < countrySym.length; i++)
			{
				this.addCountryPath(countrySym[i])
			}
			this.showClickable();
			//alert(active.toString());
			context = new contextMenu(this.map);
		}
		
		//map.setSize(1142 * .9, 964 * .9);
	}
	
	
	/**
	 * run the addclick function on all countries the user has units
	 * in so that the user my take an action
	 */
	this.showClickable = function()
	{
		var activeCountries = this.active.length;
		for(var i = 0; i < activeCountries; i++)
		{
			country = this.active.pop();
			this.rmClick(country);
		}
		
		for(var i = 0; i < currMap[this.uid].length; i++)
		{
			if((currMap[this.uid][i]['type'] == 'f' || currMap[this.uid][i]['type'] == 'a') && !this.inOrdered(currMap[this.uid][i]['aid']))
			{
				/*[what this array contains, the country you selected, the unit type]*/
				this.addClick(["context", currMap[this.uid][i]['aid'], currMap[this.uid][i]['type']]);
				this.active.push(currMap[this.uid][i]['aid']);
			}
		}
	}
		
	
	/**
	 * check if a unit has already been given an order
	 */
	this.inOrdered = function(aid)
	{
		for(var i = 0; i < this.ordered.length; i++)
		{
			if(this.ordered[i] == aid)
			{
				return true;
			}
		}
		return false;
	}

}


/**
 * Contains the data for the context menu object
 * 
 * used to display a context menu asking them what action they want 
 * a unit to take
 */
this.contextMenu = function(myMap)
	{
		this.hold = null;
		this.move = null;
		this.support = null;
		this.convoy = null;
		this.myMap = myMap;
		
		/**
		 * show the contextmenu
		 */
		this.show = function(data)
		{
			var xOffset, yOffset;
			//alert("in show function: " + data.toString());
			
			var coord = unitCoords[data[1]];
			
			xOffset = (coord[0] > 1062) ? 80 : 0;
			yOffset = (coord[1] > 864) ? 100 : 0;
			
			//Hold button
			this.hold = this.myMap.image("map/hold.png", coord[0] - xOffset, coord[1] - yOffset, 80, 25);
			this.hold.node.onclick = function(){
				map.holdClicked(data);};
			this.hold.node.onmouseover = function(){
				context.shade("hold");};
			this.hold.node.onmouseout = function(){
				context.unshade("hold");};
			
			//move button
			this.move = this.myMap.image("map/move.png", coord[0] - xOffset, coord[1] - yOffset + 25, 80, 25);
			this.move.node.onclick = function(){
				map.moveClicked(data);};
			this.move.node.onmouseover = function(){
				context.shade("move");};
			this.move.node.onmouseout = function(){
				context.unshade("move");};
			
			//support button
			this.support = this.myMap.image("map/support.png", coord[0] - xOffset, coord[1] - yOffset + 50, 80, 25);
			this.support.node.onclick = function(){
				map.supportClicked(data);};
			this.support.node.onmouseover = function(){
				context.shade("support");};
			this.support.node.onmouseout = function(){
				context.unshade("support");};
			
			//convoy button
			this.convoy = this.myMap.image("map/convoy.png", coord[0] - xOffset, coord[1] - yOffset + 75, 80, 25);
			this.convoy.node.onclick = function(){
				map.convoyClicked(data);};
			this.convoy.node.onmouseover = function(){
				context.shade("convoy");};
			this.convoy.node.onmouseout = function(){
				context.unshade("convoy");};
		}
		
		/**
		 * set the opacity of a button to 1 when the mouse leaves the
		 * button
		 */
		this.unshade = function(button)
		{
			switch(button)
			{
				case "hold":
					this.hold.attr({'opacity': 1});
					break;
				case "move":
					this.move.attr({'opacity': 1});
					break;
				case "support":
					this.support.attr({'opacity': 1});
					break;
				case "convoy":
					this.convoy.attr({'opacity': 1});
					break;
			}
		}
		
		
		/**
		 * set the opacity of a butten to .5 when the mouse hovers
		 * over it
		 */
		this.shade = function(button)
		{
			switch(button)
			{
				case "hold":
					this.hold.attr({'opacity': 0.5});
					break;
				case "move":
					this.move.attr({'opacity': 0.5});
					break;
				case "support":
					this.support.attr({'opacity': 0.5});
					break;
				case "convoy":
					this.convoy.attr({'opacity': 0.5});
					break;
			}
		}
		
		
		/**
		 * remove the context menu from the screen
		 */
		this.hide = function()
		{
			this.hold.remove();
			this.move.remove();
			this.support.remove();
			this.convoy.remove();
		}
	}


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
