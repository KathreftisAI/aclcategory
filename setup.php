<?php

/**
 * Get the name and the version of the plugin - Needed
 */
function plugin_version_aclcategory() {
   return array('name'           => "ACL Category",
                'version'        => '1.0.0',
                'author'         => '<a href="http://felicityplatform.com">Unotech Software</a>',
                'license'        => 'GPLv2+',
                'homepage'       => 'http://felicityplatform.com/',
                'minGlpiVersion' => '9.2');
}

/**
 *  Check if the config is ok - Needed
 */
function plugin_aclcategory_check_config() {
    return true;
}
 
/**
 * Check if the prerequisites of the plugin are satisfied - Needed
 */
function plugin_aclcategory_check_prerequisites() {
    // Check that the GLPI version is compatible
    if (version_compare(GLPI_VERSION, '9.2.0', 'lt')) {
        echo "This plugin Requires GLPI >= 9.2";
        return false;
    } 
    return true;
}

/**
 * Init the hooks of the plugins -Needed
**/
function plugin_init_aclcategory() 
{
    global $PLUGIN_HOOKS;
 
    $PLUGIN_HOOKS['csrf_compliant']['aclcategory'] = true;
    $PLUGIN_HOOKS['add_default_where']['aclcategory'] = 'aclcategory_add_where';
    
    $PLUGIN_HOOKS['item_can']['aclcategory'] = ['Ticket'=>'aclcategory_item_can'];
    $PLUGIN_HOOKS['item_can']['aclcategory'] = ['Change'=>'aclcategory_item_can'];
    $PLUGIN_HOOKS['item_can']['aclcategory'] = ['Problem'=>'aclcategory_item_can'];
 
    Plugin::registerClass('PluginAclcategoryAcl', array('addtabon' => array('Group')));
}
