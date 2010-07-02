#!/usr/bin/env python
# -*- coding: utf-8 -*-
#
#       untitled.py
#       
#       Copyright 2010 Jeffrey Minton <ffej@blinking-book>
#       
#       This program is free software; you can redistribute it and/or modify
#       it under the terms of the GNU General Public License as published by
#       the Free Software Foundation; either version 2 of the License, or
#       (at your option) any later version.
#       
#       This program is distributed in the hope that it will be useful,
#       but WITHOUT ANY WARRANTY; without even the implied warranty of
#       MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#       GNU General Public License for more details.
#       
#       You should have received a copy of the GNU General Public License
#       along with this program; if not, write to the Free Software
#       Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
#       MA 02110-1301, USA.

import xml.parsers.expat
import re

svgDict = []
inMap = False
key = ""
subMap = False
inProv = False
aid = ""


def main():
	svg = open("bitmap_std.svg", "r")
	svgXML = svg.read()
	global svgDict
	global inProv
	global aid
	coordRE = re.compile("M | L | z")
	
	
	
	# 3 handler functions
	def start_element(name, attrs):
		if(name == "path" and attrs.has_key('id') and len(attrs['id']) == 3):
			#print attrs
			svgDict.append(attrs['id'])
			
	def end_element(name):
		0
	
	p = xml.parsers.expat.ParserCreate()
	
	p.StartElementHandler = start_element
	p.EndElementHandler = end_element

	p.Parse(svgXML)
	
	print svgDict
	
	countries = "var countries = ["
	for i in range(len(svgDict) - 1):
		countries += "'" + svgDict[i] + "', "
	countries += "'" + svgDict[len(svgDict) - 1] + "'];"
		
	print countries
	
	f = open("countrySym.dat", "w+")
	f.write(countries)
	return 0

if __name__ == '__main__':
	main()
