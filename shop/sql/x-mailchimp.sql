CREATE TABLE xcart_mailchimp_newslists (
	listid int(11) NOT NULL auto_increment,
	name varchar(255) NOT NULL default '',
	descr text NOT NULL,
	show_as_news char(1) NOT NULL default 'N',
	avail char(1) NOT NULL default 'N',
	subscribe char(1) NOT NULL default 'N',
	lngcode char(2) NOT NULL default 'en',
	mc_list_id varchar(15) default '',
	PRIMARY KEY (listid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO xcart_modules SET module_name='Adv_Mailchimp_Subscription', module_descr='This module allows to use advanced MailChimp newsletters management service. To create a MailChimp account please <a href="http://www.mailchimp.com/signup/?pid=xcart&amp;source=website" target="_blank">click here</a>.', active='N', init_orderby=0, author='qualiteam', module_url='', tags='marketing';
INSERT INTO xcart_config SET name='adv_mailchimp_analytics', comment='Enable Analytics 360 for Mailchimp', value='Y', category='Adv_Mailchimp_Subscription', orderby='3', type='checkbox', defvalue='', variants='', validation='';
INSERT INTO xcart_config SET name='adv_mailchimp_apikey', comment='API Key : You can grab your API Key from here: http://admin.mailchimp.com/account/api-key-popup', value='', category='Adv_Mailchimp_Subscription', orderby='1', type='text', defvalue='', variants='', validation='';
INSERT INTO xcart_config SET name='adv_mailchimp_register_opt', comment='Enable confirmation request for subscription from users profile page', value='Y', category='Adv_Mailchimp_Subscription', orderby='3', type='checkbox', defvalue='', variants='', validation='';
