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

function plugin_get_headings_reports($type,$id,$withtemplate) {
   global $LANG;

   if ($type==PROFILE_TYPE) {
      $prof = new Profile();
      if ($id>0 && $prof->getFromDB($id) && $prof->fields['interface']!='helpdesk') {
         return array(1 => $LANG['plugin_reports']['title'][1]);
      }
   }
   return false;
}


function plugin_headings_actions_reports($type) {

   switch ($type) {
      case PROFILE_TYPE :
         return array(1 => "plugin_headings_reports");
         break;
   }
   return false;
}


function plugin_headings_reports($type,$id,$withtemplate=0) {
   global $CFG_GLPI;

   $prof = new PluginReportsProfile();
   switch ($type) {
      case PROFILE_TYPE :
         //Check if new reports added
         $prof->updatePluginRights(GLPI_ROOT."/plugins/reports/report");

         if (!$prof->getFromDB($id)) {
            $prof->createaccess($id);
         }
         $prof->showForm($CFG_GLPI["root_doc"]."/plugins/reports/front/profile.form.php",$id);
         break;
   }
}


// Hook done on delete item case
function plugin_pre_item_delete_reports($input) {

   if (isset($input["_item_type_"])) {
      switch ($input["_item_type_"]) {
         case PROFILE_TYPE :
            // Manipulate data if needed 
            $ReportProfile = new ReportProfile;
            $ReportProfile->cleanProfiles($input["id"]);
            break;
      }
   }
   return $input;
}


function plugin_reports_install() {
   global $DB;

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
   $DB->query($query) or die($DB->error());

   return true;
}


function plugin_reports_uninstall() {
   $DB = new DB;

   $tables = array("glpi_plugin_reports_profiles");
   foreach ($tables as $table) {
      $query = "DROP TABLE IF EXISTS `$table`";
   }
   $DB->query($query) or die($DB->error());
}


?>