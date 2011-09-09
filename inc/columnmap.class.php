<?php
/*
 * @version $Id$
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