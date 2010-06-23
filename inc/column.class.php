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

/**
 * class PluginReportsColumn to manage output
 */
class PluginReportsColumn {

   public    $name;
   private   $title;
   private   $extras;
   protected $withtotal;

   function __construct($name, $title, $options=array()) {

      $this->name      = $name;
      $this->title     = $title;

      // Extras class for each cell
      $this->extras    = (isset($options['extras']) ? $options['extras'] : '');

      // Extras class for each total cell
      $this->totextras = (isset($options['totextras']) ? $options['totextras'] : '');

      // Enable total for this column (if handle bu subtype)
      $this->withtotal = (isset($options['withtotal']) ? $options['withtotal'] : false);
   }

   function showTitle($output_type, &$num) {
       echo Search::showHeaderItem($output_type, $this->title, $num);
   }

   function showValue($output_type, $row, &$num, $row_num, $extras=false) {
      echo Search::showItem($output_type, $this->displayValue($output_type, $row), $num, $row_num,
                            ($extras ? $extras : $this->extras));
   }

   function showTotal($output_type, &$num, $row_num) {
      echo Search::showItem($output_type,
                            ($this->withtotal ? $this->displayTotal($output_type) : ''),
                            $num, $row_num, $this->totextras);
   }

   function displayValue($output_type, $row) {
      if (isset($row[$this->name])) {
         return $row[$this->name];
      }
      return '';
   }

   function displayTotal($output_type) {
      return '';
   }
}
?>
