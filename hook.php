<?php

/**
 * Called when user click on Install - Needed
 */
function plugin_aclcategory_install() {
	global $DB;
	$DB->query("
		CREATE TABLE IF NOT EXISTS `glpi_plugin_aclcategory_usergroup_itilcategory` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `groups_id` int(11) NOT NULL,
		  `itilcategory_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	");
	return true;
}
 
/**
 * Called when user click on Uninstall - Needed
 */
function plugin_aclcategory_uninstall() { return true; }

function aclcategory_item_can($param){
  global $DB, $CFG_GLPI;
  $users_id = $_SESSION['glpiID'];
  
  // Get User Group
  $group_iterator = $DB->request([
                 'SELECT' => ['groups_id'],
                 'FROM'   => 'glpi_groups_users',
                 'WHERE'  => ['users_id' => $users_id]
              ]);
  while ($group = $group_iterator->next()) {
     $group_array[] = $group['groups_id'];
  }

  // Get Categories of Groups
  $category_array = array();
  if (count($group_array)>0) {
     $query = "SELECT itilcategory_id FROM glpi_plugin_aclcategory_usergroup_itilcategory WHERE groups_id in (".implode(",", $group_array).")";
     $results = $DB->query($query);
     if ($DB->numrows($results)) {
        while ($data = $DB->fetch_assoc($results)) {
           $category_array[] = $data['itilcategory_id'];
        }
     }
  }

  if($param->fields['itilcategories_id']!=0 && !in_array($param->fields['itilcategories_id'], $category_array)){
    $param->right=0;
  }

}

function aclcategory_add_where($param=array())
{
  global $DB, $CFG_GLPI;
  $users_id = $_SESSION['glpiID'];
  $group_array = [];
  
  // Get User Group
  $group_iterator = $DB->request([
                 'SELECT' => ['groups_id'],
                 'FROM'   => 'glpi_groups_users',
                 'WHERE'  => ['users_id' => $users_id]
              ]);
  while ($group = $group_iterator->next()) {
     $group_array[] = $group['groups_id'];
  }

  // Get Categories of Groups
  $category_array = array();
  if (count($group_array)>0) {
     $query = "SELECT itilcategory_id FROM glpi_plugin_aclcategory_usergroup_itilcategory WHERE groups_id in (".implode(",", $group_array).")";
     $results = $DB->query($query);
     if ($DB->numrows($results)) {
        while ($data = $DB->fetch_assoc($results)) {
           $category_array[] = $data['itilcategory_id'];
        }
     }
  }

  // Check Item Type
//  print_r($param[0]);
  if (in_array($param[0], array('Change', 'Ticket', 'Problem', 'Incident', 'Request')) && count($category_array)>0) {
     // Add User Category Criteria
     $LINK = " AND ";
     if (empty($param[1])) {
        $LINK  = " ";
        $first = false;
     }
     switch ($param[0]) {
       case 'Change':
         $COMMONWHERE = array($param[0], " `glpi_changes`.`itilcategories_id` in (".implode(",", $category_array).") ");
         break;
       case 'Ticket':
       case 'Incident':
       case 'Request':
         $COMMONWHERE = array($param[0], " `glpi_tickets`.`itilcategories_id` in (".implode(",", $category_array).") ");
         break;
       case 'Problem':
         $COMMONWHERE = array($param[0], " `glpi_problems`.`itilcategories_id` in (".implode(",", $category_array).") ");
         break;
       
       default:
         # code...
         break;
     }
     return $COMMONWHERE;
  }
  return['',''];
}


