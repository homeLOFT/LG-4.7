

INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_auth_then_capture', value='Authorization then capture', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_capture_now', value='Immediate Charge', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_amazon_advanced', value='Pay with Amazon', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_order_avail_actions', value='Pay with Amazon available order actions', topic='Labels';

INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_refund_status', value='Refund status', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_capture_status', value='Capture status', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_confirm_capture', value='This operation will capture funds from customer. Please confirm to proceed.', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_confirm_refund', value='This operation will refund funds to customer. Please confirm to proceed.', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_confirm_void', value='This operation will cancel order. Payment authorization will be voided. Please confirm to proceed.', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_checkout', value='Amazon Checkout', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_refresh', value='Refresh Status', topic='Labels';

INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_synchronous', value='Synchronous', topic='Labels';
INSERT INTO xcart_languages SET code='en', name='lbl_amazon_pa_asynchronous', value='Asynchronous', topic='Labels';

INSERT INTO xcart_languages SET code='en', name='module_descr_Amazon_Payments_Advanced', value='This module enables Pay with Amazon functionality', topic='Modules';
INSERT INTO xcart_languages SET code='en', name='module_name_Amazon_Payments_Advanced', value='Pay with Amazon', topic='Labels';

INSERT INTO xcart_languages VALUES ('en','module_requirements_Amazon_Payments_Advanced', 'The module requires OpenSSL extension and hash (http://www.php.net/manual/en/function.hash.php) functions should be available.<br />', 'Modules');

INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_sid','Amazon Seller ID','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_access_key','Access Key ID','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_mode','Operation mode','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_secret_key','Secret Access Key','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_currency','Country of Merchant account','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_capture_mode','Capture mode','Options');
INSERT INTO xcart_languages VALUES ('en','opt_amazon_pa_sync_mode','Type of authorization request','Options');

