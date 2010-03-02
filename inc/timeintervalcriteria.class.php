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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Criteria which allows to select a time interval
 */
class PluginReportsTimeIntervalCriteria extends PluginReportsAutoCriteria {

   function __construct($report,$sql_field='') {
      parent :: __construct($report, "time-interval",$sql_field);
   }


   public function setDefaultValues() {
      $this->setStartTime(date("Y-m-d"));
      $this->setEndTime(date("Y-m-d"));
   }


   function setStartTime($starttime) {
      $this->addParameter('starttime',$starttime);
   }


   function setEndtime($endtime) {
      $this->addParameter('endtime',$endtime);
   }


   function displayCriteria() {
      global $LANG;

      $this->getReport()->startColumn();
      echo $LANG['job'][21] . " " . $LANG['buttons'][33].'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Dropdown::showHours("starttime", $this->getParameter('starttime'));
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      echo $LANG['job'][21] . " " . $LANG['buttons'][32].'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Dropdown::showHours("endtime", $this->getParameter('endtime'));
      $this->getReport()->endColumn();
   }


   function getSqlCriteriasRestriction($link = 'AND') {

      if ($this->getParameter("starttime") < $this->getParameter("endtime")) {
         // ex  08:00:00 <= time < 18:00:00
         return " $link TIME(".$this->getSqlField().") >= '".$this->getParameter('starttime'). ":00'
                 AND TIME(" .$this->getSqlField(). ") < '" .$this->getParameter('endtime'). ":00'";
      } else {
         // ex time < 08:00:00 or 18:00:00 <= time
         return " $link (TIME(". $this->getSqlField().") >= '".$this->getParameter('starttime').":00'
                         OR TIME(".$this->getSqlField().") < '".$this->getParameter('endtime').":00')";
      }
   }


   function getSubName() {
      global $LANG;

      return " " . (isset ($LANG['plugin_reports']['subname'][$this->getName()])
                    ? $LANG['plugin_reports']['subname'][$this->getName()] : '') .
             " (" . $this->getParameter('starttime') . "," . $this->getParameter('endtime') . ")";
   }

}

?>