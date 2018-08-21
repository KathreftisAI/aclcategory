<?php

class PluginAclcategoryAcl extends CommonGLPI
{
     /**
     * This function is called from GLPI to allow the plugin to insert one or more item
     *  inside the left menu of a Itemtype.
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate=0)
    {
        return self::createTabEntry('Ticket Categories');
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0)
    {
        global $CFG_GLPI, $DB;
        $entityrestrict = $item->fields['entities_id'];
        $groups_id = $item->fields['id'];
        $used_ids = self::getUsedCategoryIds($entityrestrict, $groups_id)[0];
        
        $rand = mt_rand();
        $selected_cat  = self::getGroupCategory($entityrestrict, $groups_id);

         echo "<form name='groupuser_form$rand' id='groupuser_form$rand' method='post'
                action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='groups_id' value='".$item->fields['id']."'>";

         echo "<div class='firstbloc'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='6'>".__('Add a Category')."</th></tr>";
         echo "<tr class='tab_bg_2'><td class='center'>";

         ITILCategory::dropdown(['right'  => "all",
                              'entity' => $entityrestrict,
                              'used'   => $used_ids]);

         echo "</td><td class='tab_bg_2 center'>";
         echo "<input type='hidden' name'is_dynamic' value='0'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table></div>";
         Html::closeForm();











      // show search form
      // $dropdown = new ITILCategory();
      // Search::show(get_class($dropdown));


      global $DB, $CFG_GLPI;

      $group = new Group($item->fields['id']);

      $ID = $group->getID();
      if (!User::canView()
          || !$group->can($ID, READ)) {
         return false;
      }

        $entityrestrict = $item->fields['entities_id'];
        $groups_id = $item->fields['id'];
        $category_group = new ITILCategory();

      // Have right to manage members
      $canedit = Group::canUpdate();
      $rand    = mt_rand();
      $user    = new ITILCategory();
      $crit    = Session::getSavedOption(__CLASS__, 'criterion', '');
      $tree    = Session::getSavedOption(__CLASS__, 'tree', 0);
      $used    = self::getUsedCategoryIds($entityrestrict, $groups_id);
      $used_ids=$used[1];
      $used=$used[0];
      $ids     = [];

      // Retrieve member list
      $entityrestrict = self::getDataForCategory($entityrestrict);


      // Mini Search engine
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'><th colspan='2'>".User::getTypeName(Session::getPluralNumber())."</th></tr>";
      echo "</table>";
      $number = count($used);
      $start  = (isset($_GET['start']) ? intval($_GET['start']) : 0);
      if ($start >= $number) {
         $start = 0;
      }

      // Display results
      if ($number) {
         echo "<div class='spaced'>";
         // Html::printAjaxPager(sprintf(__('%1$s (%2$s)'),
         //                              User::getTypeName(Session::getPluralNumber()), __('D=Dynamic')),
         //                      $start, $number);

         //Session::initNavigateListItems('User',
                              //TRANS : %1$s is the itemtype name,
                              //        %2$s is the name of the item (used for headings of a list)
                                        // sprintf(__('%1$s = %2$s'),
                                        //         Group::getTypeName(1), $group->getName()));

         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['num_displayed'    => min($number-$start,
                                                                   $_SESSION['glpilist_limit']),
                                         'container'        => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixehov'>";

         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';

         if ($canedit) {
            $header_begin  .= "<th width='10'>";
            $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_end    .= "</th>";
         }
         $header_end .= "<th>ITIL Category</th>";
         if ($tree) {
            $header_end .= "<th>".Group::getTypeName(1)."</th>";
         }
         echo $header_begin.$header_top.$header_end;

         $tmpgrp = new Group();

         for ($i=$start, $j=0; ($i < $number) && ($j < $_SESSION['glpilist_limit']); $i++, $j++) {
            $data = $used[$i];
            $user->getFromDB($data); //$data["id"]
            //Session::addToNavigateListItems('User', $data["id"]);

            echo "\n<tr class='tab_bg_".($user->isDeleted() ? '1_2' : '1')."'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox('PluginAclcategoryGroup_Itilcategory', $used_ids[$i]); //__CLASS__, $data["linkID"]
               echo "</td>";
            }
            echo "<td>".$user->getLink();
            echo "</tr>";
         }
         echo $header_begin.$header_bottom.$header_end;
         echo "</table>";
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         Html::printAjaxPager(sprintf(__('%1$s (%2$s)'),
                                      ITILCategory::getTypeName(Session::getPluralNumber()), __('Categories')),
                              $start, $number);

         echo "</div>";
      } else {
         echo "<p class='center b'>".__('No item found')."</p>";
      }
   


        return true;
    }

    // return array of key value
    static function getGroupCategory($entityrestrict, $groups_id){
        global $CFG_GLPI, $DB;
        $query = "SELECT glpi_itilcategories.name, glpi_itilcategories.id FROM glpi_plugin_aclcategory_usergroup_itilcategory LEFT JOIN glpi_itilcategories ON glpi_plugin_aclcategory_usergroup_itilcategory.itilcategory_id=glpi_itilcategories.id
            where glpi_plugin_aclcategory_usergroup_itilcategory.groups_id=$groups_id
            ";
        $res = $DB->query($query);
        $category = array();
        while($row = $DB->fetch_assoc($res)){
            $category[]=$row;
        }
        return $category;
    }

    //return array
    static function getUsedCategoryIds($entityrestrict, $groups_id){
        global $CFG_GLPI, $DB;
        $query = "SELECT glpi_plugin_aclcategory_usergroup_itilcategory.itilcategory_id, glpi_plugin_aclcategory_usergroup_itilcategory.id FROM glpi_plugin_aclcategory_usergroup_itilcategory 
            where glpi_plugin_aclcategory_usergroup_itilcategory.groups_id=$groups_id
            ";
        $res = $DB->query($query);
        $category = array();
        $ids = array();
        while($row = $DB->fetch_assoc($res)){
            $category[]=$row['itilcategory_id'];
            $ids[]=$row['id'];
        }
        return array($category, $ids);
    }

    // to add itil category from group
    static function addCategory(){
        global $CFG_GLPI, $DB;
        $groups_id = $_POST['groups_id'];
        $itilcategories_id = $_POST['itilcategories_id'];
        $query = "INSERT INTO glpi_plugin_aclcategory_usergroup_itilcategory SET groups_id=$groups_id, itilcategory_id=$itilcategories_id";
        $res = $DB->query($query);
        Session::addMessageAfterRedirect(__('Category added to Group Successfully!!'));
        Html::redirect($CFG_GLPI["root_doc"] . "/front/group.form.php?id=".$groups_id);
    }

    /**
    * Retrieve list of member of a Group
    *
    * @since version 0.83
    *
    * @param $group              Group object
    * @param $members   Array    filled on output of member (filtered)
    * @param $ids       Array    of ids (not filtered)
    * @param $crit      String   filter (is_manager, is_userdelegate) (default '')
    * @param $tree      Boolean  true to include member of sub-group (default 0)
    *
    * @return String tab of entity for restriction
   **/
   static function getDataForCategory($entityrestrict) {
      global $DB;

      // All itil categories
      $query = "SELECT DISTINCT `glpi_itilcategories`.`id`,
                       `glpi_plugin_aclcategory_usergroup_itilcategory`.`itilcategory_id` AS linkID,
                       `glpi_plugin_aclcategory_usergroup_itilcategory`.`groups_id`
                FROM `glpi_plugin_aclcategory_usergroup_itilcategory`
                INNER JOIN `glpi_itilcategories`
                        ON (`glpi_itilcategories`.`id` = `glpi_plugin_aclcategory_usergroup_itilcategory`.`itilcategory_id`)
                WHERE `glpi_plugin_aclcategory_usergroup_itilcategory`.`groups_id`
                ORDER BY `glpi_itilcategories`.`name`";

      $result = $DB->query($query);

      if ($DB->numrows($result) > 0) {
         while ($data=$DB->fetch_assoc($result)) {
            // Add to display list, according to criterion
            if (empty($crit) || $data[$crit]) {
               $members[] = $data;
            }
            // Add to member list (member of sub-group are not member)
            // if ($data['groups_id'] == $group->getID()) {
            //    $ids[]  = $data['id'];
            // }
            $ids[]  = $data['id'];
         }
      }

      return $entityrestrict;
   }
}