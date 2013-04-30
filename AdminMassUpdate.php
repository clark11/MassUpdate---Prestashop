<?php

/**
 * MassUpdate for PrestaShopª 1.5.3.1 
 * #CODE REFACTORING 2013 BY CLKWEB.IT - CLAUDIO CASUCCIO DEVELOPER
 * 
 * Original module author: David T Baker 
 * Tab author: Bob Claassen
 * Web: http://www.bnc-automatisering.nl
 * Email: bc@bnc-automatisering.nl
 * Created: 2011-12-28 -----

 * File: AdminMassUpdate.php
 * Provides:
 *  Tab for Prestashop Mass update module.
 * 
 * 
 */


include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');
//require_once (_PS_MODULE_DIR_ . 'massupdate/massupdate.php');
class AdminMassUpdate extends AdminTab
{
	private $_tabClass = 'AdminMassUpdate';
	private $_module = 'massupdate';
        private $_modulePath = '';
	private $_html = '';
	private $_id_lang;
	private $_defaultLanguage;
	private $_iso;

	public function __construct() {
	$this->_modulePath =  _PS_MODULE_DIR_ . $this->_module . DIRECTORY_SEPARATOR;
	$this->_setLanguage();
	parent::__construct();
    }
	
	private function _setLanguage(){
		global $cookie, $_LANGADM;
		$langFile = _PS_MODULE_DIR_.$this->_module.'/'.Language::getIsoById(intval($cookie->id_lang)).'.php';
		if(file_exists($langFile))
		{
			require_once $langFile;
			foreach($_MODULE as $key=>$value)
				if(substr(strip_tags($key), 0, 5) == 'Admin')
			$_LANGADM[str_replace('_', '', strip_tags($key))] = $value;
		}
		$this->_id_lang = $cookie->id_lang;
		$this->_defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
		$this->iso = Language::getIsoById(intval($cookie->id_lang));
	}
	
    public function display() {

		if ($this->_isAdmin() || (bool) Configuration::get('MENU_ALLOW_OPTIONS')) {
        	$this->_displayUpdateList();
		}
        echo $this->_html;
    }
	
	private function _displayConfirmation($string) {
        $this->_html .= '<div class="conf confirm"><img src="'._PS_IMG_.'admin/ok.gif" alt="" title="" /> ' . $string . '</div>';
    }
	
	private function _displayError($string) {
        $this->_html .= '<div class="module_error alert error"><img src="'._PS_IMG_.'admin/warning.gif" alt="" title="" /> ' . $string . '</div>';
		$this->error = true;
    }
	
	private function _displayUpdateList()
    {
    	global $cookie; 
    	$tab = $this->_tabClass;
    	$token = Tools::getAdminToken($tab.(int)(Tab::getIdFromClassName($tab)).(int)($cookie->id_employee));
     	$this->_html = '<h2>'.$this->l('Product Update').'</h2>';


     	/* Update the settings */
     	if (isset($_POST['mu_doupdate']))
     	{
     	 	if (!$this->_updateProducts())
     	 		$this->_html .= $this->_displayError($this->l('An error occurred during product updating, please try saving again.'));
     	 	else
     	 		$this->_html .= $this->_displayConfirmation($this->l('Your products have been successfully updated!'));
     	}
     	
     	$this->_html .= '
	 	<fieldset>
			<legend><img src="../modules/'.$this->_module.'/logo.gif" alt="" title="" /> '.$this->l('Mass Update Your Products').'</legend>
			<form method="post" action="'.$_SERVER['REQUEST_URI'].'">';
     	
		$sql = 'SELECT *
				FROM `'._DB_PREFIX_.'feature` f
				LEFT JOIN `'._DB_PREFIX_.'feature_lang` fl ON ( f.`id_feature` = fl.`id_feature` AND fl.`id_lang` = '.$this->_id_lang.')';
	 	$feature_results = Db::getInstance()->ExecuteS($sql);

		$limit = 100;
		$page = isset($_GET['page']) ? abs((int)$_GET['page']) : 1;	 	
		
	 	// all products
	 	$sql = 'SELECT *
				FROM `'._DB_PREFIX_.'product` p 
				LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product`) WHERE pl.`id_lang` = '.$this->_id_lang;
	 	$all_products = Db::getInstance()->ExecuteS($sql);
		// foreach($all_products as &$product)$product['db_price']=$product['price'];
	 	// $all_products = Product::getProductsProperties(1,$all_products);
	 	
		$c = count($all_products);
		$maxpage = ceil($c / $limit);
 
		if ($page <= 0)
		{
				$page = 1;
		}
		else if ($page >= $maxpage)
		{
				$page = $maxpage;
		}
 
		$contents = array_chunk($all_products, $limit);
		
		$main_fields = $this->_getConfig();
	 	
     	ob_start();
     	
     	?>
     	     	<input type="hidden" name="mu_doupdate" value="go">
     	
		<input type="submit" class="button" name="go" value="Save All My Products" />
		<?php
		//lapozï¿½ linkek
		$linklimit = 10;
 
		$linklimit2 = $linklimit / 2;
		$linkoffset = ($page > $linklimit2) ? $page - $linklimit2 : 0;
		$linkend = $linkoffset+$linklimit;
		$token = Tools::getValue('token');
		
		if ($maxpage - $linklimit2 < $page)
		{
				$linkoffset = $maxpage - $linklimit;
				if ($linkoffset < 0)
				{
						$linkoffset = 0;
				}
				$linkend = $maxpage;
		}
		?><p type="text" width="50%" style="float:left;padding-right:45%;" align="center"><?php
		if ($page >= 1)
		{
				print "<a href='?tab=AdminMassUpdate&token=$token&page=".($page-1)."'> &lt&ltPrevious </a>   ";
		}            
		for ($i=1+$linkoffset; $i <= $linkend and $i <= $maxpage; $i++)
		{
				$style = ($i == $page) ? "color: blue;" : "color: black;";
				print "<a href='?tab=AdminMassUpdate&token=$token&page=$i' style='$style' text-align:'center'> [$i] </a>";
		}              
		if ($page < $maxpage)
		{
				print "<a href='?tab=AdminMassUpdate&token=$token&page=".($page+1)."'> Next&gt&gt </a>";
		}
		?></p>
     	<table cellspacing="0" cellpadding="0" class="table space" width="100%" align="center">
			<tbody>
				<tr>
					<th></th>
					<th></th>
					<th>Prodotti</th>
					<?php foreach($main_fields as $m=>$f){ ?>
					<th><?php echo $f['friendly'];?></th>
					<?php } ?>
					
				
				</tr>
				<?php 
				$link = new Link();
				$i = 0;
				foreach($contents[$page-1] as $product){
					$i++;
					$images = $this->_getImages($product['id_product'], $this->_id_lang);
					$imageSRC = '../'._PS_PROD_IMG_.$this->iso.'-default-small.jpg';
					foreach ($images as $image){
						if (!empty($image))
							$imageSRC = $link->getImageLink(strtolower($product['link_rewrite']), $product['id_product'].'-'.$image['id_image'], 'small');
					}	

					//$product['price'] = $product['db_price']; // price hack
					?>  <tr>
						<td><?php echo $i+(($page-1)*100);?></td>
						<td><img src="http://<?php echo $imageSRC; ?>"	class="jqzoom" title="<?php echo $product['name'];?>" alt="<?php echo $product['name'];?>" width="45" height="45" /></td>
						<td><?php echo $product['name'];?></td>
                        <?php                                       
						foreach($main_fields as $m=>$f){ 
						   isset($f['prefix']) ? $prefix = $f['prefix'] : $prefix = '';
						   isset($f['input_size']) ? $size = $f['input_size'] : $size = '300';
						   isset($product[$f['db_field']]) ? $value = $product[$f['db_field']] : $value = '';
						   isset($f['db_field']) ? $field = $f['db_field'] : $field = '';
						   isset($product['id_product']) ? $productId = $product['id_product'] : $productId = '';
						   isset($f['suffix']) ? $suffix = $f['suffix'] : $suffix = '';
						?> <td><?php echo $prefix;?><input type="text" size="25" value="<?php echo $value;?>" name="mup[<?php echo $productId;?>][<?php echo $field;?>]"/><?php echo $suffix;?></td><?php 
						}
						foreach($feature_results as $k=>$v){ 
							// what value is in this feature?
							$feature_value = '';
							foreach($product['features'] as $f){
								if($f['id_feature']==$v['id_feature']){
									$feature_value = $f['value'];
								}
							}
							?><?php 
						} 
						?> 	</tr> <?php 
				} 
				?>
     		</tbody>
     	</table>
		<p type="text" width="98%" align="center"><?php
		if ($page >= 1)
		{
				print "<a href='?tab=AdminMassUpdate&token=$token&page=".($page-1)."'> &lt&ltPrevious </a>   ";
		}              
		for ($i=1+$linkoffset; $i <= $linkend and $i <= $maxpage; $i++)
		{
				$style = ($i == $page) ? "color: blue;" : "color: black;";
				print "<a href='?tab=AdminMassUpdate&token=$token&page=$i' style='$style' text-align:'center'> [$i] </a>   ";
		}              
		if ($page < $maxpage)
		{
				print "<a href='?tab=AdminMassUpdate&token=$token&page=".($page+1)."'> Next&gt&gt </a>";
		}
		?></p>
		<br><br>
		<input type="submit" class="button" name="go" value="<?= $this->l('Save All My Products');?>" onclick="this.value='<?= $this->l('Updating.... Please wait....');?>' this.disabled=true;" />
		<br><br>
     	<?php
     	
     	
     	$this->_html .= ob_get_clean();
     	
		     	$this->_html .= ' 
				
		     	
			</form>
		</fieldset>';
			
     	

        return $this->_html;
    }
	
	function _getConfig()
	{
		$weight_units = strtolower(Configuration::get('PS_WEIGHT_UNIT'));
	
		$currencies = Currency::getCurrencies();
		$default_currency = Configuration::get('PS_CURRENCY_DEFAULT');
		foreach ($currencies as $currency) {
			if ($currency['id_currency'] == $default_currency)
				$sign = $currency['sign'];
		}
		
		$weight_units = strtolower(Configuration::get('PS_WEIGHT_UNIT'));

		$main_product_fields = array(
			    
	 		"name"=>array(
	 			"db_field"=>"name",
	 			"friendly"=>$this->l('Name'),
	 			"isLang"  => 1,
	 			"input_size" => 25,
	 		),
			
		//	"Category"=>array(
        //      "db_field"=>"id_category_default",
        //      "friendly"=>$this->l('Category'),
        //      "isLang"  => 0,
        //      "input_size" => 1,  
		//	),
			
          
			"quantity"=>array(
				"db_field"=>"quantity",
				"friendly"=>$this->l('Quantita'),
				"isLang"  => 0,
				"input_size" => 1,
            ),

        	"weight"=>array(
			    "db_field"=>"weight",
	 			"friendly"=>$this->l('Peso kg'),
	 			"isLang"  => 0,
	 			"input_size" => 1,
	 		),

   
            
            "price"=>array(
                "db_field"=>"price",
                "friendly"=>$this->l('Prezzo iva esclusa'),
                "prefix"=> '(' . $sign . ')',
                 "isLang"  => 0,
                "input_size" => 6, 
			),
			//"reduction_price"=>array(
            //    "db_field"=>"reduction_price",
            //    "friendly"=>"Reduction Price",
			//	"isLang"  => 0,
			//	"prefix"=> '(' . $sign . ')',
	 		//	"input_size" => 6,
            //), 
            //"reduction_percent"=>array(
            //    "db_field"=>"reduction_percent",
            //    "friendly"=>"Reduction Percent",
			//	"isLang"  => 0,
	 		//	"input_size" => 4,
	 		//),  
	 		//"meta_description"=>array(
	 		//	"db_field"=>"meta_description",
	 		//	"friendly"=>"Meta Description",
	 		//	"isLang"  => 1,
	 		//	"input_size" => 30,
	 		//),  
	 		//"meta_keywords"=>array(
	 		//	"db_field"=>"meta_keywords",
	 		//	"friendly"=>"Meta Keywords",
	 		//	"isLang"  => 1,
	 		//	"input_size" => 40,
	 		//),  
			//"id_manufacturer"=>array(
	 		//	"db_field"=>"id_manufacturer",
	 		//	"friendly"=>"ID Manufacturer",
	 		//	"isLang"  => 0,
	 		//	"input_size" => 1,
	 		//),
            
			// "description_short"=>array(
	 			// "db_field"=>"description_short",
	 			// "friendly"=>"Desription Short",
	 			// "isLang"  => 1,
	 			// "input_size" => 40,
	 		// ),
			// "description"=>array(
	 			// "db_field"=>"description",
	 			// "friendly"=>"Description",
	 			// "isLang"  => 1,
	 			// "input_size" => 100,
	 		// ), 			
	 	);
	 	return $main_product_fields;
	}
	
	function _updateProducts()
	{
		$product_settings = $_POST['mup'];
		
		if(!is_array($product_settings)){
			return false;
		}
		
		$main_fields = $this->_getConfig();
		
		foreach($product_settings as $product_id => $data){
			$product_id = (int)$product_id;
			if(!$product_id)continue;
			$sql = "UPDATE "._DB_PREFIX_."product SET date_upd = NOW() ";
			$do_update = false;
			// we're updating the product id. check and update the main fields.
			foreach($main_fields as $field){
				if ($field['isLang'] == 0) {
				$update_value = trim(pSQL($data[$field['db_field']]));
					if(($update_value !='')){
						// we've found a main field to update!
						$do_update = true;
						$sql .= ", `".$field['db_field']."` = '$update_value' ";
					}
				}
			}
			if($do_update){
				$sql .= " WHERE `id_product` = '$product_id' LIMIT 1";
				//echo $sql ." <br>\n";
				if(!Db::getInstance()->Execute($sql)){
					// yer yer i know - dodgey - but this should never happen, all input is sanatised.
					// a "just in case" so we dont go and bork all our products if there's a coding error.
					echo "FAILED TO UPDATE: $sql ";
					echo mysql_error();
					exit;
				}
			}
                        
                        $main_fields = $this->_getConfig();
			$sql = "UPDATE "._DB_PREFIX_."product_shop SET ";
                       
			$do_update = false;
			// we're updating the product id. check and update the main fields.
			foreach($main_fields as $field){
				if ($field['isLang'] == 0) {
				$update_value = trim(pSQL($data[$field['db_field']]));
					if(($update_value !='')){
						// we've found a main field to update!
                                             $valori= ($_POST['mup']);
                                   $prezzoprod = ($valori[$product_id]['price']);
                                            $do_update = true;
                                            $sql .= "";
				}
			}
                        }
			if($do_update){
				$sql .= "price ='$prezzoprod' WHERE `id_product` = $product_id";
				//echo $sql ." <br>\n";
				if(!Db::getInstance()->Execute($sql)){
					// yer yer i know - dodgey - but this should never happen, all input is sanatised.
					// a "just in case" so we dont go and bork all our products if there's a coding error.
					echo "FAILED TO UPDATE: $sql ";
					echo mysql_error();
                                        echo $main_product_fields;
					exit;
				}
			}
                        
                        //aggiorno la quantitÃ  disponibile
                        
                        $main_fields = $this->_getConfig();
			$sql = "UPDATE "._DB_PREFIX_."stock_available SET ";
                       
			$do_update = false;
			// we're updating the product id. check and update the main fields.
			foreach($main_fields as $field){
				if ($field['isLang'] == 0) {
				$update_value = trim(pSQL($data[$field['db_field']]));
					if(($update_value !='')){
						// we've found a main field to update!
                                             $valori= ($_POST['mup']);
                                   $quantitaprod = ($valori[$product_id]['quantity']);
                                            $do_update = true;
                                            $sql .= "";
				}
			}
                        }
			if($do_update){
				$sql .= "quantity ='$quantitaprod' WHERE `id_product` = $product_id";
				//echo $sql ." <br>\n";
				if(!Db::getInstance()->Execute($sql)){
					// yer yer i know - dodgey - but this should never happen, all input is sanatised.
					// a "just in case" so we dont go and bork all our products if there's a coding error.
					echo "FAILED TO UPDATE: $sql ";
					echo mysql_error();
                                        echo $main_product_fields;
					exit;
				}
			}
                        
                        
			// update product_lang 
            // Product id is updated only do have "something" after SET, because additional fields 
            // are added with comma at the start (see $sql .= ", `".$field['db_field']."` = '$update_value' ";)
			$sql = "UPDATE "._DB_PREFIX_."product_lang SET `id_product` = $product_id";
			$do_update = false;
			foreach($main_fields as $field){
			    if ($field['isLang'] != 0) {
    				$update_value = trim(pSQL($data[$field['db_field']]));
    				if($update_value!=''){
    					// we've found a main field to update!
    					$do_update = true;
    					$sql .= ", `".$field['db_field']."` = '$update_value' ";
    				}
    			}
			}
			if($do_update){
				$sql .= " WHERE `id_product` = '$product_id' AND `id_lang` = ".intval($this->_id_lang)." LIMIT 1";
				if(!Db::getInstance()->Execute($sql)){
					// yer yer i know - dodgey - but this should never happen, all input is sanatised.
					// a "just in case" so we dont go and bork all our products if there's a coding error.
					echo "FAILED TO UPDATE: $sql ";
					echo mysql_error();
					exit;
				}
			}
			
			// now we have to check the product features! trickeyness..
			// we grab a list of their current features, if any...
			$product_features = Product::getFeaturesStatic($product_id);
			//print_r($product_features);
			$new_features = array();
			$do_feature_update = false;
			if(isset($data['ff']) && is_array($data['ff'])){
				foreach($data['ff'] as $feature_id => $feature_value){
					$update_value = trim(pSQL($feature_value));
					if($update_value){
						//YEY! we can update this products feature value... 
						$do_feature_update = true;
						$new_features[$feature_id] = $update_value;
					}
				}
			}

			//print_r($new_features);
			if($do_feature_update){
				$product = new Product($product_id);
				$product->deleteFeatures();
				foreach($new_features as $feature_id => $feature_value){
					// add our new custom feature:
					//echo "Adding $feature_id as $feature_value <br>\n";
					$id_value = $product->addFeaturesToDB($feature_id, 0, true, $this->_id_lang);
					$product->addFeaturesCustomToDB($id_value, $this->_id_lang, $feature_value);
				}
			}
		}
		
		
		/*
		[product_id] => array(
			"price" => 123.45,
			"weight" => 3,
			"ff"=> array(
				[feature_id] => "value",
				[feature_id] => "value",
				[feature_id] => "value",
			)
		)
		*/
		
	 	return true;
	}

	protected function _isAdmin()
	{
		global $cookie;
		$employee = new Employee((int) $cookie->id_employee);
		return (int) $employee->id_profile == 1 || $employee->id_profile == 3 || $employee->id_profile == 2 || $employee->id_profile == 4;
                //here some id_profile can use this plugin
	}
	
	private function _getImages($productId, $id_lang)
	{
		return Db::getInstance()->ExecuteS('
		SELECT DISTINCT i.`cover`, i.`id_image`, il.`legend`, i.`position`
		FROM `'._DB_PREFIX_.'image` i
		LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)($id_lang).')
		WHERE i.`id_product` = '.(int)($productId).' AND i.`cover` = 1
		ORDER BY `position`');
	}
	
}

?>