<?php
/**
 * @package Joomla
 * @subpackage jAnalyticsVirtuemart
 * @copyright (C) 2010 - Matthieu BARBE - www.ccomca.com
 * @license GNU/GPL v2
 * 
 * jAnalyticsVirtuemart is a derivative work of the excellent Google Analytics Tracking Module (from Estime, http://extensions.joomla.org/extensions/1233/details) and Asynchronous Google Analytics Plugin (from pbwebdev, http://www.pbwebdev.com.au/blog/asynchronous-google-analytics-plugin-for-joomla#download)
 *
 *
 * jAnalyticsVirtuemart is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin');

class plgSystemjAnalyticsVirtuemart extends JPlugin
{
	function plgjAnalyticsVirtuemart(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->_plugin = JPluginHelper::getPlugin( 'system', 'jAnalyticsVirtuemart' );
		$this->_params = new JParameter( $this->_plugin->params );
		
	}
	
	function onAfterRender()
	{
		global $mainframe;
		
	  	$params = &JComponentHelper::getParams( 'com_analytics' );
	        $trackerCode = $params->get('webPropertyId', '');
	      
	        if($trackerCode == '' || $mainframe->isAdmin() || strpos($_SERVER["PHP_SELF"], "index.php") === false)
	        {
			return;
	        }
		
		//getting body code and storing as buffer
		$buffer = JResponse::getBody();
		
		//embed Google Analytics code
		$javascript = "<script type=\"text/javascript\">
					  var _gaq = _gaq || [];
					  
					  _gaq.push(['_setAccount', '".$trackerCode."']);
					  _gaq.push(['_trackPageview']);
					  ".$this->getOrder($params)."					
					  (function() {
						var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
						ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
					  })();
					</script>";

		// adding the Google Analytics code in the header before the ending </head> tag and then replacing the buffer
		$buffer = preg_replace ("/<\/head>/", "\n\n".$javascript."\n\n</head>", $buffer); 
		
		//output the buffer
		JResponse::setBody($buffer);
		
		return true;
	}
	
	//return an order information
	function getOrder($params)
	{
	global $mainframe;
	
	//thank you HOTKEY33 
	if (JRequest::getVar( 'option' ) == "com_virtuemart" && (JRequest::getVar( 'page' ) == "checkout.thankyou" || JRequest::getVar( 'page' ) == "checkout.result" || JRequest::getVar( 'page' ) == "checkout.paybox_result" || JRequest::getVar( 'page' ) == "checkout.spplus_response" || JRequest::getVar( 'page' ) == "checkout.sips_response") )
		{
			$db	=& JFactory::getDBO();
			$user = & JFactory::getUser();
			$user_id = $user->get('id');
			//prefix table for virtuemart
			$table_prefix = $this->params->get( 'table_prefix', "vm" );
			$queryb = "SELECT city, state, country, user_info_id FROM #__".$table_prefix."_user_info WHERE user_id = " . (int)$user_id . "";
			$db->setQuery( $queryb );
			//user info
			if ($userinfo = $db->loadObjectList()) {
    		foreach ($userinfo as $info) {
				$city = $info->city;
				$country = $info->country;
				$state = $info->state;
				$userinfoid = $info->user_info_id;
				}
			}
			
			//fetch latest order_id for this user	
			$ordersql = "SELECT MAX(order_id) AS order_id FROM #__".$table_prefix."_orders WHERE user_info_id = '" . $userinfoid . "' AND user_id = " . (int)$user_id . "";
			$db->setQuery( $ordersql );
			if($rows = $db->loadObjectList()){
				foreach ( $rows as $row ) {
 				$maxorderid = $row->order_id;
 				}
			}
			
			// fetch order number, order total, order tax	
			$sql = "SELECT order_id, order_total, order_tax, ship_method_id, order_shipping, order_shipping_tax FROM #__".$table_prefix."_orders WHERE user_id = " . (int)$user_id . " AND user_info_id = '" . $userinfoid . "' AND order_id = " . (int)$maxorderid . "";
			$db->setQuery( $sql );
			if ($orderbd = $db->loadObjectList()) {
	    			foreach ($orderbd as $orderinfo) {
						$orderid = $orderinfo->order_id;
						$ordertotal =$orderinfo->order_total;
						$ordertax =$orderinfo->order_tax;
						$ship_method_id =$orderinfo->ship_method_id;
						$shippingsum =$orderinfo->order_shipping;
						$shippingtaxsum = $orderinfo->order_shipping_tax + $orderinfo->order_tax; 
	    			}
	   		}
			
			if($state == "-") $state= JText::_( 'No State'); else $state;
			
			return  '
			
			_gaq.push([\'_trackPageview\']);
			_gaq.push([\'_addTrans\',
		    "'.$maxorderid.'",                           
		    "'.$this->cleanDoubleQuote($mainframe->getCfg( 'sitename' )).'",                            
		    "'.$ordertotal.'",                                    
		    "'.$shippingtaxsum.'",                                   
		    "'.$shippingsum.'",                                       
		    "'.$this->cleanDoubleQuote($city).'",                                 
		    "'.$this->cleanDoubleQuote($state).'",                               
		    "'.$this->cleanDoubleQuote($country).'"                                
		  	]);
		  	'.$this->getProductsOrder($params, $maxorderid, $userinfoid).'
		  	_gaq.push([\'_trackTrans\']);';
			
			
		}
	}
	
	function getProductsOrder ($params, $order_id, $userinfoid)
	{
		$db	=& JFactory::getDBO();
		// fetch cart products
		$productrow = "";
		//prefix table for virtuemart
		$table_prefix = $this->params->get( 'table_prefix', "vm" );
		$productsdb = "SELECT product_id, order_item_sku, order_item_name, product_item_price, product_final_price, product_quantity FROM #__".$table_prefix."_order_item WHERE user_info_id = '" . $userinfoid . "' AND order_id = " . (int)$order_id . "";
		$db->setQuery( $productsdb );
		$category='';
		
		if ($productsorder = $db->loadObjectList()) {
	    		foreach ($productsorder as $productinfo) {
		
					$product_id = $productinfo->product_id;
	    			$product_sku = $productinfo->order_item_sku; 				
					$product_name = $productinfo->order_item_name;			
					$product_price = $productinfo->product_item_price;
					$quantity = $productinfo->product_quantity;
					// search category
					$catsql = "SELECT DISTINCT (p.category_name) as catname FROM #__".$table_prefix."_product_category_xref c, jos_".$table_prefix."_category p WHERE product_id = " . $product_id ." AND p.category_id = c.category_id";
					$db->setQuery( $catsql );
				
					if ($catrows = $db->loadObjectList()) {
	    				foreach ($catrows as $catrow) {
							$category .= $catrow->catname . " ";
						}
					}
				
					$productrow .= '_gaq.push([\'_addItem\',
				    "'.$order_id.'",                             
				    "'.$this->cleanDoubleQuote($product_sku).'",                                     
				    "'.$this->cleanDoubleQuote($product_name).'",                               
				    "'.$this->cleanDoubleQuote($category).'",                          
				    "'.$product_price.'",                                    
				    "'.$quantity.'"                                         
				  	]);';
					
					
				}
			}
		return $productrow;
		
	}
	
	//clean double quote
	function cleanDoubleQuote($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, "UTF-8");
	}
}
?>
