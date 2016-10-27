-- **********************************************************
-- *                                                        *
-- * IMPORTANT NOTE                                         *
-- *                                                        *
-- * Do not import this file manually but use the TYPOlight *
-- * install tool to create and maintain database tables!   *
-- *                                                        *
-- **********************************************************

--
-- Table `tl_iso_payment_modules`
--

CREATE TABLE `tl_iso_payment_modules` (
  `etransaction_id` varchar(9) NOT NULL default '',
  `etransaction_mode` varchar(4) NOT NULL default '',
  `etransaction_site` varchar(7) NOT NULL default '',
  `etransaction_rang` varchar(2) NOT NULL default '',
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
