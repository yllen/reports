<?php
/*
 * @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

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
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
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
   switch ($item->getType()) {
      case 'Profile' :
         //Check if new reports added
         $prof->updatePluginRights();
         PluginReportsProfile::showForProfile($item);
         break;
   }
}


function plugin_reports_install() {

   // No autoload when plugin is not activated
   require 'inc/profile.class.php';

   return PluginReportsProfile::install();
   }


function plugin_reports_uninstall() {
   global $DB;

   // No autoload when plugin is not activated
   require 'inc/profile.class.php';

   return PluginReportsProfile::uninstall();
}


?>