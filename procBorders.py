import MySQLdb

def readBorders():
	border = {}
	conn = MySQLdb.connect(host="localhost", user="diplomacy", passwd="hhhbbthhAG88773dip", db="diplomacy")
	
	cursor = conn.cursor()
	query = "SELECT * FROM border;"
	cursor.execute(query)
	for i in range(cursor.rowcount):
		row = cursor.fetchone()
		if(not border.has_key(row[0])):
			cursor1 = conn.cursor()
			query = "SELECT * FROM border WHERE cid_1='" + row[0] + "';"
			cursor1.execute(query)
			cursor2 = conn.cursor()
			query = "SELECT * FROM border WHERE cid_2='" + row[0] + "';"
			cursor2.execute(query)
			connect = []
			for j in range(cursor1.rowcount):
				temp = cursor1.fetchone()
				connect.append(temp[1])
			for j in range(cursor2.rowcount):
				temp = cursor2.fetchone()
				connect.append(temp[0])
			border[row[0]] = connect
	bordAmt = len(border)
	phpBorder = "$border = array("
	for country in border:
		bordAmt -= 1
		phpBorder += "'" + country + "' " + "=>" + " array("
		countAmt = len(border[country])
		for connection in border[country]:
			countAmt -= 1
			phpBorder += "'" + connection + "'"
			if(countAmt == 0):
				phpBorder += ")"
			else:
				phpBorder += ", "
		if(bordAmt == 0):
			phpBorder += ")"
		else:
			phpBorder += ", "
	javaBorder = "var border = new Array();\n"
	for country in border:
		javaBorder += "border['" + country + "'] = Array("
		countAmt = len(border[country])
		for connection in border[country]:
			countAmt -= 1
			javaBorder += "'" + connection + "'"
			if(countAmt == 0):
				javaBorder += ");\n"
			else:
				javaBorder += ", "
	f = open("data.dat", "w+")
	border = "python border\nborder = " + str(border) + "\n\n"
	phpBorder = "php border\n" + phpBorder + "\n\n"
	javaBorder = "java border\n" + javaBorder + "\n\n"
	f.write(str(border))
	f.write(str(phpBorder))
	f.write(str(javaBorder))
	cursor.close()
	conn.close()

readBorders()
