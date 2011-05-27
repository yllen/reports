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

// Original Author of file: BALPE Dévi
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Search for reports in all activated plugins
 *
 * @return tab : an array which contains all the reports found (name => plugin)
 */
function searchReport() {
   global $DB;

   $tab = array ();
   foreach ($DB->request('glpi_plugins', array('state'=>Plugin::ACTIVATED)) as $plug) {
      foreach (glob(GLPI_ROOT.'/plugins/'.$plug['directory'].'/report/*', GLOB_ONLYDIR) as $path) {
         $tab[basename($path)] = $plug['directory'];
         includeLocales(basename($path), $plug['directory']);
      }
   }

   return $tab;
}


/**
 * Include locales for a specific report
 *
 * @param $report_name the name of the report to use
 * @param $plug directory of plugin
 *
 * @return boolean, true if locale found
 */
function includeLocales($report_name, $plugin='reports') {
   global $CFG_GLPI, $LANG;

   $prefix = GLPI_ROOT . "/plugins/$plugin/report/". $report_name ."/" . $report_name;

   if (isset ($_SESSION["glpilanguage"])
       && file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {

      include_once  ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);
   } else if (file_exists($prefix . ".en_GB.php")) {
      include_once  ($prefix . ".en_GB.php");
   } else if (file_exists($prefix . ".fr_FR.php")) {
      include_once  ($prefix . ".fr_FR.php");
   } else {
      // At least defined report name
      if (!isset($LANG["plugin_$plugin"][$report_name][1])) {
         $LANG["plugin_$plugin"][$report_name][1] = $report_name;
      }
      logInFile('php-errors', "includeLocales($report_name,$plugin) => not found\n");
      return false;
   }

   return true;
}


/**
 * Manage display and export of an sql query
 * @param name name of the report
 * @param sql the sql query to execute
 * @param cols the columns and their name to display
 * @param subname second level of name to display
 * @param group an array which contains all the fields to use in GROUP BY sql instruction
 */
function simpleReport($name, $sql, $cols = array (), $subname = "", $group = array ()) {
   global $DB, $LANG, $CFG_GLPI;

   $report = new AutoReport($name);

   if (count($cols)) {
      $report->setColumnsNames($cols);
   }
   if (!empty($subname)) {
      $report->setSubName($subname);
   }
   if (count($group)) {
      $report->setGroupBy($group);
   }
   $report->setSqlRequest($sql);
   $report->execute();
}


function getPriorityLabelsArray() {

   return array("1" => Ticket::getPriorityName(1),
                "2" => Ticket::getPriorityName(2),
                "3" => Ticket::getPriorityName(3),
                "4" => Ticket::getPriorityName(4),
                "5" => Ticket::getPriorityName(5),
                "6" => Ticket::getPriorityName(6));
}


function getImpactLabelsArray() {

   return array("1" => Ticket::getImpactName(1),
                "2" => Ticket::getImpactName(2),
                "3" => Ticket::getImpactName(3),
                "4" => Ticket::getImpactName(4),
                "5" => Ticket::getImpactName(5));
}


function getUrgencyLabelsArray() {

   return array("1" => Ticket::getUrgencyName(1),
                "2" => Ticket::getUrgencyName(2),
                "3" => Ticket::getUrgencyName(3),
                "4" => Ticket::getUrgencyName(4),
                "5" => Ticket::getUrgencyName(5));
}


/**
 * This function should be in the core
 */
function displayOutputFormat() {
   global $LANG,$CFG_GLPI;

   echo "<select name='display_type'>";
   echo "<option value='" . PDF_OUTPUT_LANDSCAPE . "'>" . $LANG['buttons'][27] . " " .
          $LANG['common'][68] . "</option>";
   echo "<option value='" . PDF_OUTPUT_PORTRAIT . "'>" . $LANG['buttons'][27] . " " .
          $LANG['common'][69] . "</option>";
   echo "<option value='" . SYLK_OUTPUT . "'>" . $LANG['buttons'][28] . "</option>";
   echo "<option value='" . CSV_OUTPUT . "'>" . $LANG['buttons'][44] . "</option>";
   echo "<option value='-" . PDF_OUTPUT_LANDSCAPE . "'>" . $LANG['buttons'][29] . " " .
          $LANG['common'][68] . "</option>";
   echo "<option value='-" . PDF_OUTPUT_PORTRAIT . "'>" . $LANG['buttons'][29] . " " .
          $LANG['common'][69] . "</option>";
   echo "<option value='-" . SYLK_OUTPUT . "'>" . $LANG['buttons'][30] . "</option>";
   echo "<option value='-" . CSV_OUTPUT . "'>" . $LANG['buttons'][45] . "</option>";
   echo "</select>";
   echo "&nbsp;<input type='image' name='export' src='" . $CFG_GLPI["root_doc"] .
         "/pics/greenbutton.png' title='" . $LANG['buttons'][31] . "' value='" .
         $LANG['buttons'][31] . "'>";
}


function getReportConfigPage($plugin,$report_name) {
   return GLPI_ROOT."/plugins/$plugin/report/$report_name/".$report_name.".config".".php";
}

?>