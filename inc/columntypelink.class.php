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
class PluginReportsColumnTypeLink extends PluginReportsColumn {

   private $obj          = NULL;
   private $with_comment = 0;
   private $nametype     = '';


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
          && (is_null($this->obj) || $this->obj->getType()!=$row[$this->nametype])) {

         if (!($this->obj = getItemForItemtype($row[$this->nametype]))) {
            $this->obj = NULL;
         }
      }

      if (!$this->obj || !$this->obj->getFromDB($row[$this->name])) {
         return 'ID #'.$row[$this->name];
      }

      if ($output_type==HTML_OUTPUT) {
         return $this->obj->getLink($this->with_comment);
      }

      return $this->obj->getNameID();
   }
}
?>
