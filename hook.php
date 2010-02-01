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

// Original Author of file: Nelly LASSON
// Purpose of file:
// ----------------------------------------------------------------------

function plugin_get_headings_reports($item,$withtemplate) {
   global $LANG;

   if (get_class($item)=='Profile') {
      if ($item->fields['interface']!='helpdesk') {
         return array(1 => $LANG['plugin_reports']['title'][1]);
      }
   }
   return false;
}


function plugin_headings_actions_reports($item) {

   switch (get_class($item)) {
      case 'Profile' :
         if ($item->getField('interface')=='central') {
            return array(1 => "plugin_headings_reports");
         }
         break;
   }
   return false;
}


function plugin_headings_reports($item, $withtemplate=0) {
   global $CFG_GLPI;

   $prof = new PluginReportsProfile();
   switch (get_class($item)) {
      case 'Profile' :
         //Check if new reports added
         $prof->updatePluginRights(GLPI_ROOT."/plugins/reports/report");

         $id = $item->getField('id');
         if (!$prof->getFromDB($id)) {
            $prof->createaccess($id);
         }
         $prof->showForm($id,
                         array('target' => $CFG_GLPI["root_doc"]."/plugins/reports/front/profile.form.php"));
         break;
   }
}


// Hook done on delete item case
function plugin_pre_item_purge_reports($item) {

   switch (get_class($item)) {
      case 'Profile' :
         // Manipulate data if needed
         $ReportProfile = new PluginReportsProfile;
         $ReportProfile->cleanProfiles($item-getField('id'));
         break;
   }
   return $item;
}


function plugin_reports_install() {
   global $DB;

   $query = '';
   if (TableExists('glpi_plugin_reports_profiles')) { //1.1 ou 1.2
      if (FieldExists('glpi_plugin_reports_profiles','ID')) { // version installee < 1.4.0
         $query = "ALTER TABLE `glpi_plugin_reports_profiles`
                   CHANGE `ID` `id` int(11) NOT NULL auto_increment";
      }
   } else {
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_reports_profiles` (
                  `id` int(11) NOT NULL auto_increment,
                  `profile` varchar(255) NOT NULL,
                PRIMARY KEY (`id`))
                ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
   }
   if ($query) {
      $DB->query($query) or die($DB->error());
   }


   return true;
}


function plugin_reports_uninstall() {
   global $DB;

   $tables = array('glpi_plugin_reports_profiles',
                   'glpi_plugin_reports_doublons_backlist',
                   'glpi_plugin_reports_doublons_backlists');

   foreach ($tables as $table) {
      $query = "DROP TABLE IF EXISTS `$table`";
      $DB->query($query) or die($DB->error());
   }

   return true;
}


?>