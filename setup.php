<?php
/**
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2021 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
*/

include_once(Plugin::getPhpDir('reports')."/inc/function.php");

define('PLUGIN_REPORTS_VERSION', '2.0.0');

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

   Plugin::registerClass('PluginReportsProfile', ['addtabon' => ['Profile']]);

   if (Session::haveRight("config", UPDATE)) {
      $PLUGIN_HOOKS['config_page']['reports']     = 'front/config.form.php';
   }

   $PLUGIN_HOOKS['menu_entry']['reports'] = false;

   $rightreport = [];
   $rightstats  = [];

   foreach (searchReport() as $report => $plug) {
      $field = 'plugin_reports_'.$report;
      if ($plug != 'reports') {
         $field = 'plugin_reports_'.$plug."_".$report;
      }
      if (Session::haveRight($field, READ)) {
         $tmp = $LANG["plugin_$plug"][$report];
         //If the report's name contains 'stat' then display it in the statistics page
         //(instead of Report page)
         if (isStat($report)) {
            if (!isset($PLUGIN_HOOKS['stats'][$plug])) {
               $PLUGIN_HOOKS['stats'][$plug] = [];
            }
            $PLUGIN_HOOKS['stats'][$plug]["report/$report/$report.php"] = $tmp;
         } else {
            if (!isset($PLUGIN_HOOKS['reports'][$plug])) {
               $PLUGIN_HOOKS['reports'][$plug] = [];
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

   return ['name'           => _n('Report', 'Reports', 2),
           'version'        => PLUGIN_REPORTS_VERSION,
           'author'         => 'Nelly Mahu-Lasson, Remi Collet',
           'license'        => 'GPLv3+',
           'homepage'       => 'https://forge.glpi-project.org/projects/reports',
           'requirements'   => [
              'glpi' => [
                 'min' => '10.0',
                 'max' => '11.0',
              ]
           ]];
}
