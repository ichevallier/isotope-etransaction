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
 * @copyright  IRa coding 2012
 * @author	   IRa coding <http://www.poisson-soluble.com>
 * @license    http://opensource.org/licenses/lgpl-3.0.html
 * @package	   payment e-transaction 
 */


/**
 * Add a palette to tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['palettes']['etransaction'] = '{type_legend},type,name,label;{note_legend:hide},note;{config_legend},new_order_status,minimum_total,maximum_total,countries,shipping_modules,product_types;{gateway_legend},etransaction_id,etransaction_mode,etransaction_site,etransaction_rang;{price_legend:hide},price,tax_class;{enabled_legend},debug,enabled';


/**
 * Add fields to tl_iso_payment_modules
 */
$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['etransaction_id'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['etransaction_id'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>9, 'rgxp'=>'digit', 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['etransaction_mode'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['etransaction_mode'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>4, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['etransaction_site'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['etransaction_site'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>7, 'tl_class'=>'w50')
);

$GLOBALS['TL_DCA']['tl_iso_payment_modules']['fields']['etransaction_rang'] = array
(
	'label'			=> &$GLOBALS['TL_LANG']['tl_iso_payment_modules']['etransaction_rang'],
	'inputType'		=> 'text',
	'eval'			=> array('mandatory'=>true, 'maxlength'=>2, 'tl_class'=>'w50')
);

?>