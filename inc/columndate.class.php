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
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumnDate extends PluginReportsColumn {

   function __construct($name, $title, $options=array()) {

      if (!isset($options['extrafine'])) {
         $options['extrafine'] =  "class='center'";
      }
      if (!isset($options['extrabold'])) {
         $options['extrabold'] =  "class='b center'";
      }

      parent::__construct($name, $title, $options);
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name]) && $row[$this->name]) {
         return Html::convDate($row[$this->name]);
      }
      return '';
   }
}
?>
