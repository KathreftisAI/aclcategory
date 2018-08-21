<?php

include('../../../inc/includes.php');

if(isset($_POST['add']) && !empty($_POST['groups_id']) && !empty($_POST['itilcategories_id'])){
	PluginAclcategoryAcl::addCategory();
} else{
	echo "Forbidden!!";
}
