<?php
/**
 * Copyright (C) 2005, 2006, 2007, 2008  Brice Burgess <bhb@iceburg.net>
 * 
 * This file is part of poMMo (http://www.pommo.org)
 * 
 * poMMo is free software; you can redistribute it and/or modify 
 * it under the terms of the GNU General Public License as published 
 * by the Free Software Foundation; either version 2, or any later version.
 * 
 * poMMo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See
 * the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with program; see the file docs/LICENSE. If not, write to the
 * Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**********************************
	INITIALIZATION METHODS
*********************************/
require ('../bootstrap.php');
Pommo::init();
$logger = & Pommo::$_logger;
$dbo = & Pommo::$_dbo;


/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
require_once(Pommo::$_baseDir.'classes/Pommo_Template.php');
$smarty = new Pommo_Template();
$smarty->prepareForForm();

SmartyValidate :: connect($smarty);

if (!SmartyValidate :: is_registered_form('users') || empty ($_POST)) {
	// ___ USER HAS NOT SENT FORM ___
	SmartyValidate::register_form('users', true);

	// register custom criteria
	SmartyValidate :: register_validator('admin_username', 'admin_username', 'notEmpty', false, false, 'trim', 'users');
	SmartyValidate :: register_validator('admin_password2', 'admin_password:admin_password2', 'isEqual', TRUE, false, false, 'users');
	SmartyValidate :: register_validator('admin_email', 'admin_email', 'isEmail', false, false, false, 'users');
    
	$vMsg = array();
	$vMsg['admin_username'] = Pommo::_T('Cannot be empty.');
	$vMsg['admin_email'] = Pommo::_T('Invalid email address');
	$vMsg['admin_password2'] = Pommo::_T('Passwords must match.');
	$smarty->assign('vMsg', $vMsg);

	// populate _POST with info from database (fills in form values...)
	$dbVals = Pommo_Api::configGet(array (
		'admin_username',
	));
	$dbVals['admin_email'] = Pommo::$_config['admin_email'];
	$smarty->assign($dbVals);
}
else
{
	// ___ USER HAS SENT FORM ___
	
	/**********************************
		JSON OUTPUT INITIALIZATION
	 *********************************/
	require_once(Pommo::$_baseDir.'classes/Pommo_Json.php');
	$json = new Pommo_Json();

	if (isset($_POST['admin_email']))
	{
		Pommo_Api::configUpdate($_POST);
		
		Pommo::reloadConfig();
		
		$json->success(Pommo::_T('Configuration Updated.'));
	}
	else {
		// __ FORM NOT VALID
		
		$json->add('fieldErrors',$smarty->getInvalidFields('users'));
		$json->fail(Pommo::_T('Please review and correct errors with your submission.'));
	}
	
}
$smarty->assign($_POST);
$smarty->display('admin/setup/config/users.tpl');
Pommo::kill();
