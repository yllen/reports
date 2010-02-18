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
* Class to create, execute and display a new record
* The class stores a collection of criterias and
* manage :
* 	- criterias selection form
*	- query executing using with criterias restriction
* 	- result display & export (HTML, PDF, CSV, SLK)
**/
class PluginReportsAutoReport {

   private $criterias = array ();
   private $columns = array ();
   private $group_by = array ();
   private $columns_mapping = array ();
   private $sql = "";
   private $name = "";
   private $subname = "";
   private $cpt = 0;
   private $title = '';

   function __construct($name='', $title='') {

      if (empty($name)) {
         $this->name = basename($_SERVER['SCRIPT_NAME'],'.php');
      } else {
         $this->name = $name;
      }
      includeLocales($this->name);
      $this->setTitle($title);
   }


   //-------------- Getters ------------------//
   function getCriterias() {
      return $this->criterias;
   }


   //-------------- Setters ------------------//
   /**
   * Set column mappings : when a column's value cannot be
   * displays as it is, but needs to be replaced by another one
   * @param columns_mappings the columns new values
	**/
   function setColumnsMappings($columns_mappings) {
      $this->columns_mapping = $columns_mappings;
   }


   /**
   * Defined "GROUP BY" columns
   * for output improvment
   * first line displayed in bold
   * next lines not displayed
   *
   * $colmuns : column name or array of column names
   *
	 */
   function setGroupBy($columns) {

      if (is_array($columns)) {
         $this->group_by = $columns;
      } else {
         $this->group_by = array($columns);
      }
   }


   /**
   * Set columns names (label to be displayed)
   * @param columns an array which contains
   * sql column name => GLPI's locale
	**/
   function setColumnsNames($columns) {
      $this->columns = $columns;
   }


   /**
   * Set sql request to be executed
   * @param sql the sql request as a string
	**/
   function setSqlRequest($sql) {
      $this->sql = $sql;
   }


   /**
   * Set report's name
   * @param name the name of the report
	**/
   function setName($name) {
      $this->name = $name;
   }

   /**
   * Set report's Title
   * @param $title the title of the report
   **/
   function setTitle($title) {
      global $LANG;

      if ($title) {
         $this->title = $title;

      } else {
         $this->title = (isset ($LANG['plugin_reports'][$this->name][1])
                           ? $LANG['plugin_reports'][$this->name][1]
                           : $LANG['plugin_reports']['config'][10]);
      }
   }


   /**
   * Set the report's subname
   * @param subname the report's subname to display
	**/
   function setSubName($subname) {
      $this->subname = $subname;
   }


   /**
   * Generate automatically the report's subname
	**/
   function setSubNameAuto() {

      $subname = "";
      $prefix = "";
      //Get all criteria's subnames and add it to the report's subname
      foreach ($this->criterias as $criteria) {
         if ($name = $criteria->getSubName()) {
            $subname .= $prefix.$name;
            $prefix = " - ";
         }
      }

      $this->subname = $subname;
   }


   //------------- Other -------------//
   /**
   * Indicates if the criteria's form is validated or not
   * @return true if form is validated
	**/
   function criteriasValidated() {
      return isset ($_POST['find']);
   }


   /**
   * Execute the report
	**/
   function execute() {
      global $DB, $LANG, $CFG_GLPI;

      if (isset ($_POST['list_limit'])) {
         $_SESSION['glpilist_limit'] = $_POST['list_limit'];
         unset ($_POST['list_limit']);
      }

      $limit = $_SESSION['glpilist_limit'];

      if (isset ($_POST["display_type"])) {
         $output_type = $_POST["display_type"];
         if ($output_type < 0) {
            $output_type = - $output_type;
            $limit = 0;
         }
      } else {
         $output_type = HTML_OUTPUT;
      }

      $title = $this->title;
      if ($this->subname) {
         $title .= " - $this->subname";
      }

      $res = $DB->query($this->sql);
      $nbtot = ($res ? $DB->numrows($res) : 0);
      if ($limit) {
         $start = (isset ($_GET["start"]) ? $_GET["start"] : 0);
         if ($start >= $nbtot) {
            $start = 0;
         }
         if ($start > 0 || $start + $limit < $nbtot) {
            $res = $DB->query($this->sql . " LIMIT $start,$limit");
         }
      } else {
         $start = 0;
      }

      if ($nbtot == 0) {
         commonHeader($title, $_SERVER['PHP_SELF'], "utils", "report");
         echo "<div class='center'><font class='red b'>".$LANG['search'][15]."</font></div>";
         commonFooter();
      } else if ($output_type == PDF_OUTPUT_PORTRAIT || $output_type == PDF_OUTPUT_LANDSCAPE) {
         include (GLPI_ROOT . "/lib/ezpdf/class.ezpdf.php");
      } else if ($output_type == HTML_OUTPUT) {
         commonHeader($title, $_SERVER['PHP_SELF'], "utils", "report");

         echo "<div class='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th>$title</th></th></tr>\n";
         echo "<tr class='tab_bg_2 center'><td class='center'>";
         echo "<form method='POST' action='" .$_SERVER["PHP_SELF"] . "?start=$start'>\n";

         $param = "";
         foreach ($_POST as $key => $val) {
            if (is_array($val)) {
               foreach ($val as $k => $v) {
                  echo "<input type='hidden' name='".$key."[$k]' value='$v' >";
                  if (!empty ($param)) {
                     $param .= "&";
                  }
                  $param .= $key."[".$k."]=".urlencode($v);
               }
            } else {
               echo "<input type='hidden' name='$key' value='$val' >";
               if (!empty ($param)) {
                  $param .= "&";
               }
               $param .= "$key=" . urlencode($val);
            }
         }
         displayOutputFormat();
         echo "</form></td></tr>";
         echo "</table></div>";

         printPager($start, $nbtot, $_SERVER['PHP_SELF'], $param);
      }

      plugin_reports_checkRight($this->name, "r");

      if ($res && $nbtot >0) {
         $nbcols = $DB->num_fields($res);
         $nbrows = $DB->numrows($res);

         echo Search::showHeader($output_type, $nbrows, $nbcols, true);

         echo Search::showNewLine($output_type);
         $num = 1;

         // fill $sqlcols with default sql query fields so we can validate $columns
         $sqlcols = array();
         for ($i = 0 ; $i < $nbcols ; $i++) {
            $colname = $DB->field_name($res, $i);
            $sqlcols[] = $colname;
         }
         // if $columns is not empty, display $columns
         if (!empty($this->columns)) {
            foreach ($this->columns as $colname => $coltitle) {
               // display only $columns that are valid
               if (in_array($colname, $sqlcols)) {
                  echo Search::showHeaderItem($output_type, $coltitle, $num);
                  $colsname[] = $colname;
               }
            }
         } else { // else display default columns from SQL query
            foreach ($sqlcols as $colname => $coltitle) {
               echo Search::showHeaderItem($output_type, $coltitle, $num);
            }
            $colsname = $sqlcols;
            unset($sqlcols);
         }

         echo Search::showEndLine($output_type);

         $prev = "";
         for ($row_num = 2 ; $row = $DB->fetch_assoc($res) ; $row_num++) {
            $crt = "";
            foreach ($this->group_by as $colname) {
               if (isset ($row[$colname])) {
                  $crt .= $row[$colname] . "####";
               }
            }

            echo Search::showNewLine($output_type);
            $num = 1;

            foreach ($colsname as $colname) {

               //If value needs to be modified on the fly
               if (isset ($this->columns_mapping[$colname])
                   && isset ($this->columns_mapping[$colname][$row[$colname]])) {

                  $new_value = $this->columns_mapping[$colname][$row[$colname]];
                  $row[$colname] = $new_value;
               }

               if (!in_array($colname, $this->group_by)) {
                  echo Search::showItem($output_type, $row[$colname], $num, $row_num);
               } else if ($crt == $prev) {
                  echo Search::showItem($output_type,
                                         ($output_type == CSV_OUTPUT ? $row[$colname] : ""),
                                         $num, $row_num);
               } else if ($output_type == HTML_OUTPUT) {
                  echo Search::showItem($output_type, "<strong>" . $row[$colname] . "</strong>",
                                         $num, $row_num);
               } else {
                  echo Search::showItem($output_type, $row[$colname], $num, $row_num);
               }
            } // Each column
            echo Search::showEndLine($output_type);
            $prev = $crt;
         } // Each row
      }
      echo Search::showFooter($output_type, $title);

      if (!isset ($_POST["display_type"]) || $_POST["display_type"] == HTML_OUTPUT) {
         commonFooter();
      }
   }


   /**
    * Display a common search criterias form
    * @param target the form's target
    * @param params the search criterias
    */
   function displayCriteriasForm($target) {
      global $LANG;

      //Get criteria's values
      $this->manageCriteriasValues();
      //Display commonHeader is output is HTML
      if (!isset ($_POST["display_type"]) || $_POST["display_type"] == HTML_OUTPUT) {
         if (isStat($this->name)) {
            commonHeader($LANG['plugin_reports'][$this->name][1], $_SERVER['PHP_SELF'],
                         "maintain", "stat");
         } else {
            commonHeader($LANG['plugin_reports'][$this->name][1], $_SERVER['PHP_SELF'],
                         "utils", "report");
         }
      } else {
         return;
      }

      plugin_reports_checkRight($this->name, "r");

      //Display form only if there're criterias
      if (!empty ($this->criterias)) {
         echo "<div class='center'>";
         echo "<form method='post' name='form' action='$target'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>" . $LANG['plugin_reports']['reports'][1];

         //If form is validated, then display the bookmark button
         if ($this->criteriasValidated()) {
            //Add parameters to uri to be saved as bookmarks
            $_SERVER["REQUEST_URI"] = $this->buildBookmarkUrl();
            Bookmark::showSaveButton(BOOKMARK_SEARCH,
                                     (isStat($this->name)?'PluginReportsStat':'PluginReportsReport'));
         }
         echo "</th></tr>\n";

         //Display each criteria's html selection item
         foreach ($this->criterias as $criteria) {
            $criteria->displayCriteria();
         }

         $this->closeColumn();

         echo "<tr class='tab_bg_2'><td colspan='4' class='center'>";
         echo "<input type='submit' name='find' value='" . $LANG['buttons'][0] . "' class='submit'>";
         echo "</td></tr>";
         echo "</table></div></form>";
      }
   }


   function manageCriteriasValues() {

      foreach ($this->criterias as $criteria) {
         $criteria->manageCriteriaValues();
      }

      //If selectio form is validated, then stores it
      if (isset ($_GET['find']) || isset ($_POST['find'])) {
         $_POST['find'] = true;
      }
   }


   /**
    * Append date and time restriction in an sql request
    * @param fields the fields to be restricted
    * @param params the values to be used
    * @param link with previous condition
    */
   function addSqlCriteriasRestriction($link = 'AND') {

      $sql = "";
      //Get all criterias sql restriction criterias
      foreach ($this->criterias as $criteria) {
         $sql .= $criteria->getSqlCriteriasRestriction($link);
         $link = 'AND';
      }
      return $sql;
   }


   /**
   * Build the bookmark URL, which contains all the criteria's values
   * @return a string to be stored by the bookmarking system
   **/
   function buildBookmarkUrl() {

      $bookmark_criterias='?find=1';
      foreach ($this->criterias as $criteria) {
         $bookmark_criterias.= $criteria->getBookmarkUrl();
      }
      return $_SERVER["REQUEST_URI"].$bookmark_criterias;
   }


   /**
   * Add a new criteria to the report
   **/
   function addCriteria($criteria) {
      $this->criterias[] = $criteria;
   }

   /**
    * Delete a criteria
    */
   function delCriteria($name) {
      foreach ($this->criterias as $key => $crit) {
         if ($crit->getName() == $name) {
            unset($this->criterias[$key]);
         }
      }
   }

   /**
   * Add a new column in the criterias selection form
   **/
   function startColumn() {

      if ($this->cpt==0) {
         echo "<tr class='tab_bg_1'>";
      }
      echo "<td>";
      $this->cpt++;
   }


   /**
   * End a column in the criterias selection form
   **/
   function endColumn() {

      echo "</td>";
      if ($this->cpt==4) {
         echo "</tr>";
         $this->cpt=0;
      }
   }


   /**
   * Close a column in the criterias selection form
   **/
   function closeColumn() {

      if ($this->cpt>0) {
         while ($this->cpt<4) {
            echo "<td></td>";
            $this->cpt++;
         }
         $this->cpt=0;
         echo "</tr>";
      }
   }

}

?>