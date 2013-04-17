<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2013 by the reports Development Team.

 https://forge.indepnet.net/projects/reports
 -------------------------------------------------------------------------

 LICENSE

 This file is part of reports.

 reports is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with reports. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

include_once(GLPI_ROOT . '/plugins/reports/inc/function.php');

define ("REPORTS_NO_ENTITY_RESTRICTION", 0);
define ("REPORTS_CURRENT_ENTITY", 1);
define ("REPORTS_SUB_ENTITIES", 2);


function plugin_init_reports() {
   global $PLUGIN_HOOKS, $DB, $LANG;

   $PLUGIN_HOOKS['csrf_compliant']['reports'] = true;

   $plugin = new plugin;

   //Define only for bookmarks
   Plugin::registerClass('PluginReportsReport');

   Plugin::registerClass('PluginReportsStat');

   Plugin::registerClass('PluginReportsProfile', array('addtabon' => array('Profile')));


   $PLUGIN_HOOKS['change_profile']['reports'] = array('PluginReportsProfile','changeprofile');

   if (Session::haveRight("config", "w")) {
      $PLUGIN_HOOKS['config_page']['reports']     = 'front/config.form.php';
   }

   $PLUGIN_HOOKS['menu_entry']['reports']     = false;
   $PLUGIN_HOOKS['pre_item_purge']['reports'] = array('Profile' => array('PluginReportsProfile',
                                                                         'cleanProfile'));
   $PLUGIN_HOOKS['item_clone']['reports']     = array('Profile' => array('PluginReportsProfile',
                                                                         'cloneProfile'));

   $rightreport = array ();
   $rightstats  = array ();

   foreach (searchReport() as $report => $plug) {
      if (plugin_reports_haveRight($plug, $report, "r")) {
         $tmp = $LANG["plugin_$plug"][$report];
         //If the report's name contains 'stat' then display it in the statistics page
         //(instead of Report page)
         if (isStat($report)) {
            if (!isset($PLUGIN_HOOKS['stats'][$plug])) {
               $PLUGIN_HOOKS['stats'][$plug] = array();
            }
            $PLUGIN_HOOKS['stats'][$plug]["report/$report/$report.php"] = $tmp;
         } else {
            if (!isset($PLUGIN_HOOKS['reports'][$plug])) {
               $PLUGIN_HOOKS['reports'][$plug] = array();
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

   return array('name'           => _n('Report', 'Reports', 2),
                'version'        => '1.7.0',
                'author'         => 'Nelly Mahu-Lasson, Remi Collet, Walid Nouh',
                'license'        => 'GPLv2+',
                'homepage'       => 'https://forge.indepnet.net/projects/reports',
                'minGlpiVersion' => '0.84');
}


function plugin_reports_check_config() {
   return true;
}


function plugin_reports_haveRight($plug, $report, $right) {

   $module = ($plug == 'reports' ? $report : $plug.'_'.$report);
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
         Html::redirect($CFG_GLPI["root_doc"] . "/index.php");
         exit();
      }
      Html::displayRightError();
   }
}



// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_reports_check_prerequisites() {
   global $LANG;

   if (version_compare(GLPI_VERSION,'0.84','lt') || version_compare(GLPI_VERSION,'0.85','ge')) {
      echo "This plugin requires GLPI >= 0.84 and GLPI < 0.85";
      return false;
   }
   return true;
}
?>