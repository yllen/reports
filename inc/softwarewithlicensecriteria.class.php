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

/**
 * Dropdown for softwares with license
 */
class PluginReportsSoftwareWithLicenseCriteria extends PluginReportsDropdownCriteria {


   /**
    * @param $report
    * @param $name      (default 'softwares_id')
    * @param $label     (default '')
   **/
   function __construct($report, $name='softwares_id', $label='') {

      parent::__construct($report, $name, 'glpi_softwares',
                          ($label ? $label : _n('Software', 'Software', 1)));
   }


   function displayDropdownCriteria() {
      global $DB;

      $query = "SELECT `glpi_softwares`.`name`, `glpi_softwares`.`id`
                FROM `glpi_softwarelicenses`
                LEFT JOIN `glpi_softwares`
                     ON `glpi_softwarelicenses`.`softwares_id` = `glpi_softwares`.`id`
                LEFT JOIN `glpi_entities`
                     ON (`glpi_softwares`.`entities_id` = `glpi_entities`.`id`)
                WHERE `glpi_softwarelicenses`.`entities_id`
                           IN (" . $_SESSION['glpiactiveentities_string'] . ")
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
         echo "<span class='red b center'>".__('No item found')."</span>";
      }
   }

}
?>