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

// ----------------------------------------------------------------------
// Original Author of file:
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Ticket status selection criteria
**/
class PluginReportsTicketStatusCriteria extends PluginReportsArrayCriteria {

   private $choice = array();


   function __construct($report, $name='status', $label='', $option=1) {
      global $LANG;

      if (is_array($option)) {
         foreach ($option as $opt) {
            $tab[$opt] = Ticket::getStatus($opt);
         }

      } else if ($option == 1) {
         $tab = Ticket::getAllStatusArray(true);

      } else {
         $tab = Ticket::getAllStatusArray(false);
      }

      // Parent is PluginReportsArrayCriteria
      parent::__construct($report, $name, ($label ? $label : $LANG['joblist'][0]), $tab);
   }


   /**
    * Get SQL code associated with the criteria
   **/
   public function getSqlCriteriasRestriction($link = 'AND') {

      $status = $this->getParameterValue();
      switch ($status) {
         case "notold" :
            $list = "'new','plan','assign','waiting'";
            break;

         case "old" :
            $list = "'solved','closed'";
            break;

         case "process" :
            $list = "'plan','assign'";
            break;

         case "new" :
         case "assign" :
         case "plan" :
         case "waiting" :
         case "solved" :
         case "closed" :
            $list = "'$status'";
            break;

         case "all" :
         default :
            return '';
      }
      return $link . " " . $this->getSqlField() . " IN ($list) ";
   }

}
?>