<?php
/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Remi Collet
 * 
 * Purpose of file: 
 * 		Generate location report
 * 		Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */ 

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

$NEEDED_ITEMS=array("search");
define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

simpleReport(
	// Report Name
	"equipmentbylocation", 

	// SQL statement
	"select i.entity,i.location,i.computernumber,i.networkingnumber,i.monitornumber,i.printernumber,j.peripheralsnumber, l.phonesnumber" .
	" from (select g.entity,g.location,g.computernumber,g.networkingnumber,g.monitornumber,h.printernumber,g.id" .
	" from (select e.entity,e.location,e.computernumber,e.networkingnumber,f.monitornumber,e.id" .
	" from (select c.entity,c.location,c.computernumber,d.networkingnumber,c.id" .
	" from (select a.entity,a.location,b.computernumber,a.id" .
	" from (SELECT glpi_entities.completename AS entity, glpi_dropdown_locations.completename AS location,glpi_dropdown_locations.id as id" .
	" FROM glpi_dropdown_locations LEFT JOIN glpi_entities ON (glpi_dropdown_locations.FK_entities=glpi_entities.ID) ".
	getEntitiesRestrictRequest(" WHERE ", "glpi_dropdown_locations") .
	") a" .
	" LEFT OUTER JOIN (SELECT count(glpi_computers.name) as computernumber, glpi_computers.location as id FROM glpi_computers group by glpi_computers.location) b ON (a.id=b.id)) c" .
	" LEFT OUTER JOIN (SELECT count(glpi_networking.name) as networkingnumber, glpi_networking.location as id FROM glpi_networking group by glpi_networking.location) d ON (c.id=d.id)) e" .
	" LEFT OUTER JOIN (SELECT count(glpi_monitors.name) as monitornumber, glpi_monitors.location as id FROM glpi_monitors group by glpi_monitors.location) f ON (e.id=f.id)) g" .
	" LEFT OUTER JOIN (SELECT count(glpi_printers.name) as printernumber, glpi_printers.location as id FROM glpi_printers group by glpi_printers.location) h ON (g.id=h.id)) i" .
	" LEFT OUTER JOIN (SELECT count(glpi_peripherals.name) as peripheralsnumber, glpi_peripherals.location as id FROM glpi_peripherals group by glpi_peripherals.location) j ON (i.id=j.id)" .
	" LEFT OUTER JOIN (SELECT count(glpi_phones.name) as phonesnumber, glpi_phones.location as id FROM glpi_phones group by glpi_phones.location) l ON (i.id=l.id)" .	
	
	" ORDER BY i.entity,i.location",
	
	// Columns title (optional), from $LANG
	array (
		"entity" => $LANG["entity"][0],
		"location" => $LANG["common"][15],
		"computernumber" => $LANG["Menu"][0],
		"networkingnumber" => $LANG["Menu"][1],
		"monitornumber" => $LANG["Menu"][3],
		"printernumber" => $LANG["Menu"][2],
		"peripheralsnumber" => $LANG["Menu"][16],
		"phonesnumber" => $LANG['Menu'][34]
		),
		
	// Sub title
	"",
	
	// Group by
	array ("entity")
	);
 
?>
