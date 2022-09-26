<?php
/**
 -------------------------------------------------------------------------
  LICENSE

 This file is part of Reports plugin for GLPI.

 Reports is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Reports is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Reports. If not, see <http://www.gnu.org/licenses/>.

 @package   reports
 @authors    Nelly Mahu-Lasson, Remi Collet, Alexandre Delaunay
 @copyright Copyright (c) 2009-2022 Reports plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/reports
 @link      http://www.glpi-project.org/
 @since     2009
 --------------------------------------------------------------------------
 */

/**
 * Ticket status selection criteria
**/
class PluginReportsTicketStatusCriteria extends PluginReportsArrayCriteria {

   private $choice = [];


   /**
    * @param $report
    * @param $name      (default 'status')
    * @param $label     (default '')
    * @param $option    (default 1)
   **/
   function __construct($report, $name='status', $label='', $option=1) {

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
      parent::__construct($report, $name, ($label ? $label : _n('Status', 'Statuses', 1)), $tab);
   }


   /**
    * Get SQL code associated with the criteria
    *
    * @see plugins/reports/inc/PluginReportsArrayCriteria::getSqlCriteriasRestriction()
   **/
   public function getSqlCriteriasRestriction($link='AND') {

      $status = $this->getParameterValue();

      switch ($status) {
         case "notold" :
            $list = Ticket::getAllStatusArray();
            $check = array_merge(Ticket::getSolvedStatusArray(),
                                 Ticket::getClosedStatusArray());
            foreach ($check as $status) {
               if (isset($list[$status])) {
                  unset($list[$status]);
               }
            }
            $list = implode("','", array_keys($list));
            break;

         case "old" :
            $list = implode("','", array_merge(Ticket::getSolvedStatusArray(),
                                               Ticket::getClosedStatusArray()));
            break;

         case "process" :
            $list = implode("','", Ticket::getProcessStatusArray());
            break;

         case 'notclosed' :
            $list = Ticket::getAllStatusArray();
            foreach (Ticket::getClosedStatusArray() as $status) {
               if (isset($list[$status])) {
                  unset($list[$status]);
               }
            }
            $list = implode("','", array_keys($list));
            break;

         case Ticket::INCOMING :
         case Ticket::ASSIGNED :
         case Ticket::PLANNED :
         case Ticket::WAITING :
         case Ticket::SOLVED :
         case Ticket::CLOSED :
            $list = $status;
            break;

         case "all" :
         default :
            return '';
      }
      return $link . " " . $this->getSqlField() . " IN ('".$list."') ";
   }

}
