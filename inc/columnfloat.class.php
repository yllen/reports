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
class PluginReportsColumnFloat extends PluginReportsColumn {

   private $total;
   private $with_zero = 1;
   private $decimal   = -1;


   function __construct($name, $title, $options=array()) {

      if (!isset($options['extrafine'])) {
         $options['extrafine'] =  "class='right'";
      }

      if (!isset($options['extrabold'])) {
         $options['extrabold'] =  "class='b right'";
      }

      if (isset($options['with_zero'])) {
         $this->with_zero = $options['with_zero'];
      }

      if (isset($options['decimal'])) {
         $this->decimal = $options['decimal'];
      }

      parent::__construct($name, $title, $options);

      $this->total = 0.0;
   }


   function displayValue($output_type, $row) {

      if (isset($row[$this->name])) {
         $this->total += floatval($row[$this->name]);

         if ($row[$this->name] || $this->with_zero) {
            return formatNumber($row[$this->name], false, $this->decimal);
         }
      }
      return '';
   }


   function displayTotal($output_type) {
      return formatNumber($this->total, false, $this->decimal);;
   }
}
?>