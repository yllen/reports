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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Dropdown for softwares with license
 */
class PluginReportsSoftwareWithLicenseCriteria extends PluginReportsDropdownCriteria {


   function __construct($report, $name='softwares_id', $label='') {
      global $LANG;

      parent::__construct($report, $name, 'glpi_softwares', ($label ? $label :$LANG['help'][31]));
   }


   function displayDropdownCriteria() {
      global $DB, $LANG;

      $query = "SELECT `glpi_softwares`.`name`, `glpi_softwares`.`id`
                FROM `glpi_softwarelicenses`
                LEFT JOIN `glpi_softwares`
                     ON `glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
                WHERE `glpi_softwarelicenses`.`entities_id`
                           IN(" . $_SESSION['glpiactiveentities_string'] . ")
                GROUP BY `glpi_softwares`.`name`";
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         echo "<select name='".$this->getName()."'>";
         while ($data = $DB->fetch_array($result)) {
            echo "<option value='" . $data["id"] . "'";
            if ($data["id"] == $this->getParameterValue()) {
               echo " selected = 'selected'";
            }
            echo ">" . $data["name"];
            echo "</option>";
         }
         echo "</select>";
      } else {
         echo "<font class='red b'>".$LANG['search'][15]."</font>";
      }
   }

}
?>