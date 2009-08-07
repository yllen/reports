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
 * 		Generate a detailed license report
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0;

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (isset ($_POST["display_type"])) {
	$output_type = $_POST["display_type"];
} else {
	$output_type = HTML_OUTPUT;
}

includeLocales("licensesexpires");

plugin_reports_checkRight("licensesexpires", "r");
checkSeveralRightsAnd(array (
	COMPUTER_TYPE => "r",
	SOFTWARE_TYPE => "r"
));

simpleReport(
	// Report Name
	"licensesexpires",

	// SQL statement
	"SELECT glpi_softwarelicenses.expire,
		glpi_softwarelicenses.name,
		CONCAT(glpi_software.name,' - ',buyversion.name) AS software,
		glpi_softwarelicenses.serial,
		glpi_entities.completename,
		glpi_softwarelicenses.comments,
		glpi_computers.name AS ordinateur
	FROM glpi_softwarelicenses
	LEFT JOIN glpi_software ON (glpi_softwarelicenses.sID=glpi_software.ID)
	LEFT JOIN glpi_dropdown_licensetypes ON (glpi_dropdown_licensetypes.ID=glpi_softwarelicenses.type)
	LEFT JOIN glpi_softwareversions AS buyversion ON (buyversion.ID=glpi_softwarelicenses.buy_version)
	LEFT JOIN glpi_infocoms ON (glpi_infocoms.FK_device=glpi_softwarelicenses.ID)
	LEFT JOIN glpi_entities ON (glpi_software.FK_entities = glpi_entities.ID)
	LEFT JOIN glpi_computers ON (glpi_softwarelicenses.FK_computers=glpi_computers.ID) 
	WHERE glpi_software.deleted=0 
		AND glpi_software.is_template='0'
		AND glpi_infocoms.device_type=" . SOFTWARELICENSE_TYPE . "
		AND (glpi_softwarelicenses.otherserial!='' OR glpi_infocoms.num_immo !='')	" .
	getEntitiesRestrictRequest(' AND ', 'glpi_softwarelicenses') ."
	ORDER BY glpi_softwarelicenses.expire, name",
	
	array (
		"expire" => $LANG["financial"][88],
		"name" => $LANG['plugin_reports']['licensesexpires'][2],
		"software" => $LANG['plugin_reports']['licensesexpires'][3],
		"serial" => $LANG["common"][19],
		"completename" => $LANG["entity"][0],
		"comments" => $LANG["common"][25],
		"ordinateur" => $LANG['help'][25]
	),
	"",
	array("expire","name")
);
?>