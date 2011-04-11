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

foreach (glob(GLPI_ROOT . '/plugins/reports/inc/*.php') as $file) {
   //include_once ($file);
}
include_once(GLPI_ROOT . '/plugins/reports/inc/function.php');

define ("REPORTS_NO_ENTITY_RESTRICTION",0);
define ("REPORTS_CURRENT_ENTITY",1);
define ("REPORTS_SUB_ENTITIES",2);

function plugin_init_reports() {
   global $PLUGIN_HOOKS, $DB, $LANG;

   $plugin = new plugin;

   //Define only for bookmarks
   Plugin::registerClass('PluginReportsReport');

   Plugin::registerClass('PluginReportsStat');

   Plugin::registerClass('PluginReportsProfile');


   $PLUGIN_HOOKS['change_profile']['reports'] = array('PluginReportsProfile','changeprofile');

   if (haveRight("config", "w")) {
      $PLUGIN_HOOKS['headings']['reports']        = 'plugin_get_headings_reports';
      $PLUGIN_HOOKS['headings_action']['reports'] = 'plugin_headings_actions_reports';
      $PLUGIN_HOOKS['config_page']['reports']     = 'front/config.form.php';
   }
   $PLUGIN_HOOKS['menu_entry']['reports']     = false;
   $PLUGIN_HOOKS['pre_item_purge']['reports'] = array('Profile' => array('PluginReportsProfile','cleanProfiles'));

   $rightreport = array ();
   $rightstats = array ();

   foreach (searchReport() as $report => $plug) {
      if (plugin_reports_haveRight($plug, $report, "r")) {
         $tmp = $LANG["plugin_$plug"][$report][1];
         //If the report's name contains 'stat' then display it in the statistics page
         //(instead of Report page)
         if (isStat($report)) {
            if (!isset($PLUGIN_HOOKS['stats'][$plug])) {
               $PLUGIN_HOOKS['stats'][$plug]=array();
            }
            $PLUGIN_HOOKS['stats'][$plug]["report/$report/$report.php"] = $tmp;
         } else {
            if (!isset($PLUGIN_HOOKS['reports'][$plug])) {
               $PLUGIN_HOOKS['reports'][$plug]=array();
            }
            $PLUGIN_HOOKS['reports'][$plug]["report/$report/$report.php"] = $tmp;
         }
      }
   }
}

/**
 * Indicate if the report must be displayed in reports or statistics menu
 * @param $report_name the name of the report
 * @return true if it's a stat, false if it's a report
 */
function isStat($report_name) {

   if (strpos($report_name, 'stat') !== false) {
      return true;
   }
   return false;
}


function plugin_version_reports() {
   global $LANG;

   return array('name'     => $LANG['plugin_reports']['title'][1],
                'version'  => '1.5.0',
                'author'   => 'Nelly LASSON',
                'homepage' => 'https://forge.indepnet.net/projects/show/reports',
                'minGlpiVersion' => '0.80');
}


function plugin_reports_check_config() {
   return true;
}


function plugin_reports_haveRight($plug, $report, $right) {

   $module = ($plug=='reports' ? $report : $plug.'_'.$report);
   $matches = array(""  => array("","r","w"), // ne doit pas arriver normalement
                    "r" => array("r","w"),
                    "w" => array("w"),
                    "1" => array("1"),
                    "0" => array("0","1")); // ne doit pas arriver non plus

   if (isset($_SESSION["glpi_plugin_reports_profile"][$module])
       && in_array($_SESSION["glpi_plugin_reports_profile"][$module],$matches[$right])) {
      return true;
   }
   return false;
}


function plugin_reports_checkRight($plug, $module, $right) {
   global $CFG_GLPI;

   if (!plugin_reports_haveRight($plug, $module, $right)) {
      // Gestion timeout session
      if (!isset ($_SESSION["glpiID"])) {
         glpi_header($CFG_GLPI["root_doc"] . "/index.php");
         exit ();
      }
      displayRightError();
   }
}



// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_reports_check_prerequisites() {
   global $LANG;

   if (GLPI_VERSION < 0.80) {
      echo "GLPI version not compatible need 0.80";
   } else {
      return true;
   }
}

?>