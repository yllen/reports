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
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; 

define('GLPI_ROOT', '../../../..'); 
include (GLPI_ROOT . "/inc/includes.php"); 

$soft = (isset($_POST["soft"]) ? $_POST["soft"] : 0);

if (isset($_POST["display_type"])) {
   $output_type=$_POST["display_type"];
} else {
   $output_type=HTML_OUTPUT;
}

includeLocales("licenses");

plugin_reports_checkRight("licenses","r");
checkSeveralRightsAnd(array(COMPUTER_TYPE=>"r", 
                            SOFTWARE_TYPE=>"r"));

if ($output_type==HTML_OUTPUT || !$soft) {
   commonHeader($LANG['plugin_reports']['licenses'][1],$_SERVER['PHP_SELF'],"utils","report");

   echo "<div class='center'>";
   echo "<form action='".$_SERVER["PHP_SELF"]."' method='post'>";
   echo "<table class='tab_cadre' cellpadding='5'>\n";
   echo "<tr><th colspan='2'>" . $LANG['plugin_reports']['licenses'][1] . "</th></tr>\n";

   echo "<tr class='tab_bg_1 center'><td>" . $LANG['help'][31] . "</td>";
   echo "<td>";
   dropdownSoftwareWithLicense($soft);
   echo "</td></tr>\n";

   echo "<tr class='tab_bg_1 center'><td colspan='2'>";
   echo "<input type='submit' name='action' value='".$LANG['buttons'][2]."' class='submit' />";
   echo "</td></tr>\n";
   echo "</table>";
   echo "</form></div>";

}

if ($soft) {
   simpleReport("licenses", // Report Name

                // SQL statement
                "SELECT `glpi_softwareslicenses`.`name` AS license, 
                        `glpi_softwareslicenses`.`serial`, 
                        `glpi_softwareslicenses`.`number` AS nombre,
                        `glpi_softwareslicensestypes`.`name` AS type,
                        buyversion.`name` AS buy,
                        useversion.`name` AS used,
                        `glpi_softwareslicenses`.`expire`, 
                        `glpi_softwareslicenses`.`comment`,
                        `glpi_computers`.`name`
                 FROM `glpi_softwareslicenses`
                 LEFT JOIN `glpi_softwares` 
                     ON (`glpi_softwareslicenses`.`softwares_id` = `glpi_softwares`.`id`)
                 LEFT JOIN `glpi_computers`
                     ON (`glpi_computers`.`id` = `glpi_softwareslicenses`.`computers_id`)
                 LEFT JOIN `glpi_softwaresversions` AS buyversion 
                     ON (buyversion.`id` = `glpi_softwareslicenses`.`softwaresversions_id_buy`)
                 LEFT JOIN `glpi_softwaresversions` AS useversion 
                     ON (useversion.`id` = `glpi_softwareslicenses`.`softwaresversions_id_use`)
                 LEFT JOIN `glpi_softwareslicensestypes` 
                     ON (`glpi_softwareslicensestypes`.`id` 
                         = `glpi_softwareslicenses`.`softwareslicensestypes_id`)
                 LEFT JOIN `glpi_entities` 
                     ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
                 WHERE `glpi_softwareslicenses`.`softwares_id` = $soft 
                       AND `glpi_softwares`.`is_deleted` = '0' 
                       AND `glpi_softwares`.`is_template` = '0' " .
                       getEntitiesRestrictRequest(' AND ', 'glpi_softwares') ."
                 ORDER BY license",

                 // Columns title (optional), from $LANG
                 array("license" => $LANG['software'][11],
                       "serial"  => $LANG['common'][19],
                       "nombre"  => $LANG['tracking'][29],
                       "type"    => $LANG['common'][17],
                       "buy"     => $LANG['plugin_reports']['licenses'][2],
                       "used"    => $LANG['plugin_reports']['licenses'][3],
                       "expire"  => $LANG['financial'][98],
                       "comment" => $LANG['common'][25],
                       "name"    => $LANG['help'][25]),

                 // Sub title
                 getDropdownName("glpi_softwares", $soft),

                 // Group by
                 array ("license")
   );

}

?>