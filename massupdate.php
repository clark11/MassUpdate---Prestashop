<?php

/**
 * Original author: David T Baker
 * Web: http://dtbaker.com.au
 * Email: dtbaker@gmail.com
 * Created: 2009-02-26
 * 
 * Updated by author: Bob Claassen
 * Web: http://www.bnc-automatisering.nl
 * Email: bc@bnc-automatisering.nl
 * Update date: 2011-12-28
 *
 * 2013 Updated by: Claudio Casuccio
 * Web: http://claudiocasuccio.it  
 * http://clkweb.it
 * 
 * File: massupdate.php
 * Provides:
 *  Ability to perform mass updates on products..
 * 
 */
class MassUpdate extends Module
{
	/* @var boolean error */
	protected $error = false;
	private $_tabClass = 'AdminMassUpdate';
	
	
	function __construct()
	{
	 	$this->name = 'massupdate';
	 	$this->tab = 'administration';
	 	$this->version = '1.6';

	 	parent::__construct();

	 	/* The parent construct is required for translations */
		$this->page = basename(__FILE__, '.php');
        $this->displayName = $this->l('Mass Update');
        $this->description = $this->l('Perform mass updates on all your products (price, weight, and features)');
		$this->confirmUninstall = $this->l('Aww... are you sure you want to delete me?');
	}
	
	function install($try_again=true)
	{
	 	//if (!$this->addMassUpdateHook() || !parent::install() || !$this->registerHook('shippingCalculate') || !$this->registerHook('shippingCalculateDays')){
	 	if (!parent::install() || 
			!$this->installModuleTab('AdminMassUpdate', 'Mass Update', 1)){
 			return false;
	 	}
	 	
	 	return (Configuration::updateValue('PS_MASSUPDATE_TITLE', array('1' => 'Mass Update')) );
	}
	
	function uninstall()
	{
	 	if (!parent::uninstall() ||
			!$this->uninstallModuleTab('AdminMassUpdate')
			)
	 		return false;
	 	
	 		
	 	return (Configuration::deleteByName('PS_MASSUPDATE_TITLE'));
	}
	
	private function installModuleTab($tabClass, $tabName, $idTabParent)
	{
		@copy(_PS_MODULE_DIR_.$this->name.'/logo.gif', _PS_IMG_DIR_.'t/'.$tabClass.'.gif');
		$tab = new Tab();
		foreach (Language::getLanguages() as $language)
			$tab->name[$language['id_lang']] = $tabName; 
		$tab->class_name = $tabClass;
		$tab->module = $this->name;
		$tab->id_parent = $idTabParent;
		if(!$tab->save())
		return false;
	  return true;
	} 
	
	private function uninstallModuleTab($tabClass)
	{
		$idTab = Tab::getIdFromClassName($tabClass);
		if($idTab != 0){
			$tab = new Tab($idTab);
			$tab->delete();
			return true;
		}
		return false;
	} 
	
	function getContent()
    {
		global $cookie;
		$tab = $this->_tabClass;
		$token = Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)($cookie->id_employee));
		Tools::redirectAdmin('index.php?tab=' . $tab . '&token=' . $token);
    }
	
	
}
?>
