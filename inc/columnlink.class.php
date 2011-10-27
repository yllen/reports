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
class PluginReportsColumnLink extends PluginReportsColumn {

   private $obj           = NULL;
   private $with_comment  = 0;
   private $with_navigate = 0;


   function __construct($name, $title, $itemtype, $options=array()) {
      global $LANG;

      parent::__construct($name, $title, $options);

      $this->obj = getItemForItemtype($itemtype);

      if (isset($options['with_comment'])) {
         $this->with_comment = $options['with_comment'];
      }

      if (isset($options['with_navigate'])) {
         $this->with_navigate = $options['with_navigate'];
         Session::initNavigateListItems($this->obj->getType(), $LANG['Menu'][6]);
      }
   }


   function displayValue($output_type, $row) {

      if (!isset($row[$this->name]) || !$row[$this->name]) {
         return '';
      }

      if (!$this->obj || !$this->obj->getFromDB($row[$this->name])) {
         return $row[$this->name];
      }

      if ($this->with_navigate) {
         Session::addToNavigateListItems($this->obj->getType(), $row[$this->name]);
      }

      if ($output_type==HTML_OUTPUT) {
         return $this->obj->getLink($this->with_comment);
      }

      return $this->obj->getNameID();
   }
}
?>
