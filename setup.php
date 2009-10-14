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
   include_once ($file);
}

define ("REPORTS_NO_ENTITY_RESTRICTION",0);
define ("REPORTS_CURRENT_ENTITY",1);
define ("REPORTS_SUB_ENTITIES",2);

function plugin_init_reports() {
   global $PLUGIN_HOOKS, $DB, $LANG;

   $plugin = new plugin;

   //Define only for bookmarks
   registerPluginType('reports', 'PLUGIN_REPORTS_REPORT_TYPE', 3050, 
                      array ('classname' => '',
                             'tablename' => '',
                             'formpage' => '',
                             'searchpage' => '',
                             'typename' => $LANG['plugin_reports']['title'][1]));

   registerPluginType('reports', 'PLUGIN_REPORTS_STAT_TYPE', 3051, 
                      array ('classname' => '',
                             'tablename' => '',
                             'formpage' => '',
                             'searchpage' => '',
                             'typename' => $LANG['Menu'][13]));

   $PLUGIN_HOOKS['change_profile']['reports'] = 'plugin_reports_changeprofile';

   if (haveRight("config", "w")) {
      $PLUGIN_HOOKS['headings']['reports'] = 'plugin_get_headings_reports';
      $PLUGIN_HOOKS['headings_action']['reports'] = 'plugin_headings_actions_reports';
      $PLUGIN_HOOKS['config_page']['reports'] = 'front/plugin_reports.config.form.php';
   }
   $PLUGIN_HOOKS['menu_entry']['reports'] = false;
   $PLUGIN_HOOKS['pre_item_delete']['reports'] = 'plugin_pre_item_delete_reports';

   $rightreport = array ();
   $rightstats = array ();

   foreach (searchReport("../plugins/reports/report") as $report => $val) {
      if (plugin_reports_haveRight($report, "r")) {
         if (isset ($LANG['plugin_reports'][$report][1])) {
            $tmp = $LANG['plugin_reports'][$report][1];
         } else {
            $tmp = $report;
         }
         //If the report's name contains 'stat' then display it in the statistics page 
         //(instead of Report page)
         if (isStat($report)) {
            $rightstats["report/$report/" . $report . ".php"] = $tmp;
         } else {
            $rightreport["report/$report/" . $report . ".php"] = $tmp;
         }
      }
   }
   if (count($rightreport) > 0) {
      $PLUGIN_HOOKS['reports']['reports'] = $rightreport;
   }
   if (count($rightstats) > 0) {
      $PLUGIN_HOOKS['stats']['reports'] = $rightstats;
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
   } else {
      return false;
   }
}

function plugin_version_reports() {
   global $LANG;

   return array ('name' => $LANG['plugin_reports']['title'][1],
                 'version' => '1.3.1',
                 'author' => 'Nelly LASSON',
                 'homepage' => 'http://glpi-project.org/wiki/doku.php?id=' . 
                               substr($_SESSION["glpilanguage"], 0, 2) . ':plugins:pluginslist',
                 'minGlpiVersion' => '0.72'); // For compatibility / no install in version < 0.72
}

function plugin_reports_check_config() {
   return true;
}

function plugin_reports_install() {
   global $DB;

   if (!TableExists("glpi_plugin_reports_profiles")) {
      $query = "CREATE TABLE IF NOT EXISTS 
                `glpi_plugin_reports_profiles` (`ID` int(11) NOT NULL auto_increment,
                                                `profile` varchar(255) NOT NULL,
                                                PRIMARY KEY (`ID`)) 
                ENGINE=MyISAM";
      $DB->query($query) or die($DB->error());
   }
   return true;
}
?>