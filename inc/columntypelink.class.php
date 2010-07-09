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
class PluginReportsColumnTypeLink extends PluginReportsColumn {

   private $obj = NULL;
   private $with_comment = 0;
   private $nametype = '';

   function __construct($nameid, $title, $nametype, $options=array()) {

      parent::__construct($nameid, $title, $options);
      $this->nametype = $nametype;

      if (isset($options['with_comment'])) {
         $this->with_comment = $options['with_comment'];
      }
   }

   function displayValue($output_type, $row) {

      if (!isset($row[$this->name]) || !$row[$this->name]) {
         return '';
      }
      if (isset($row[$this->nametype])
          && $row[$this->nametype]
          && (is_null($this->obj) || get_class($this->obj)!=$row[$this->nametype])) {
         $this->obj = new $row[$this->nametype];
      }
      if (!$this->obj || !$this->obj->getFromDB($row[$this->name])) {
         return $row[$this->name];
      }
      if ($output_type==HTML_OUTPUT) {
         return $this->obj->getLink($this->with_comment);
      }
      return $this->obj->getNameID();
   }
}
?>
