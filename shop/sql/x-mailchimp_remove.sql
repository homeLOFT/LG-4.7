DELETE FROM xcart_modules WHERE module_name='Adv_Mailchimp_Subscription';

DROP TABLE IF EXISTS xcart_mailchimp_newslists;

DELETE FROM xcart_config WHERE name='adv_mailchimp_analytics' AND category='Adv_Mailchimp_Subscription';
DELETE FROM xcart_config WHERE name='adv_mailchimp_apikey' AND category='Adv_Mailchimp_Subscription';
DELETE FROM xcart_config WHERE name='adv_mailchimp_register_opt' AND category='Adv_Mailchimp_Subscription';

DELETE FROM xcart_languages WHERE name='lbl_mailchimp_news_management';
DELETE FROM xcart_languages WHERE name='msg_adm_mailchimp_connection_configured';
DELETE FROM xcart_languages WHERE name='msg_adm_mailchimp_newslists_imported';
DELETE FROM xcart_languages WHERE name='msg_adm_mailchimp_newslists_import_error';
DELETE FROM xcart_languages WHERE name='msg_adm_mailchimp_newslists_no_lists';
DELETE FROM xcart_languages WHERE name='txt_mailchimp_news_management_top_text';
DELETE FROM xcart_languages WHERE name='module_descr_Adv_Mailchimp_Subscription';
DELETE FROM xcart_languages WHERE name='module_name_Adv_Mailchimp_Subscription';
DELETE FROM xcart_languages WHERE name='opt_adv_mailchimp_analytics';
DELETE FROM xcart_languages WHERE name='opt_adv_mailchimp_apikey';
DELETE FROM xcart_languages WHERE name='opt_adv_mailchimp_register_opt';
