<?php if (!defined('TL_ROOT')) die('You can not access this file directly!');

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2010 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Isotope eCommerce Workgroup 2009-2011
 * @author	   IRa coding <http://www.poisson-soluble.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * Class PaymentEtransaction
 * 
 * Provide a payment method "E-Transaction" for Isotope.
 * @copyright IRa coding 2012
 * @author	 IRa coding <http://www.poisson-soluble.com>
 * @package	payment e-transaction 
 * @license	LGPL
 * @filesource
 */
class PaymentEtransaction extends IsotopePayment
{
	/**
	 * Return a list of status options.
	 *
	 * @access public
	 * @return array
	 */
	public function statusOptions()
	{
		//return array('pending', 'processing', 'complete', 'on_hold', 'failed', 'demonstration');
		return array('pending', 'processing', 'complete', 'on_hold');
	}
	
	/**
	 * Process checkout payment
	 * @return mixed
	 */
	public function processPayment()
	{
		log_message('processPayment '.time());
		
		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id)) return false;
		else return true;
	}


	/**
	 * Process Transaction URL notification
	 */
	public function processPostSale()
	{
	
		log_message('processPostSale Ref : '.$this->Input->get('ref'));
		#return;
		/*
		$montant=$_GET['montant'];
		$ref_com=$_GET['ref'];
		$auto=$_GET['auto'];
		$trans=$_GET['trans'];
		*/
		# echo "GET => ".$this->Input->get('auto');
		# echo "POST => ".$this->Input->post('auto');
		
		/* --------------------------------------------------------------------------------------------------------------- */
		// TEST IF CONNEXION
		/* --------------------------------------------------------------------------------------------------------------- */
		
		
		//$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE order_id=?")->limit(1)->execute($this->Input->get('ref'));
		$objOrder = $this->Database->prepare("SELECT * FROM tl_iso_orders WHERE uniqid=?")->limit(1)->execute($this->Input->get('ref'));
          log_message("SELECT * FROM tl_iso_orders WHERE uniqid=".$this->Input->get('ref'));
        if (!$objOrder->numRows)
        {
	        //$this->log('Order ID "' . $this->getRequestData('orderID') . '" not found', 'PaymentEtransaction processPostSale()', TL_ERROR);
	        log_message('PaymentEtransaction processPostSale() failed :: Order ID not found');
	        return;
        }
		
		// Load / initialize data
		$arrPayment = deserialize($objOrder->payment_data, true);
		
		// Store request data in order for future references
		$arrPayment['payment_data']['POSTSALE'][] = $_GET;		
		
		log_message('ID : '.$objOrder->id);
		log_message('CODE ERROR : '.$this->Input->get('erreur'));
		switch( $this->Input->get('erreur') )
        {
			case '00000':
                $arrPayment['status'] = 'complete';
                $this->Database->execute("UPDATE tl_iso_orders SET date_paid=" . time() . " WHERE id=" . $objOrder->id);
                break; 
			case '00003': case '00004': case '00006': case '00008': case '00009': case '00010': case '00011': case '00015': case '00016': case '00021': case '00029': case '00030':
				break;
		}

		$arrData = $objOrder->row();
		$arrData['old_payment_status'] = $arrPayment['status'];
        
        //$arrPayment['status'] = $arrSet['payment_data']['status'];
        $arrData['new_payment_status'] = $arrPayment['status'];

        // REQUETE 
        $this->Database->prepare("UPDATE tl_iso_orders SET payment_data=? WHERE id=?")->execute(serialize($arrPayment), $objOrder->id);        
		$this->Database->execute("UPDATE tl_iso_orders SET status='" . $arrPayment['status'] . "' WHERE id=" . $objOrder->id);
		
		
		/* PATCH 2015 :: IF CUSTOMER CLICK ON "RETURN" BUTTON ==> PBX_EFFECTUE ISNT CALL */
		
		log_message('CART_ID : '.$objOrder->cart_id);

		if ($this->Input->get('erreur') == '00000') {

			// ADD INTO TABLE tl_iso_order_items de tl_iso_cart_items
			$objCartPatch = $this->Database->execute("SELECT * FROM tl_iso_cart_items WHERE pid = " . $objOrder->cart_id);
			while ($objCartPatch->next()) {
				$req = "INSERT INTO tl_iso_order_items VALUES ('','".$objOrder->id."','".$objCartPatch->tstamp."','".$objCartPatch->product_id."','".$objCartPatch->product_sku."','".$objCartPatch->product_name."','".$objCartPatch->product_options."','".$objCartPatch->product_quantity."','".$objCartPatch->price."','".$objCartPatch->taxe_id."')";
				$this->Database->execute($req);
			};
			
			// CREATE order_id INTO tl_iso_order
			$nouvel_id = $this->generateOrderIdPayment($objOrder->id);
			log_message ('Order Id Payment : '.$nouvel_id);
		}

	}
	/**
	 * Generate the next higher Order-ID Payment based on config prefix, order number digits and existing records
	 * @return string
	 */
	private function generateOrderIdPayment($id_table_order)
	{
		if ($this->strOrderId != '')
		{
			return $this->strOrderId;
		}
		
		// HOOK: generate a custom order ID
		if (isset($GLOBALS['ISO_HOOKS']['generateOrderId']) && is_array($GLOBALS['ISO_HOOKS']['generateOrderId']))
		{
			foreach ($GLOBALS['ISO_HOOKS']['generateOrderId'] as $callback)
			{
				$this->import($callback[0]);
				$strOrderId = $this->$callback[0]->$callback[1]($this);
				
				if ($strOrderId !== false)
				{
					$this->strOrderId = $strOrderId;
					break;
				}
			}
		}

		if ($this->strOrderId == '')
		{
			$strPrefix = $this->Isotope->Config->orderPrefix;
			$intPrefix = utf8_strlen($strPrefix);
			$arrConfigIds = $this->Database->execute("SELECT id FROM tl_iso_config WHERE store_id=" . $this->Isotope->Config->store_id)->fetchEach('id');
	
			// Lock tables so no other order can get the same ID
			$this->Database->lockTables(array('tl_iso_orders'));
	
			// Retrieve the highest available order ID
			$objMax = $this->Database->prepare("SELECT order_id FROM tl_iso_orders WHERE " . ($strPrefix != '' ? "order_id LIKE '$strPrefix%' AND " : '') . "config_id IN (" . implode(',', $arrConfigIds) . ") ORDER BY CAST(" . ($strPrefix != '' ? "SUBSTRING(order_id, " . ($intPrefix+1) . ")" : 'order_id') . " AS UNSIGNED) DESC")->limit(1)->executeUncached();
			$intMax = (int) substr($objMax->order_id, $intPrefix);
			
			$this->strOrderId = $strPrefix . str_pad($intMax+1, $this->Isotope->Config->orderDigits, '0', STR_PAD_LEFT);
		}

		$this->Database->prepare("UPDATE tl_iso_orders SET order_id=? WHERE id=$id_table_order")->executeUncached($this->strOrderId);
		$this->Database->unlockTables();

		return $this->strOrderId;
	}

	/**
	 * HTML form for checkout
	 * @return string
	 */
	public function checkoutForm()
	{
		$objOrder = new IsotopeOrder();
		if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id))
		{
			$this->redirect($this->addToUrl('step=failed', true));
		}
		
		list($endTag, $startScript, $endScript) = IsotopeFrontend::getElementAndScriptTags();
		
		/* ------------------------------------------------------------ */
		/* VALUE E-TRANSACTION */
		/* ------------------------------------------------------------ */
		/*
		$this->etransaction_id;
		$this->etransaction_mode;
		$this->etransaction_site;
		$this->etransaction_rang;
		*/
		/* ------------------------------------------------------------ */
		
		$strBuffer = '<h2>' . $GLOBALS['TL_LANG']['MSC']['pay_with_etransaction'][0] . '</h2>
		<p class="message">' . $GLOBALS['TL_LANG']['MSC']['pay_with_etransaction'][1] . '</p>
		<FORM action = "/cgi-bin/modulev2.cgi" METHOD ="POST">
		<input type="hidden" name="PBX_MODE" value="'.$this->etransaction_mode.'"' . $endTag . '
		<input type="hidden" name="PBX_SITE" value="'.$this->etransaction_site.'"' . $endTag . '
		<input type="hidden" name="PBX_RANG" value="'.$this->etransaction_rang.'"' . $endTag . '
		<input type="hidden" name="PBX_IDENTIFIANT" value="'.$this->etransaction_id.'"' . $endTag;

		$intPrice = $this->Isotope->Cart->grandTotal * 100;
		
		$uniqid=$objOrder->uniqid;
		// PBX_DEVISE :: 978 => euros
		$strBuffer .= '<input type="hidden" name="PBX_TOTAL" value="' . str_replace(".","",$intPrice) . '"' . $endTag;
		$strBuffer .= '<input type="hidden" name="PBX_DEVISE" value="978"' . $endTag . '
		<input type="hidden" name="PBX_CMD" value="' . $uniqid . '"' . $endTag . '
		<input type="hidden" name="PBX_PORTEUR" value="' . $this->Isotope->Cart->billingAddress['email'] . '"' . $endTag . '
		<input type="hidden" name="PBX_REPONDRE_A" value="' . $this->Environment->base .'system/modules/isotope/postsale.php?mod=pay&id='. $this->id .'"' . $endTag . '
		<input type="hidden" name="PBX_RETOUR" value="montant:M;ref:R;auto:A;trans:T;erreur:E"' . $endTag . '
		<input type="hidden" name="PBX_EFFECTUE" value="' . $this->Environment->base . $this->addToUrl('step=complete') . '?uid=' . $uniqid . '"' . $endTag . '
		<input type="hidden" name="PBX_REFUSE" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '"' . $endTag . '
		<input type="hidden" name="PBX_ANNULE" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '"' . $endTag . '
		<input type="hidden" name="PBX_TXT" value=" "' . $endTag . '
		<input type="hidden" name="PBX_WAIT" value="0"' . $endTag . '
		<input type="hidden" name="PBX_BOUTPI" value="nul"' . $endTag . '
		<input type="hidden" name="PBX_BKGD" value="white"' . $endTag . '
		<input type="hidden" name="PBX_LANGUE" value="FRA"' . $endTag . '
		<input type="hidden" name="PBX_ERREUR" value="' . $this->Environment->base . $this->addToUrl('step=failed') . '"' . $endTag . '
		<input type="hidden" name="PBX_TYPECARTE" value="CB"' . $endTag . '
		<input type="submit" name="bouton_paiement" value="'.specialchars($GLOBALS['TL_LANG']['MSC']['pay_with_etransaction'][2]). '"' . $endTag . '
		</form>

		' . $startScript . '
		window.addEvent( \'domready\' , function() {
		  $(\'payment_form\').submit();
		});
		' . $endScript;

		return $strBuffer;
	}
	
/* ------------------------------------------------------------------------------------------------------------------------------------ */
	
}

?>