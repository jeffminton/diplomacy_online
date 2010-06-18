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

svgDict = {}
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
		#print name
		global svgDict
		global inProv
		global aid
		#global inMap
		#print 'Start element:', name, attrs
		
		#if(inMap == True):
			#svgDict[attrs["id"]] = attrs["d"]
			
		#if(attrs.has_key("id")):
			#if(attrs["id"] == "MouseLayer"):
				#inMap = True
		if(inProv == True and name == "UNIT"):
			svgDict[aid] = attrs
		elif(name == "PROVINCE"):
			aid = attrs['name']
			inProv = True
		
			
	def end_element(name):
		#global svgDict
		#print "\t" + name
		global inMap
		global inProv
		global aid
		#print '\tEnd element:', name
		
		if(name == "PROVINCE"):
			inProv = False
	
	p = xml.parsers.expat.ParserCreate()
	
	p.StartElementHandler = start_element
	p.EndElementHandler = end_element

	p.Parse(svgXML)
	
	print svgDict
	
	countries = "var unitCoords = Array();\n"
	for aid in svgDict:
		countries += "unitCoords['" + aid + "'] = [" + svgDict[aid]['x'] + ", " + svgDict[aid]['y'] + "];\n"
		
	print countries
	
	f = open("coords.dat", "w+")
	f.write(countries)
	return 0

if __name__ == '__main__':
	main()
