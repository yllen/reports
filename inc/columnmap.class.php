<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 reports - Additional reports plugin for GLPI
 Copyright (C) 2003-2011 by the reports Development Team.

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
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumnMap extends PluginReportsColumn {

   private $map;


   function __construct($name, $title, $map=array(), $options=array()) {

      parent::__construct($name, $title, $options);

      if (count($map)) {
         $this->map = $map;
      } else {
         switch ($name) {
            case 'status':
               $this->map = Ticket::getAllStatusArray();
               break;

            case 'impact':
               $this->map = getImpactLabelsArray();
               break;

            case 'urgency':
               $this->map = getUrgencyLabelsArray();
               break;

            case 'priority':
               $this->map = getPriorityLabelsArray();
               break;

            default:
               $this->map = array();
         }
      }
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])){
         if (isset($this->map[$row[$this->name]])) {
            return $this->map[$row[$this->name]];
         }
         return $row[$this->name];
      }
      return '';
   }
}
?>