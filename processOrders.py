#!/usr/bin/python

import MySQLdb
from datetime import datetime
import re
import copy
import sys
import traceback

##@package processOrders
#Checks if all orders for a game are submitted or order deadline is up
#Checks orders for validity
#Executes all order and determines the results

##Sets up sql connection and other parameters
#contains all other functions
def checkOrders():
	##Checks to see which if any games' deadlines are up
	def deadlineUp():
		query ="SELECT * \
			FROM games"
		cursor.execute(query)
		gameTable = cursor.fetchall()
		currTime = datetime.now()
		gameToCheck = []
		for gameRow in gameTable:
			if(gameRow['deadline'] != None and currTime > gameRow['deadline']):
				gameToCheck.append(gameRow['gid'])
	
	##Checks to see if all order are in for a game
	def orderIn():
		query = "SELECT DISTINCT gid \
			FROM games"
		cursor.execute(query)
		games = cursor.fetchall()
		for game in games:
			gid = game['gid']
			query = "SELECT i.uid \
				FROM games g, in_game i \
				WHERE g.gid=i.gid and g.gid=" + str(gid)
			cursor.execute(query)
			players = cursor.fetchall()
			query = "SELECT o.uid \
				FROM games g, orders o \
				WHERE g.gid=" + str(gid) + " and g.gid=o.gid and g.year=o.year and g.season=o.season"
			cursor.execute(query)
			orders = cursor.fetchall()
			if(players == orders):
				execute(gid)
			else:
				#sys.stdout.write(str("orders not in"))
				return "orders not in"
	
	##execute orders for game
	#@param[gid] the game whos orders to execute
	def execute(gid):
		#global out
		orderRE = re.compile("\W*")
		#get the current orders for game gid
		query = "SELECT o.uid, o.orders \
			FROM games g, orders o \
			WHERE g.gid=" + str(gid) + " and g.gid=o.gid and g.year=o.year and g.season=o.season;"
		cursor.execute(query)
		orderTab = cursor.fetchall()
		#sys.stdout.write(str( orderTab))
		
		'''get the current state of the map for game gid'''
		query = "SELECT c.owner, c.type, c.aid \
			FROM games g, curr_map c \
			WHERE g.gid=" + str(gid) + " and g.gid=c.gid and g.year=c.year and g.season=c.season;"
		cursor.execute(query)
		mapTab = cursor.fetchall()
		
		uidOrders = {}
		
		#out += str(orderTab)
		#sys.stdout.write(str(orderTab))
		
		for arr in orderTab:
			uid = arr['uid']
			orders = arr['orders']
			if(not uidOrders.has_key(uid)):
				uidOrders[uid] = []
			orders = orders.strip()
			moves = orderRE.split(orders)
			for i in range(0, len(moves), 3):
				uidOrders[uid].append({'type':moves[i], 'from':moves[i + 1], 'action':moves[i + 2]})
		
		currMap = {}
		for arr in mapTab:
			uid = arr['owner']
			aid = arr['aid']
			unitType = arr['type']
			if(not currMap.has_key(uid)):
				currMap[uid] = {}
			currMap[uid][aid] = unitType
			
		validateOrders(currMap, uidOrders, gid)
		
		
	##Validate all the orders to ensure they can even be executed
	#@param[currMap] the current state of the map
	#@param[orders] a dictionary of orders match uid to orders
	#@param[gid] the games id
	def validateOrders(currMap, orders, gid):
	
		for uid in orders:
			for i in range(len(orders[uid])):
				currOrder = orders[uid][i]
				unitType = currOrder['type']
				fromCo = currOrder['from']
				action = currOrder['action']
				if(currOrder.has_key('result')):
					result = currOrder['result']
				else:
					result = None
				
				'''check if order already resolved'''
				if(result == None):
					'''ensure that player has unit in from country''' 
					if(currMap[uid].has_key(fromCo)):
						'''HOLD action desired'''
						if(action == "holds"):
							for checkUser in orders:
								for j in range(len(orders[checkUser])):
									checkOrder = orders[checkUser][j]
									'''if move order found to hold point'''
									if(fromCo == checkOrder['action'] and currOrder != checkOrder):
										orders[checkUser][j]['result'] = False
										orders[checkUser][j]['note'] = "Attempted move to occupied city"
							orders[uid][i]['result'] = True
							orders[uid][i]['note'] = "Hold successful"	
# 						'''Convoy action desired'''
# 						elif(action == "c"):
# 							0
# 						'''Support action desired'''
# 						elif(action == "s"):
# 							0
# 						'''Move action desired'''
						else:
							success = True
							connection = border[fromCo]
							borderExists = False
							for country in connection:
								if(action == country):
									borderExists = True
							if(borderExists == True):
								for checkUser in orders:
									for j in range(len(orders[checkUser])):
										checkOrder = orders[checkUser][j]
										'''if another move order found to same place as this order'''
										if(action == checkOrder['action'] and currOrder != checkOrder):
											orders[checkUser][j]['result'] = False
											orders[checkUser][j]['note'] = "Two units move to " + action + " both units bounce"
											orders[uid][i]['result'] = False
											orders[uid][i]['note'] = "Two units move to " + action + " both units bounce"
											success = False
							else:
								success = False
								orders[uid][i]['result'] = False
								orders[uid][i]['note'] = "There is no path from " + fromCo + " to " + action

							if(success == True):
								orders[uid][i]['result'] = True
								orders[uid][i]['note'] = "Action successful"
					else:
						orders[uid][i]['result'] = False
						orders[uid][i]['note'] = "Player does not own that country"	
		#sys.stdout.write( str(orders))
		update(orders, currMap, gid)
	
	##Update orders with results and fill in current map table
	#@param[orders] the orders now containing the reasults
	#@param[currMap] the current map
	#@param[gid] the gid of the game
	def update(orders, currMap, gid):
		#global out
		query = "SELECT * \
			FROM games \
			WHERE gid=" + str(gid)
		cursor.execute(query)
		game = cursor.fetchone()
		year = game['year']
		season = game['season']

		for uid in orders:
			resultStr = ""
			for i in range(len(orders[uid])):
				currOrder = orders[uid][i]
				unitType = currOrder['type']
				fromCo = currOrder['from']
				action = currOrder['action']
				result = currOrder['result']
				note = currOrder['note']
				resultStr += (unitType + " " + fromCo + "-" + 
					action + ": " + str(result) + ", " + note + "\r\n")
			query = "UPDATE orders \
				SET result='" + resultStr + "' \
				WHERE uid='" + uid + "' and gid=" + str(gid) + " and year= " + str(year) + " and season='" + season + "'"
			cursor.execute(query)
		
		if(season == 'f'):
			season = 's'
			year += 1
		else:
			season = 'f'
		
		query = "UPDATE games \
			SET year=" + str(year) + ", season='" + season + "', \
			deadline=TIMESTAMPADD(WEEK, 1, deadline)\
			WHERE gid=" + str(gid)
		cursor.execute(query)
		
		newMap = copy.deepcopy(currMap)
		
		
		#out += currMap
		#sys.stdout.write(str( currMap))
		'''move all armies that had a successful order'''
		for uid in currMap:
			for aid in currMap[uid]:
				for order in orders[uid]:
					if(order['from'] == aid and order['result'] == True and order['action'] != "holds"):
						newMap[uid][order['action']] = order['type']
						newMap[uid][order['from']] = None
						'''remove taken over teritories'''
						for uidCheck in currMap:
							if(uidCheck != uid and currMap[uidCheck].has_key(order['action'])):
								del newMap[uidCheck][order['action']]
		#out += newMap
		#sys.stdout.write(str( newMap))					
		for uid in newMap:
			for aid in newMap[uid]:
				if(newMap[uid][aid] != None):
					query = "INSERT INTO curr_map(gid, owner, type, year, season, aid) \
						VALUES(" + str(gid) + ", '" + uid + "', \
						'" + newMap[uid][aid] + "', " + str(year) + ", \
						'" + season + "', '" + aid + "')"
				else:
					query = "INSERT INTO curr_map(gid, owner, year, season, aid) \
						VALUES(" + str(gid) + ", '" + uid + "', " + str(year) + ", \
						'" + season + "', '" + aid + "')"
				#out += query
				#sys.stdout.write(str( query))
				cursor.execute(query)
				
	conn = MySQLdb.connect(host="localhost", user="diplomacy", 
		passwd="1B80A65167C5AD50A288593B04F4EEBF37", db="diplomacy")
	cursor = conn.cursor(MySQLdb.cursors.DictCursor)
	f = open("accessTime.dat", "a+")
	f.write("Second: " + str(datetime.now()) + "\n")
	#out = ""
	
	deadlineUp()
	orderIn()
	#return out


border = {'tun': ['ion', 'tyn', 'wes', 'naf'], 
'sev': ['arm', 'bla', 'rum', 'ukr', 'mos'], 
'ser': ['bud', 'tri', 'bul', 'rum', 'gre', 'alb'],
'nap': ['apu', 'ion', 'tyn', 'rom'], 
'vie': ['boh', 'tri', 'bud', 'gal', 'tyr'], 
'lon': ['yor', 'eng', 'nth', 'wal'], 
'edi': ['cly', 'lyp', 'yor', 'nrg', 'nth'], 
'alb': ['adr', 'ion', 'ser', 'tri', 'gre'], 
'nwy': ['nth', 'stp', 'ska', 'swe', 'fin'], 
'ank': ['bla', 'con', 'arm', 'smy'], 
'pru': ['sil', 'war', 'lvn', 'bal', 'ber'], 
'mar': ['gol', 'pie', 'bur', 'gas', 'spa'], 
'spa': ['gol', 'mar', 'por', 'wes', 'mid', 'gas'], 
'bre': ['gas', 'par', 'mid', 'eng', 'pic'], 
'arm': ['ank', 'bla', 'smy', 'syr', 'sev'], 
'rom': ['nap', 'tyn', 'tus', 'apu'], 
'gol': ['pie', 'tyn', 'wes', 'spa', 'mar', 'tus'], 
'wal': ['lon', 'yor', 'iri', 'eng', 'lvp'], 
'naf': ['tun', 'mid', 'wes'], 
'smy': ['aeg', 'ank', 'con', 'eas', 'arm', 'syr'], 
'eng': ['bel', 'bre', 'lon', 'nth', 'pic', 'wal', 'mid', 'iri'], 
'tyr': ['boh', 'ven', 'vie', 'tri', 'mun', 'pie'], 
'mid': ['bre', 'eng', 'gas', 'iri', 'naf', 'por', 'spa', 'wes', 'nat'], 
'hol': ['kie', 'ruh', 'bel', 'nth', 'hel'], 
'swe': ['bal', 'bar', 'fin', 'nrg', 'nwy', 'bot', 'ska', 'den'], 
'ukr': ['war', 'mos', 'sev', 'gal'], 
'wes': ['naf', 'tun', 'tyn', 'mid', 'spa', 'gol'], 
'iri': ['eng', 'lvp', 'wal', 'nat', 'mid'], 
'gre': ['aeg', 'alb', 'ion', 'ser', 'bul'], 
'ska': ['den', 'nth', 'nwy', 'swe'], 
'kie': ['ber', 'mun', 'ruh', 'bal', 'hel', 'hol'], 
'nat': ['cly', 'iri', 'lvp', 'mid', 'nrg'], 
'hel': ['hol', 'kie', 'bal', 'den', 'nth'], 
'mun': ['boh', 'bur', 'ruh', 'tyr', 'kie', 'ber', 'sil'], 
'fin': ['nwy', 'stp', 'bot', 'swe'], 
'war': ['lvn', 'pru', 'sil', 'mos', 'gal', 'ukr'], 
'sil': ['boh', 'mun', 'gal', 'war', 'ber', 'pru'], 
'ruh': ['bur', 'bel', 'hol', 'kie', 'mun'], 
'pic': ['bre', 'par', 'eng', 'bel', 'bur'], 
'den': ['hel', 'kei', 'nth', 'swe', 'bal', 'ska'], 
'rum': ['bla', 'bud', 'gal', 'ser', 'sev', 'bul'], 
'mos': ['lvn', 'sev', 'ukr', 'war', 'stp'], 
'gas': ['mar', 'par', 'spa', 'mid', 'bur', 'bre'], 
'tus': ['gol', 'rom', 'tyn', 'ven', 'pie'], 
'nrg': ['cly', 'edi', 'nat', 'nth', 'swe', 'bar'], 
'pie': ['tus', 'tyr', 'ven', 'mar', 'gol'], 
'syr': ['eas', 'smy', 'arm'], 
'gal': ['boh', 'sil', 'ukr', 'vie', 'war', 'rum', 'bud'], 
'bul': ['aeg', 'bla', 'gre', 'rum', 'ser', 'con'], 
'ven': ['apu', 'tri', 'pie', 'tus', 'adr', 'tyr'], 
'adr': ['apu', 'ven', 'alb', 'tri', 'ion'], 
'eas': ['ion', 'syr', 'smy', 'aeg'], 
'apu': ['nap', 'rom', 'ion', 'adr', 'ven'], 
'bud': ['gal', 'vie', 'rum', 'ser', 'tri'], 
'tri': ['adr', 'bud', 'tyr', 'ven', 'vie', 'alb', 'ser'], 
'bar': ['nrg', 'stp', 'swe'], 
'lvp': ['cly', 'wal', 'yor', 'nat', 'iri'], 
'bel': ['hol', 'nth', 'pic', 'ruh', 'eng', 'bur'], 
'nth': ['edi', 'hel', 'hol', 'lon', 'yor', 'ska', 'den', 'nwy', 'nrg', 'eng', 'bel'], 
'tyn': ['nap', 'tus', 'gol', 'wes', 'tun', 'ion', 'rom'], 
'bot': ['bal', 'fin', 'swe', 'stp', 'lvn'], 
'bur': ['bel', 'gas', 'mar', 'par', 'pic', 'mun', 'ruh'], 
'ion': ['adr', 'apu', 'nap', 'tyn', 'gre', 'alb', 'aeg', 'tun', 'eas'], 
'stp': ['bar', 'bot', 'fin', 'lvn', 'mos', 'nwy'], 
'aeg': ['bla', 'eas', 'ion', 'smy', 'con', 'bul', 'gre'], 
'ber': ['mun', 'pru', 'sil', 'bal', 'kie'], 
'bal': ['ber', 'den', 'hel', 'kie', 'pru', 'lvn', 'bot', 'swe'], 
'lvn': ['bal', 'bot', 'pru', 'stp', 'mos', 'war'], 
'con': ['aeg', 'bla', 'bul', 'smy', 'ank'],
'boh': ['vie', 'tyr', 'mun', 'sil', 'gal'],
'cly': ['lvp', 'nat', 'edi', 'nrg'],
'yor': ['nth', 'lon', 'wal', 'lvp', 'edi'],
'par': ['bur', 'gas', 'bre', 'pic'],
'nap': ['apu', 'ion', 'tyn', 'rom'],
'por': ['spa', 'mid'],
'bla': ['sev', 'arm', 'ank', 'con', 'aeg', 'bul', 'rum']}

f = open("accessTime.dat", "a+")
f.write("First: " + str(datetime.now()) + "\n")
try:
	checkOrders()
except:
	print "Trigger Exception, traceback info forward to log file."
	traceback.print_exc(file=open("errlog.txt","w"))
	sys.exit(20)

