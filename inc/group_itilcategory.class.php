<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * PluginAclcategoryGroup_Itilcategory Class
 *
 *  Relation between Group and Itilcategory
**/
class PluginAclcategoryGroup_Itilcategory extends CommonDBRelation{
	// From CommonDBRelation
   static $itemtype_1                 = 'ITILCategory';
   static $items_id_1                 = 'itilcategory_id';

   static $itemtype_2                 = 'Group';
   static $items_id_2                 = 'groups_id';
   
   public function __construct() {
       parent::__construct();
       self::forceTable('glpi_plugin_aclcategory_usergroup_itilcategory');
   }

   /**
    * @param $user_ID
    * @param $only_dynamic (false by default
   **/
   static function deleteItilCategory($groups_id, $only_dynamic = false) {
      global $DB;
      $crit['groups_id'] = $groups_id;
      // if ($only_dynamic) {
      //    $crit['is_dynamic'] = '1';
      // }
      $obj = new self();
      $obj->deleteByCriteria($crit);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBRelation::getRelationInputForProcessingOfMassiveActions()
   **/
   static function getRelationInputForProcessingOfMassiveActions($action, CommonDBTM $item, array $ids, array $input) {
      return [];
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBRelation::getRelationMassiveActionsSpecificities()
   **/
   static function getRelationMassiveActionsSpecificities() {
      global $CFG_GLPI;
      $specificities                           = parent::getRelationMassiveActionsSpecificities();

      $specificities['select_items_options_1'] = ['right'     => 'all'];
      $specificities['select_items_options_2'] = ['condition' => ''];

      $specificities['button_labels']['add_supervisor'] = $specificities['button_labels']['add'];
      $specificities['button_labels']['add_delegatee']  = $specificities['button_labels']['add'];

      $specificities['update_if_different'] = true;

      return $specificities;
   }
}
