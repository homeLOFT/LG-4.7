







CREATE TABLE xcart_address_book (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  default_s char(1) NOT NULL DEFAULT 'N',
  default_b char(1) NOT NULL DEFAULT 'N',
  title varchar(32) NOT NULL DEFAULT '',
  firstname varchar(128) NOT NULL DEFAULT '',
  lastname varchar(128) NOT NULL DEFAULT '',
  address varchar(255) NOT NULL DEFAULT '',
  city varchar(64) NOT NULL DEFAULT '',
  county varchar(32) NOT NULL DEFAULT '',
  state varchar(32) NOT NULL DEFAULT '',
  country char(2) NOT NULL DEFAULT '',
  zipcode varchar(32) NOT NULL DEFAULT '',
  zip4 varchar(4) NOT NULL DEFAULT '',
  phone varchar(32) NOT NULL DEFAULT '',
  fax varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY userid (userid),
  KEY default_s (userid,default_s),
  KEY default_b (userid,default_b)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_amazon_data (
  ref varchar(255) NOT NULL DEFAULT '',
  sessid binary(32) NOT NULL,
  cart mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (ref),
  KEY sessid (sessid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_amazon_orders (
  orderid int(11) NOT NULL DEFAULT '0',
  amazon_oid varchar(255) NOT NULL DEFAULT '',
  total decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (orderid),
  UNIQUE KEY ao (amazon_oid,orderid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_avatax_cache (
  cache_key varchar(64) NOT NULL DEFAULT '',
  `value` blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  expiration int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (cache_key),
  KEY expiration_index (expiration)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_benchmark_pages (
  pageid int(11) NOT NULL AUTO_INCREMENT,
  script varchar(64) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  method char(1) NOT NULL DEFAULT 'G',
  PRIMARY KEY (pageid),
  UNIQUE KEY sdm (script,`data`,method)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_bonus_memberships (
  bonusid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (bonusid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_categories (
  categoryid int(11) NOT NULL AUTO_INCREMENT,
  parentid int(11) NOT NULL DEFAULT '0',
  category varchar(255) NOT NULL DEFAULT '',
  description text NOT NULL,
  meta_description varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  has_disabled_parent char(1) NOT NULL DEFAULT 'N',
  order_by int(11) NOT NULL DEFAULT '0',
  product_count int(11) NOT NULL DEFAULT '0',
  top_product_count int(11) NOT NULL DEFAULT '0',
  meta_keywords varchar(255) NOT NULL DEFAULT '',
  override_child_meta char(1) NOT NULL DEFAULT 'Y',
  title_tag varchar(255) NOT NULL DEFAULT '',
  lpos int(11) NOT NULL DEFAULT '0',
  rpos int(11) NOT NULL DEFAULT '0',
  category_level smallint(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (categoryid),
  UNIQUE KEY ia (categoryid,avail),
  UNIQUE KEY lcrc (lpos,categoryid,rpos,category),
  KEY avail (avail),
  KEY order_by (order_by,category),
  KEY lpos (lpos),
  KEY rpos (rpos),
  KEY parentid (parentid),
  KEY poc (parentid,order_by,category),
  KEY pa (lpos,avail)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_categories_lng (
  `code` char(2) NOT NULL DEFAULT '',
  categoryid int(11) NOT NULL DEFAULT '0',
  category varchar(255) NOT NULL DEFAULT '',
  description text NOT NULL,
  PRIMARY KEY (`code`,categoryid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_categories_subcount (
  categoryid int(11) NOT NULL DEFAULT '0',
  subcategory_count int(11) NOT NULL DEFAULT '0',
  product_count int(11) NOT NULL DEFAULT '0',
  top_product_count int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (categoryid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_category_bookmarks (
  categoryid int(11) NOT NULL DEFAULT '0',
  add_date int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY categoryid (categoryid,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_category_memberships (
  categoryid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (categoryid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_category_threshold_bestsellers (
  categoryid int(11) NOT NULL DEFAULT '0' COMMENT '0 value is used for home page',
  membershipid int(11) NOT NULL DEFAULT '0',
  threshold_sales_stats int(11) NOT NULL DEFAULT '1' COMMENT 'threshold is a current value of xcart_product_sales_stats.sales_stats to show at least config.Bestsellers.number_of_bestsellers for the categoryid-membershipid',
  PRIMARY KEY (categoryid,membershipid),
  KEY membershipid (membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Keep threshold_sales_stats for each membership in the category.Using for bestsellers section';





CREATE TABLE xcart_cc_gestpay_data (
  `value` char(32) NOT NULL DEFAULT '',
  `type` char(1) NOT NULL DEFAULT 'C',
  PRIMARY KEY (`value`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_cc_pp3_data (
  ref varchar(255) NOT NULL DEFAULT '',
  sessid char(32) NOT NULL DEFAULT '',
  param1 varchar(255) NOT NULL DEFAULT '',
  param2 varchar(255) NOT NULL DEFAULT '',
  param3 varchar(255) NOT NULL DEFAULT '',
  param4 varchar(255) NOT NULL DEFAULT '',
  param5 varchar(255) NOT NULL DEFAULT '',
  param6 varchar(255) NOT NULL DEFAULT '',
  param7 varchar(255) NOT NULL DEFAULT '',
  param8 varchar(255) NOT NULL DEFAULT '',
  param9 varchar(255) NOT NULL DEFAULT '',
  trstat varchar(255) NOT NULL DEFAULT '',
  is_callback char(1) NOT NULL DEFAULT '',
  UNIQUE KEY refk (ref)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_ccprocessor_params (
  processor varchar(255) NOT NULL DEFAULT '',
  param varchar(64) NOT NULL DEFAULT '',
  `value` text NOT NULL COMMENT 'Changed to text to support 256bit keys in Cybersource',
  PRIMARY KEY (processor,param)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_ccprocessors (
  module_name varchar(255) NOT NULL DEFAULT '',
  `type` char(1) NOT NULL DEFAULT '',
  processor varchar(255) NOT NULL DEFAULT '',
  template varchar(255) NOT NULL DEFAULT '',
  param01 varchar(255) NOT NULL DEFAULT '',
  param02 varchar(255) NOT NULL DEFAULT '',
  param03 varchar(255) NOT NULL DEFAULT '',
  param04 varchar(255) NOT NULL DEFAULT '',
  param05 varchar(255) NOT NULL DEFAULT '',
  param06 varchar(255) NOT NULL DEFAULT '',
  param07 varchar(255) NOT NULL DEFAULT '',
  param08 varchar(255) NOT NULL DEFAULT '',
  param09 varchar(255) NOT NULL DEFAULT '',
  disable_ccinfo char(1) NOT NULL DEFAULT 'N',
  background char(1) NOT NULL DEFAULT 'N',
  testmode char(1) NOT NULL DEFAULT 'N',
  is_check char(1) NOT NULL DEFAULT '',
  is_refund char(1) NOT NULL DEFAULT '',
  c_template varchar(255) NOT NULL DEFAULT '',
  paymentid int(11) NOT NULL DEFAULT '0',
  cmpi char(1) NOT NULL DEFAULT '',
  use_preauth char(1) NOT NULL DEFAULT '',
  preauth_expire int(11) NOT NULL DEFAULT '0',
  has_preauth char(1) NOT NULL DEFAULT '',
  capture_min_limit varchar(32) NOT NULL DEFAULT '0%',
  capture_max_limit varchar(32) NOT NULL DEFAULT '0%',
  PRIMARY KEY (module_name),
  UNIQUE KEY pphm (paymentid,preauth_expire,has_preauth,module_name),
  KEY paymentid (paymentid),
  KEY processor (processor),
  KEY pph (paymentid,preauth_expire,has_preauth)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_class_lng (
  `code` char(2) NOT NULL DEFAULT 'en',
  classid int(11) NOT NULL DEFAULT '0',
  class varchar(128) NOT NULL DEFAULT '',
  classtext varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (classid,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_class_options (
  optionid int(11) NOT NULL AUTO_INCREMENT,
  classid int(11) NOT NULL DEFAULT '0',
  option_name varchar(255) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'Y',
  price_modifier decimal(12,2) NOT NULL DEFAULT '0.00',
  modifier_type char(1) NOT NULL DEFAULT '$',
  PRIMARY KEY (optionid),
  KEY orderby (orderby,avail),
  KEY ia (classid,avail)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_classes (
  classid int(11) NOT NULL AUTO_INCREMENT,
  productid int(11) NOT NULL DEFAULT '0',
  class varchar(128) NOT NULL DEFAULT '',
  classtext varchar(255) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'Y',
  is_modifier char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (classid),
  UNIQUE KEY paic (productid,avail,is_modifier,classid),
  KEY orderby (orderby,avail),
  KEY func_get_default_options_markup_list_many_products (is_modifier,avail),
  KEY class (class)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_clean_urls (
  clean_url varchar(250) NOT NULL DEFAULT '',
  resource_type char(1) NOT NULL DEFAULT '',
  resource_id int(11) NOT NULL DEFAULT '0',
  mtime int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (clean_url),
  KEY rr (resource_type,resource_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_clean_urls_history (
  id int(11) NOT NULL AUTO_INCREMENT,
  resource_type char(1) NOT NULL DEFAULT '',
  resource_id int(11) NOT NULL DEFAULT '0',
  clean_url varchar(250) NOT NULL DEFAULT '',
  mtime int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  UNIQUE KEY rrc (resource_type,resource_id,clean_url)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_condition_memberships (
  conditionid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (conditionid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_config (
  `name` varchar(32) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  category varchar(32) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  `type` enum('numeric','text','textarea','checkbox','password','separator','selector','multiselector','state','country','trimmed_text') DEFAULT 'text',
  defvalue text NOT NULL,
  variants text NOT NULL,
  validation varchar(255) NOT NULL DEFAULT '',
  signature char(40) NOT NULL DEFAULT '' COMMENT 'Used to validate significant values',
  PRIMARY KEY (`name`),
  KEY orderby (orderby),
  KEY category (category)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_contact_fields (
  fieldid int(11) NOT NULL AUTO_INCREMENT,
  field varchar(255) NOT NULL DEFAULT '',
  `type` char(1) NOT NULL DEFAULT 'T',
  variants text NOT NULL,
  def varchar(255) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  avail varchar(4) NOT NULL DEFAULT '',
  required varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (fieldid),
  KEY avail (avail),
  KEY required (required)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_counties (
  countyid int(11) NOT NULL AUTO_INCREMENT,
  stateid int(11) NOT NULL DEFAULT '0',
  county varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (countyid),
  UNIQUE KEY countyname (stateid,county),
  KEY countyid (stateid,countyid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_countries (
  `code` char(2) NOT NULL DEFAULT '',
  code_A3 char(3) NOT NULL DEFAULT '',
  code_N3 int(4) NOT NULL DEFAULT '0',
  region char(2) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  display_states char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_country_currencies (
  `code` char(3) NOT NULL DEFAULT '',
  country_code char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (`code`,country_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_cs_search_pids (
  id int(11) DEFAULT NULL,
  rank int(11) NOT NULL AUTO_INCREMENT,
  KEY id (id),
  KEY rank (rank)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Non-TEMPORARY table used as a fallback for its TEMPORARY counterpart';





CREATE TABLE xcart_currencies (
  `code` char(3) NOT NULL DEFAULT '',
  code_int int(3) NOT NULL DEFAULT '0',
  `name` varchar(128) NOT NULL DEFAULT '',
  symbol varchar(16) NOT NULL DEFAULT '',
  UNIQUE KEY `code` (`code`),
  KEY code_int (code_int)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_customer_bonuses (
  userid int(11) NOT NULL DEFAULT '0',
  points int(11) NOT NULL DEFAULT '0',
  memberships text NOT NULL,
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_customer_enabled_activities (
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Customer has enabled activity=Y if he/she has a row in this table';





CREATE TABLE xcart_customer_eu_cookie_accesses (
  userid int(11) NOT NULL DEFAULT '0',
  eu_cookie_access varchar(20) NOT NULL DEFAULT '' COMMENT 'EU_Cookie_Law module',
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_customer_saved_carts (
  userid int(11) NOT NULL DEFAULT '0',
  saved_cart mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_customers (
  id int(11) NOT NULL AUTO_INCREMENT,
  login varchar(128) NOT NULL DEFAULT '',
  username varchar(128) NOT NULL DEFAULT '',
  usertype char(1) NOT NULL DEFAULT '',
  `password` varchar(255) NOT NULL DEFAULT '',
  signature char(40) NOT NULL DEFAULT '' COMMENT 'Used to validate admin accounts',
  invalid_login_attempts int(11) NOT NULL DEFAULT '0',
  title varchar(32) NOT NULL DEFAULT '',
  firstname varchar(128) NOT NULL DEFAULT '',
  lastname varchar(128) NOT NULL DEFAULT '',
  company varchar(255) NOT NULL DEFAULT '',
  email varchar(128) NOT NULL DEFAULT '',
  url varchar(128) NOT NULL DEFAULT '',
  last_login int(11) NOT NULL DEFAULT '0',
  first_login int(11) NOT NULL DEFAULT '0',
  `status` char(1) NOT NULL DEFAULT 'Y',
  activation_key varchar(32) NOT NULL DEFAULT '',
  autolock char(1) NOT NULL DEFAULT 'N',
  suspend_date int(11) NOT NULL DEFAULT '0',
  referer varchar(255) NOT NULL DEFAULT '',
  ssn varchar(32) NOT NULL DEFAULT '',
  `language` char(2) NOT NULL DEFAULT 'en',
  change_password char(1) NOT NULL DEFAULT 'N',
  change_password_date int(11) NOT NULL DEFAULT '0',
  parent int(11) NOT NULL DEFAULT '0',
  pending_plan_id int(11) NOT NULL DEFAULT '0',
  is_anonymous_customer tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Keep data for non-registered customers which placed at least one order, 0 is registered, 1 is anonymous.',
  membershipid int(11) NOT NULL DEFAULT '0',
  pending_membershipid int(11) NOT NULL DEFAULT '0',
  tax_number varchar(50) NOT NULL DEFAULT '',
  tax_exempt char(1) NOT NULL DEFAULT 'N',
  trusted_provider char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (id),
  KEY `status` (`status`),
  KEY activation_key (activation_key),
  KEY ea (email,is_anonymous_customer),
  KEY first_login (first_login),
  KEY is_anonymous_customer (is_anonymous_customer),
  KEY last_login (last_login),
  KEY la (login,is_anonymous_customer),
  KEY membershipid (membershipid),
  KEY usertype (usertype)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_delayed_queries (
  id int(11) NOT NULL AUTO_INCREMENT,
  query_type varchar(255) NOT NULL DEFAULT '',
  `query` text NOT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id),
  KEY qd (query_type,`date`),
  KEY date_key (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_delivery (
  shippingid int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (shippingid,productid),
  KEY productid_index (productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_discount_coupons (
  coupon char(16) NOT NULL DEFAULT '',
  discount decimal(12,2) NOT NULL DEFAULT '0.00',
  coupon_type char(12) NOT NULL DEFAULT '',
  productid int(11) NOT NULL DEFAULT '0',
  categoryid int(11) NOT NULL DEFAULT '0',
  minimum decimal(12,2) NOT NULL DEFAULT '0.00',
  times int(11) NOT NULL DEFAULT '0',
  per_user char(1) NOT NULL DEFAULT 'N',
  times_used int(11) NOT NULL DEFAULT '0',
  `expire` int(11) NOT NULL DEFAULT '0',
  `status` char(1) NOT NULL DEFAULT '',
  provider int(11) NOT NULL DEFAULT '0',
  recursive char(1) NOT NULL DEFAULT 'N',
  apply_category_once char(1) NOT NULL DEFAULT 'N',
  apply_product_once char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (coupon),
  KEY provider (provider),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_discount_coupons_login (
  coupon varchar(16) NOT NULL DEFAULT '',
  userid int(11) NOT NULL DEFAULT '0',
  times_used int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (coupon,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_discount_memberships (
  discountid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (discountid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_discounts (
  discountid int(11) NOT NULL AUTO_INCREMENT,
  minprice decimal(12,2) NOT NULL DEFAULT '0.00',
  discount decimal(12,2) NOT NULL DEFAULT '0.00',
  discount_type char(32) NOT NULL DEFAULT 'absolute',
  provider int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (discountid),
  KEY provider (provider),
  KEY minprice (minprice)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_download_keys (
  download_key char(32) NOT NULL DEFAULT '' COMMENT 'Generated by keygen function via php:md5 call',
  expires int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  itemid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (download_key),
  UNIQUE KEY itemid (itemid),
  KEY key_func_delete_product_tbl_keys (productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_export_ranges (
  sec varchar(64) NOT NULL DEFAULT '',
  id varchar(64) NOT NULL DEFAULT '',
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (sec,id,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_extra_field_values (
  productid int(11) NOT NULL DEFAULT '0',
  fieldid int(11) NOT NULL DEFAULT '0',
  `value` char(255) NOT NULL DEFAULT '',
  PRIMARY KEY (productid,fieldid),
  KEY `value` (`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_extra_fields (
  fieldid int(11) NOT NULL AUTO_INCREMENT,
  provider int(11) NOT NULL DEFAULT '0',
  field char(255) NOT NULL DEFAULT '',
  `value` char(255) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  service_name char(128) NOT NULL DEFAULT '',
  PRIMARY KEY (fieldid),
  KEY provider (provider),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_extra_fields_lng (
  fieldid int(11) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL DEFAULT 'en',
  field char(255) NOT NULL DEFAULT '',
  UNIQUE KEY fc (fieldid,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_featured_products (
  productid int(11) NOT NULL DEFAULT '0',
  categoryid int(11) NOT NULL DEFAULT '0',
  product_order int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (productid,categoryid),
  KEY product_order (product_order),
  KEY avail (avail),
  KEY pacpo (productid,avail,categoryid,product_order)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_form_ids (
  sessid binary(32) NOT NULL COMMENT 'func_generate_formid->XCARTSESSID',
  formid binary(32) NOT NULL COMMENT 'func_generate_formid->md5()',
  `expire` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (sessid,formid),
  KEY `expire` (`expire`),
  KEY se (sessid,`expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_ge_products (
  sessid binary(32) NOT NULL,
  geid binary(32) NOT NULL COMMENT 'func_ge_add->md5()',
  productid int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY gp (geid,productid),
  KEY sessid (sessid),
  KEY productid (productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_giftcerts (
  gcid varchar(16) NOT NULL DEFAULT '' COMMENT 'Generated by func_giftcert_get_gcid() or from import file',
  orderid int(11) NOT NULL DEFAULT '0',
  purchaser varchar(128) NOT NULL DEFAULT '',
  recipient varchar(128) NOT NULL DEFAULT '',
  send_via char(1) NOT NULL DEFAULT 'E',
  recipient_email varchar(128) NOT NULL DEFAULT '',
  recipient_firstname varchar(128) NOT NULL DEFAULT '',
  recipient_lastname varchar(128) NOT NULL DEFAULT '',
  recipient_address varchar(255) NOT NULL DEFAULT '',
  recipient_city varchar(64) NOT NULL DEFAULT '',
  recipient_state varchar(32) NOT NULL DEFAULT '',
  recipient_zipcode varchar(32) NOT NULL DEFAULT '',
  recipient_zip4 varchar(4) NOT NULL DEFAULT '',
  recipient_country char(2) NOT NULL DEFAULT '',
  recipient_phone varchar(32) NOT NULL DEFAULT '',
  message text NOT NULL,
  amount decimal(12,2) NOT NULL DEFAULT '0.00',
  debit decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` char(1) NOT NULL DEFAULT 'P',
  add_date int(11) NOT NULL DEFAULT '0',
  block_date int(11) NOT NULL DEFAULT '0',
  tpl_file varchar(255) NOT NULL DEFAULT 'template_default.tpl',
  recipient_county varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (gcid),
  KEY orderid (orderid),
  KEY `status` (`status`),
  KEY add_date (add_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_giftreg_events (
  event_id int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  title varchar(255) NOT NULL DEFAULT '',
  event_date int(11) NOT NULL DEFAULT '0',
  description text NOT NULL,
  html_content text NOT NULL,
  sent_date int(11) NOT NULL DEFAULT '0',
  `status` char(1) NOT NULL DEFAULT 'P',
  guestbook char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (event_id),
  KEY userid (userid),
  KEY event_date (event_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_giftreg_guestbooks (
  message_id int(11) NOT NULL AUTO_INCREMENT,
  event_id int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `subject` varchar(255) NOT NULL DEFAULT '',
  message text NOT NULL,
  post_date int(11) NOT NULL DEFAULT '0',
  moderator char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (message_id),
  KEY event_id (event_id,post_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_giftreg_maillist (
  regid int(11) NOT NULL AUTO_INCREMENT,
  event_id int(11) NOT NULL DEFAULT '0',
  recipient_name varchar(255) NOT NULL DEFAULT '',
  recipient_email varchar(128) NOT NULL DEFAULT '',
  `status` char(1) NOT NULL DEFAULT 'P',
  status_date int(11) NOT NULL DEFAULT '0',
  confirmation_code varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (regid),
  UNIQUE KEY event_id (event_id,recipient_email),
  UNIQUE KEY confirmation_code (confirmation_code),
  KEY recipient_name (recipient_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_B (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_C (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_D (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY iaoi (id,avail,orderby,imageid),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_G (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_L (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_M (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_P (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_S (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id varchar(16) NOT NULL DEFAULT '',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_T (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_W (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  UNIQUE KEY id (id),
  KEY image_path (image_path)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_images_Z (
  imageid int(11) NOT NULL AUTO_INCREMENT,
  id int(11) NOT NULL DEFAULT '0',
  image mediumblob NOT NULL,
  image_path varchar(255) NOT NULL DEFAULT '',
  image_type varchar(64) NOT NULL DEFAULT 'image/jpeg',
  image_x int(11) NOT NULL DEFAULT '0',
  image_y int(11) NOT NULL DEFAULT '0',
  image_size int(11) NOT NULL DEFAULT '0',
  filename varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  alt varchar(255) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  md5 char(32) NOT NULL DEFAULT '',
  PRIMARY KEY (imageid),
  KEY image_path (image_path),
  KEY id (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_import_cache (
  data_type varbinary(3) NOT NULL DEFAULT '',
  id varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT '',
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (data_type,id,userid),
  KEY du (data_type,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='3 length is used for UL.usertype cache';





CREATE TABLE xcart_internal_banners (
  `type` varchar(255) NOT NULL DEFAULT '',
  param01 varchar(255) NOT NULL DEFAULT '',
  param02 varchar(255) NOT NULL DEFAULT '',
  param03 varchar(255) NOT NULL DEFAULT '',
  param04 varchar(255) NOT NULL DEFAULT '',
  param05 varchar(255) NOT NULL DEFAULT '',
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_iterations (
  sessid binary(32) NOT NULL,
  `code` varchar(8) NOT NULL DEFAULT '',
  id varchar(32) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (sessid,`code`,id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_language_codes (
  `code` char(2) NOT NULL DEFAULT '',
  code3 char(3) NOT NULL DEFAULT '',
  `language` varchar(128) NOT NULL DEFAULT '',
  country_code char(2) NOT NULL DEFAULT '',
  lngid int(11) NOT NULL AUTO_INCREMENT,
  `charset` varchar(32) NOT NULL DEFAULT 'UTF-8',
  r2l char(1) NOT NULL DEFAULT '',
  disabled char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (lngid),
  UNIQUE KEY code3 (code3),
  UNIQUE KEY code2 (`code`),
  KEY country_code (country_code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_languages (
  `code` char(2) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  topic varchar(24) NOT NULL DEFAULT '',
  PRIMARY KEY (`code`,`name`),
  KEY topic (topic)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_languages_alt (
  `code` char(2) NOT NULL DEFAULT '',
  `name` varchar(128) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`code`,`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_login_history (
  userid int(11) NOT NULL DEFAULT '0',
  date_time int(11) NOT NULL DEFAULT '0' COMMENT 'KEY date_time is used to delete expired rows',
  usertype char(1) NOT NULL DEFAULT '',
  `action` varchar(255) NOT NULL DEFAULT '',
  `status` varchar(32) NOT NULL DEFAULT '',
  ip int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (userid,date_time),
  KEY date_time (date_time)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_lwpp (
  userid int(11) NOT NULL DEFAULT '0',
  payerId char(128) NOT NULL DEFAULT '' COMMENT 'Sha512, generate by func_pplogin_create_hash function',
  openid_identity varchar(255) NOT NULL DEFAULT '',
  pplogin_email varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (userid,payerId)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_manufacturers (
  manufacturerid int(11) NOT NULL AUTO_INCREMENT,
  manufacturer varchar(255) NOT NULL DEFAULT '',
  url varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  orderby int(11) NOT NULL DEFAULT '0',
  provider int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'Y',
  meta_description varchar(255) NOT NULL DEFAULT '',
  meta_keywords varchar(255) NOT NULL DEFAULT '',
  title_tag varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (manufacturerid),
  KEY manufacturer (manufacturer),
  KEY orderby (orderby),
  KEY provider (provider),
  KEY avail (avail)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_manufacturers_lng (
  manufacturerid int(11) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL DEFAULT 'en',
  manufacturer varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  UNIQUE KEY mc (manufacturerid,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_memberships (
  membershipid int(11) NOT NULL AUTO_INCREMENT,
  area char(1) NOT NULL DEFAULT 'C',
  membership varchar(255) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  flag char(2) NOT NULL DEFAULT '',
  PRIMARY KEY (membershipid),
  KEY area (area),
  KEY orderby (orderby),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_memberships_lng (
  membershipid int(11) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL DEFAULT 'en',
  membership varchar(255) NOT NULL DEFAULT '',
  UNIQUE KEY mc (membershipid,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_modules (
  moduleid int(11) NOT NULL AUTO_INCREMENT,
  module_name varchar(255) NOT NULL DEFAULT '',
  module_descr varchar(255) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  init_orderby int(11) NOT NULL DEFAULT '0' COMMENT 'Initialization order for modules in customer area',
  author varchar(255) NOT NULL DEFAULT 'other',
  module_url varchar(255) NOT NULL DEFAULT '',
  tags varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (moduleid),
  UNIQUE KEY module_name (module_name),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_newsletter (
  newsid int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(128) NOT NULL DEFAULT '',
  body text NOT NULL,
  send_date int(11) NOT NULL DEFAULT '0',
  email1 varchar(128) NOT NULL DEFAULT '',
  email2 varchar(128) NOT NULL DEFAULT '',
  email3 varchar(128) NOT NULL DEFAULT '',
  `status` char(1) NOT NULL DEFAULT 'N',
  listid int(11) NOT NULL DEFAULT '0',
  show_as_news char(1) NOT NULL DEFAULT 'N',
  allow_html char(1) NOT NULL DEFAULT 'N',
  `date` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (newsid),
  UNIQUE KEY lsd (listid,show_as_news,`date`),
  KEY `status` (`status`),
  KEY send_date (send_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_newslist_subscription (
  listid int(11) NOT NULL DEFAULT '0',
  email char(128) NOT NULL DEFAULT '',
  to_be_sent char(1) NOT NULL DEFAULT '',
  since_date int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (listid,email),
  KEY to_be_sent (to_be_sent)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_newslists (
  listid int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  show_as_news char(1) NOT NULL DEFAULT 'N',
  avail char(1) NOT NULL DEFAULT 'N',
  subscribe char(1) NOT NULL DEFAULT 'N',
  lngcode char(2) NOT NULL DEFAULT 'en',
  PRIMARY KEY (listid),
  UNIQUE KEY asll (avail,show_as_news,lngcode,listid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_bonus_params (
  paramid int(11) NOT NULL AUTO_INCREMENT,
  bonusid int(11) NOT NULL DEFAULT '0',
  setid int(11) NOT NULL DEFAULT '0',
  param_type char(1) NOT NULL DEFAULT '',
  param_id int(11) NOT NULL DEFAULT '0',
  param_arg char(1) NOT NULL DEFAULT '',
  param_qnty int(11) NOT NULL DEFAULT '0',
  param_promo char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (paramid),
  UNIQUE KEY pt (param_type,paramid),
  KEY bonus_id_type (bonusid,param_type,param_id,param_arg),
  KEY bonusid (bonusid),
  KEY setid (setid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_bonuses (
  bonusid int(11) NOT NULL AUTO_INCREMENT,
  offerid int(11) NOT NULL DEFAULT '0',
  bonus_type char(1) NOT NULL DEFAULT '',
  amount_type char(1) NOT NULL DEFAULT '',
  amount_min decimal(12,2) NOT NULL DEFAULT '0.00',
  amount_max decimal(12,2) NOT NULL DEFAULT '0.00',
  bonus_data text,
  provider int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (bonusid),
  UNIQUE KEY b_type (offerid,bonus_type),
  KEY b_sprice (bonusid,avail,bonus_type,amount_type,amount_min,amount_max)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_condition_params (
  paramid int(11) NOT NULL AUTO_INCREMENT,
  conditionid int(11) NOT NULL DEFAULT '0',
  setid int(11) NOT NULL DEFAULT '0',
  param_type char(1) NOT NULL DEFAULT '',
  param_id int(11) NOT NULL DEFAULT '0',
  param_arg char(1) NOT NULL DEFAULT '',
  param_qnty int(11) NOT NULL DEFAULT '0',
  param_promo char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (paramid),
  UNIQUE KEY pt (param_type,paramid),
  KEY args1 (param_type,param_id,param_arg),
  KEY conditionid (conditionid),
  KEY setid (setid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_conditions (
  conditionid int(11) NOT NULL AUTO_INCREMENT,
  offerid int(11) NOT NULL DEFAULT '0',
  condition_type char(1) NOT NULL DEFAULT '',
  amount_type char(1) NOT NULL DEFAULT '',
  amount_min decimal(12,2) NOT NULL DEFAULT '0.00',
  amount_max decimal(12,2) NOT NULL DEFAULT '0.00',
  condition_data text,
  provider int(11) NOT NULL DEFAULT '0',
  avail char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (conditionid),
  UNIQUE KEY c_type (offerid,condition_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_product_params (
  productid int(11) NOT NULL DEFAULT '0',
  sp_discount_avail char(1) NOT NULL DEFAULT 'N',
  bonus_points int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offer_product_sets (
  setid int(11) NOT NULL AUTO_INCREMENT,
  offerid int(11) NOT NULL DEFAULT '0',
  set_type char(1) NOT NULL DEFAULT '',
  cb_id int(11) NOT NULL DEFAULT '0',
  cb_type char(1) NOT NULL DEFAULT '0',
  `name` varchar(32) NOT NULL DEFAULT '',
  avail char(1) NOT NULL DEFAULT 'Y',
  appl_type char(1) NOT NULL DEFAULT 'I',
  PRIMARY KEY (setid),
  UNIQUE KEY set_item_id (setid,cb_id),
  KEY set_incl_type (cb_id,set_type,appl_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offers (
  offerid int(11) NOT NULL AUTO_INCREMENT,
  offer_name varchar(255) NOT NULL DEFAULT '',
  offer_start int(11) NOT NULL DEFAULT '0',
  offer_end int(11) NOT NULL DEFAULT '0',
  offer_avail char(1) NOT NULL DEFAULT 'N',
  provider int(11) NOT NULL DEFAULT '0',
  modified_time int(11) NOT NULL DEFAULT '0',
  show_short_promo char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (offerid),
  KEY offer_avail (offer_avail,offer_start,offer_end,provider)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_offers_lng (
  offerid int(11) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL DEFAULT '',
  promo_short text,
  promo_long text,
  promo_checkout text,
  promo_items_amount text,
  PRIMARY KEY (offerid,`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_old_passwords (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  `password` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  UNIQUE KEY lp (userid,`password`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_order_details (
  orderid int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  price decimal(12,2) NOT NULL DEFAULT '0.00',
  amount int(11) NOT NULL DEFAULT '0',
  provider int(11) NOT NULL DEFAULT '0',
  product_options text NOT NULL,
  extra_data text NOT NULL,
  itemid int(11) NOT NULL AUTO_INCREMENT,
  productcode varchar(32) NOT NULL DEFAULT '',
  product varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (itemid),
  KEY orderid (orderid),
  KEY productid (productid),
  KEY provider (provider),
  KEY productcode (productcode)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_order_details_stats (
  productid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  sum_amount int(11) NOT NULL DEFAULT '0' COMMENT 'SUM(amount) for this productid from all xcart_order_details.The same as xcart_product_sales_stats.sales_stats',
  `date` int(11) NOT NULL DEFAULT '0' COMMENT 'key date below is used in XCUpsellingProducts::deleteExpired()',
  PRIMARY KEY (productid,orderid,userid),
  KEY `date` (`date`),
  KEY os (orderid,`date`,sum_amount),
  KEY us (userid,`date`,sum_amount)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Copied from xcart_order_details with sum_amount for all orders[Add_to_cart_popup,Recommended_Products]';





CREATE TABLE xcart_order_extras (
  orderid int(11) NOT NULL DEFAULT '0',
  khash varchar(64) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (orderid,khash),
  UNIQUE KEY kvo (khash,`value`(32),orderid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_order_status_history (
  recid int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  date_time int(11) NOT NULL DEFAULT '0',
  details text NOT NULL,
  PRIMARY KEY (recid),
  KEY orderid (orderid,date_time)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_orders (
  orderid int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0' COMMENT 'Keep xcart_customers.id for registered customers,0 for anonymous',
  all_userid int(1) NOT NULL DEFAULT '0' COMMENT 'Keep xcart_customers.id for all users registered/anonymous.Duplicate xcart_orders.userid for registered customers.0 indicate that anonymous profile was not created in xcart_customers',
  membership varchar(255) NOT NULL DEFAULT '',
  total decimal(12,2) NOT NULL DEFAULT '0.00',
  giftcert_discount decimal(12,2) NOT NULL DEFAULT '0.00',
  giftcert_ids text NOT NULL,
  subtotal decimal(12,2) NOT NULL DEFAULT '0.00',
  discount decimal(12,2) NOT NULL DEFAULT '0.00',
  coupon varchar(32) NOT NULL DEFAULT '',
  coupon_discount decimal(12,2) NOT NULL DEFAULT '0.00',
  shippingid int(11) NOT NULL DEFAULT '0',
  shipping varchar(255) NOT NULL DEFAULT '',
  tracking varchar(64) NOT NULL DEFAULT '',
  shipping_cost decimal(12,2) NOT NULL DEFAULT '0.00',
  tax decimal(12,2) NOT NULL DEFAULT '0.00',
  taxes_applied text NOT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  `status` char(2) NOT NULL DEFAULT 'Q' COMMENT 'XC_XOSTAT_STATUS_LENGTH from config.php. The type must be the same as xcart_custom_order_statuses.code',
  payment_method varchar(128) NOT NULL DEFAULT '',
  flag char(1) NOT NULL DEFAULT 'N',
  notes text NOT NULL,
  details text NOT NULL,
  customer_notes text NOT NULL,
  title varchar(32) NOT NULL DEFAULT '',
  firstname varchar(128) NOT NULL DEFAULT '',
  lastname varchar(128) NOT NULL DEFAULT '',
  company varchar(255) NOT NULL DEFAULT '',
  b_title varchar(32) NOT NULL DEFAULT '',
  b_firstname varchar(128) NOT NULL DEFAULT '',
  b_lastname varchar(128) NOT NULL DEFAULT '',
  b_address varchar(255) NOT NULL DEFAULT '',
  b_city varchar(64) NOT NULL DEFAULT '',
  b_county varchar(32) NOT NULL DEFAULT '',
  b_state varchar(32) NOT NULL DEFAULT '',
  b_country char(2) NOT NULL DEFAULT '',
  b_zipcode varchar(32) NOT NULL DEFAULT '',
  b_zip4 varchar(4) NOT NULL DEFAULT '',
  b_phone varchar(32) NOT NULL DEFAULT '',
  b_fax varchar(32) NOT NULL DEFAULT '',
  s_title varchar(32) NOT NULL DEFAULT '',
  s_firstname varchar(128) NOT NULL DEFAULT '',
  s_lastname varchar(128) NOT NULL DEFAULT '',
  s_address varchar(255) NOT NULL DEFAULT '',
  s_city varchar(255) NOT NULL DEFAULT '',
  s_county varchar(32) NOT NULL DEFAULT '',
  s_state varchar(32) NOT NULL DEFAULT '',
  s_country char(2) NOT NULL DEFAULT '',
  s_zipcode varchar(32) NOT NULL DEFAULT '',
  s_phone varchar(32) NOT NULL DEFAULT '',
  s_fax varchar(32) NOT NULL DEFAULT '',
  s_zip4 varchar(4) NOT NULL DEFAULT '',
  url varchar(128) NOT NULL DEFAULT '',
  email varchar(128) NOT NULL DEFAULT '',
  `language` char(2) NOT NULL DEFAULT 'en',
  clickid int(11) NOT NULL DEFAULT '0',
  extra mediumtext NOT NULL,
  membershipid int(11) NOT NULL DEFAULT '0',
  paymentid int(11) NOT NULL DEFAULT '0',
  payment_surcharge decimal(12,2) NOT NULL DEFAULT '0.00',
  tax_number varchar(50) NOT NULL DEFAULT '',
  tax_exempt char(1) NOT NULL DEFAULT 'N',
  init_total decimal(12,2) NOT NULL DEFAULT '0.00',
  access_key varchar(16) NOT NULL DEFAULT '',
  klarna_order_status char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (orderid),
  UNIQUE KEY odsp (orderid,`date`,`status`,paymentid),
  UNIQUE KEY ospd (orderid,`status`,paymentid,`date`),
  KEY access_key (access_key),
  KEY all_userid (all_userid),
  KEY b_country (b_country),
  KEY b_state (b_state),
  KEY clickid (clickid),
  KEY order_date (`date`),
  KEY paymentid (paymentid),
  KEY s_country (s_country),
  KEY s_state (s_state),
  KEY shippingid (shippingid),
  KEY es (email(64),`status`),
  KEY us (userid,`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_packages_cache (
  md5_args binary(32) NOT NULL COMMENT 'func_get_packages->md5()',
  sessid binary(32) NOT NULL,
  packages blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (sessid,md5_args)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_pages (
  pageid int(11) NOT NULL AUTO_INCREMENT,
  filename varchar(255) NOT NULL DEFAULT '',
  title varchar(255) NOT NULL DEFAULT '',
  `level` char(1) NOT NULL DEFAULT 'E',
  orderby int(11) NOT NULL DEFAULT '0',
  active char(1) NOT NULL DEFAULT 'Y',
  `language` char(2) NOT NULL DEFAULT '',
  show_in_menu char(1) NOT NULL DEFAULT '',
  meta_description varchar(255) NOT NULL DEFAULT '',
  meta_keywords varchar(255) NOT NULL DEFAULT '',
  title_tag varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (pageid),
  UNIQUE KEY filename_pageid (filename,pageid),
  KEY orderby (`level`,orderby,title)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_adv_campaigns (
  campaignid int(11) NOT NULL AUTO_INCREMENT,
  campaign varchar(128) NOT NULL DEFAULT '',
  per_visit decimal(12,2) NOT NULL DEFAULT '0.00',
  per_period decimal(12,2) NOT NULL DEFAULT '0.00',
  start_period int(11) NOT NULL DEFAULT '0',
  end_period int(11) NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `data` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (campaignid),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_adv_clicks (
  campaignid int(11) NOT NULL DEFAULT '0',
  add_date int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (campaignid,add_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_adv_orders (
  campaignid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (campaignid,orderid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_banners (
  bannerid int(11) NOT NULL AUTO_INCREMENT,
  banner varchar(128) NOT NULL DEFAULT '',
  body mediumblob NOT NULL,
  avail char(1) NOT NULL DEFAULT 'Y',
  is_image char(1) NOT NULL DEFAULT 'Y',
  is_name char(1) NOT NULL DEFAULT 'Y',
  is_descr char(1) NOT NULL DEFAULT 'Y',
  is_add char(1) NOT NULL DEFAULT 'Y',
  banner_type char(1) NOT NULL DEFAULT 'T',
  open_blank char(1) NOT NULL DEFAULT 'Y',
  legend text NOT NULL,
  alt text NOT NULL,
  direction char(1) NOT NULL DEFAULT 'D',
  banner_x int(11) NOT NULL DEFAULT '0',
  banner_y int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (bannerid),
  KEY userid (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_clicks (
  userid int(11) NOT NULL DEFAULT '0',
  add_date int(11) NOT NULL DEFAULT '0',
  bannerid int(11) NOT NULL DEFAULT '0',
  target char(1) NOT NULL DEFAULT '',
  targetid int(11) NOT NULL DEFAULT '0',
  referer varchar(255) NOT NULL DEFAULT '',
  clickid int(11) NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (clickid),
  KEY userid (userid),
  KEY add_date (add_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_commissions (
  userid int(11) NOT NULL DEFAULT '0',
  plan_id int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (userid),
  UNIQUE KEY userid_plan_id (userid,plan_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_payment (
  payment_id int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  commissions decimal(12,2) NOT NULL DEFAULT '0.00',
  paid char(1) NOT NULL DEFAULT 'N',
  add_date int(11) NOT NULL DEFAULT '0',
  affiliate int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (payment_id),
  KEY ua (userid,add_date),
  KEY orderid (orderid),
  KEY affiliate (affiliate)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_plans (
  plan_id int(11) NOT NULL AUTO_INCREMENT,
  plan_title varchar(64) NOT NULL DEFAULT '',
  `status` char(1) NOT NULL DEFAULT 'A',
  min_paid decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (plan_id),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_plans_commissions (
  plan_id int(11) NOT NULL DEFAULT '0',
  commission decimal(12,2) NOT NULL DEFAULT '0.00',
  commission_type enum('$','%') NOT NULL DEFAULT '%',
  item_id int(11) NOT NULL DEFAULT '0',
  item_type char(1) NOT NULL DEFAULT 'A',
  PRIMARY KEY (plan_id,item_id,item_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_product_commissions (
  itemid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  product_commission decimal(12,2) NOT NULL DEFAULT '0.00',
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (itemid,orderid,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_tier_commissions (
  plan_id int(11) NOT NULL DEFAULT '0',
  `level` int(2) NOT NULL DEFAULT '0',
  commission decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (plan_id,`level`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_partner_views (
  userid int(11) NOT NULL DEFAULT '0',
  add_date int(11) NOT NULL DEFAULT '0',
  bannerid int(11) NOT NULL DEFAULT '0',
  target char(1) NOT NULL DEFAULT '',
  targetid int(11) NOT NULL DEFAULT '0',
  KEY userid (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_payment_countries (
  processor varchar(255) NOT NULL,
  payment_type varchar(32) NOT NULL,
  country_code char(2) NOT NULL,
  PRIMARY KEY (processor,country_code,payment_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_payment_methods (
  paymentid int(11) NOT NULL AUTO_INCREMENT,
  payment_method varchar(128) NOT NULL DEFAULT '',
  payment_details varchar(255) NOT NULL DEFAULT '',
  payment_template varchar(128) NOT NULL DEFAULT '',
  payment_script varchar(128) NOT NULL DEFAULT '',
  protocol varchar(6) NOT NULL DEFAULT '' COMMENT 'Unused, left for backward compatibility',
  orderby int(11) NOT NULL DEFAULT '0',
  active char(1) NOT NULL DEFAULT 'Y',
  is_cod char(1) NOT NULL DEFAULT '',
  af_check char(1) NOT NULL DEFAULT 'Y',
  processor_file varchar(255) NOT NULL DEFAULT '',
  surcharge decimal(12,2) NOT NULL DEFAULT '0.00',
  surcharge_type char(1) NOT NULL DEFAULT '$',
  use_recharges char(1) NOT NULL DEFAULT 'N' COMMENT 'For XPayments tokenization system',
  PRIMARY KEY (paymentid),
  KEY orderby (orderby),
  KEY pa (processor_file,active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_pmethod_memberships (
  paymentid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (paymentid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_pricing (
  priceid int(11) NOT NULL AUTO_INCREMENT,
  productid int(11) NOT NULL DEFAULT '0',
  quantity int(11) NOT NULL DEFAULT '0',
  price decimal(12,2) NOT NULL DEFAULT '0.00',
  variantid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (priceid),
  KEY pqmv (productid,quantity,membershipid,variantid),
  KEY vqm (variantid,quantity,membershipid),
  KEY vqpm (variantid,quantity,productid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='vqpm is used in func_build_quick_prices.pqmv cannot be UNIQUE due to wholesale import feature bt#0078387,0071339';





CREATE TABLE xcart_product_bookmarks (
  productid int(11) NOT NULL DEFAULT '0',
  add_date int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  UNIQUE KEY productid (productid,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_links (
  productid1 int(11) NOT NULL DEFAULT '0',
  productid2 int(11) NOT NULL DEFAULT '0',
  orderby int(11) NOT NULL DEFAULT '0',
  KEY productid2 (productid2),
  KEY productid1 (productid1),
  KEY orderby (orderby)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_memberships (
  productid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (productid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_options_ex (
  optionid int(11) NOT NULL DEFAULT '0',
  exceptionid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (optionid,exceptionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_options_js (
  productid int(11) NOT NULL DEFAULT '0',
  javascript_code text,
  PRIMARY KEY (productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_options_lng (
  `code` char(2) NOT NULL DEFAULT 'en',
  optionid int(11) NOT NULL DEFAULT '0',
  option_name varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`code`,optionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_reviews (
  review_id int(11) NOT NULL AUTO_INCREMENT,
  remote_ip varchar(15) NOT NULL DEFAULT '',
  email varchar(128) NOT NULL DEFAULT '',
  message text NOT NULL,
  productid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (review_id),
  KEY productid (productid),
  KEY remote_ip (remote_ip)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_rnd_keys (
  productid int(11) NOT NULL DEFAULT '0',
  rnd_key int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (productid,rnd_key),
  KEY rnd_key (rnd_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_sales_stats (
  productid int(11) NOT NULL DEFAULT '0',
  sales_stats int(11) NOT NULL DEFAULT '0' COMMENT 'SUM(amount) for this productid from all xcart_order_details.The same as xcart_order_details.sum_amount',
  PRIMARY KEY (productid),
  UNIQUE KEY ps (productid,sales_stats),
  UNIQUE KEY sp (sales_stats,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Keep sales_stats for the product. Using for sort by Sales and for bestsellers section';





CREATE TABLE xcart_product_taxes (
  productid int(11) NOT NULL DEFAULT '0',
  taxid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (productid,taxid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_product_votes (
  vote_id int(11) NOT NULL AUTO_INCREMENT,
  remote_ip varchar(15) NOT NULL DEFAULT '',
  vote_value int(1) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (vote_id),
  KEY remote_ip (remote_ip),
  KEY productid (productid),
  KEY rp (remote_ip,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products (
  productid int(11) NOT NULL AUTO_INCREMENT,
  productcode varchar(32) NOT NULL DEFAULT '',
  provider int(11) NOT NULL DEFAULT '0',
  distribution varchar(255) NOT NULL DEFAULT '',
  weight decimal(12,2) NOT NULL DEFAULT '0.00',
  list_price decimal(12,2) NOT NULL DEFAULT '0.00',
  avail int(11) NOT NULL DEFAULT '0',
  rating int(11) NOT NULL DEFAULT '0',
  forsale char(1) NOT NULL DEFAULT 'Y',
  add_date int(11) NOT NULL DEFAULT '0',
  shipping_freight decimal(12,2) NOT NULL DEFAULT '0.00',
  free_shipping char(1) NOT NULL DEFAULT 'N',
  discount_avail char(1) NOT NULL DEFAULT 'Y',
  min_amount int(11) NOT NULL DEFAULT '1',
  length decimal(12,2) NOT NULL DEFAULT '0.00',
  width decimal(12,2) NOT NULL DEFAULT '0.00',
  height decimal(12,2) NOT NULL DEFAULT '0.00',
  low_avail_limit int(11) NOT NULL DEFAULT '10',
  free_tax char(1) NOT NULL DEFAULT 'N',
  product_type char(1) NOT NULL DEFAULT 'N',
  manufacturerid int(11) NOT NULL DEFAULT '0',
  return_time int(11) NOT NULL DEFAULT '0',
  meta_description varchar(255) NOT NULL DEFAULT '',
  meta_keywords varchar(255) NOT NULL DEFAULT '',
  small_item char(1) NOT NULL DEFAULT 'N',
  separate_box char(1) NOT NULL DEFAULT 'N',
  items_per_box int(11) NOT NULL DEFAULT '1',
  title_tag varchar(255) NOT NULL DEFAULT '',
  taxcloud_tic varchar(50) NOT NULL DEFAULT '00000',
  avatax_tax_code varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (productid),
  UNIQUE KEY productcode (productcode,provider),
  UNIQUE KEY fia (forsale,productid,avail),
  UNIQUE KEY ppp (productcode,provider,productid),
  KEY rating (rating),
  KEY add_date (add_date),
  KEY provider (provider),
  KEY avail (avail),
  KEY manufacturerid (manufacturerid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products_categories (
  categoryid int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  main char(1) NOT NULL DEFAULT 'N',
  orderby int(11) NOT NULL DEFAULT '0',
  avail char(1) CHARACTER SET latin1 NOT NULL DEFAULT 'Y' COMMENT 'For performance purpose;Must duplicate avail field from xcart_categories',
  PRIMARY KEY (categoryid,productid),
  UNIQUE KEY cpm (categoryid,productid,main),
  KEY ca (categoryid,avail),
  KEY pa (productid,avail),
  KEY ma (main,avail),
  KEY co (categoryid,orderby),
  KEY pm (productid,main),
  KEY po (productid,orderby)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products_lng_de (
  productid int(11) NOT NULL DEFAULT '0',
  product varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  fulldescr text NOT NULL,
  keywords varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (productid),
  UNIQUE KEY pp (product,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products_lng_en (
  productid int(11) NOT NULL DEFAULT '0',
  product varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  fulldescr text NOT NULL,
  keywords varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (productid),
  UNIQUE KEY pp (product,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products_lng_fr (
  productid int(11) NOT NULL DEFAULT '0',
  product varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  fulldescr text NOT NULL,
  keywords varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (productid),
  UNIQUE KEY pp (product,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_products_lng_sv (
  productid int(11) NOT NULL DEFAULT '0',
  product varchar(255) NOT NULL DEFAULT '',
  descr text NOT NULL,
  fulldescr text NOT NULL,
  keywords varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (productid),
  UNIQUE KEY pp (product,productid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_provider_commissions (
  orderid int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  commission_date int(11) NOT NULL DEFAULT '0',
  paid char(1) NOT NULL DEFAULT '',
  note tinytext NOT NULL,
  add_date int(11) NOT NULL DEFAULT '0',
  commissions decimal(12,2) NOT NULL DEFAULT '0.00',
  paid_commissions decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (orderid),
  KEY userid (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_provider_product_commissions (
  itemid int(11) NOT NULL DEFAULT '0',
  orderid int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  product_commission decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (itemid,orderid,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_quick_flags (
  productid int(11) NOT NULL DEFAULT '0',
  is_variants char(1) NOT NULL DEFAULT '',
  is_product_options char(1) NOT NULL DEFAULT '',
  is_taxes char(1) NOT NULL DEFAULT '',
  image_path_T varchar(255) DEFAULT NULL,
  PRIMARY KEY (productid),
  UNIQUE KEY pi (productid,image_path_T),
  KEY vpt (is_variants,is_product_options,is_taxes)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_quick_prices (
  productid int(11) NOT NULL DEFAULT '0',
  priceid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  variantid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (productid,membershipid),
  UNIQUE KEY pmp (productid,membershipid,priceid),
  KEY pp (productid,priceid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_register_field_address_values (
  fieldid int(11) NOT NULL DEFAULT '0',
  addressid int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (addressid,fieldid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_register_field_values (
  fieldid int(11) NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  `value` text NOT NULL,
  PRIMARY KEY (fieldid,userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_register_fields (
  fieldid int(11) NOT NULL AUTO_INCREMENT,
  field varchar(255) NOT NULL DEFAULT '',
  `type` char(1) NOT NULL DEFAULT 'T',
  variants text NOT NULL,
  def varchar(255) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  section char(1) NOT NULL DEFAULT 'A',
  avail varchar(5) NOT NULL DEFAULT '',
  required varchar(5) NOT NULL DEFAULT '',
  PRIMARY KEY (fieldid),
  KEY orderby (orderby),
  KEY avail (avail),
  KEY required (required)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_reset_passwords (
  userid int(11) NOT NULL DEFAULT '0',
  password_reset_key varchar(255) NOT NULL DEFAULT '',
  password_reset_key_date int(11) NOT NULL DEFAULT '0',
  signature char(40) NOT NULL DEFAULT '' COMMENT 'Used to validate password_reset_key',
  PRIMARY KEY (userid),
  UNIQUE KEY password_reset_key (password_reset_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_returns (
  returnid int(11) NOT NULL AUTO_INCREMENT,
  itemid int(11) NOT NULL DEFAULT '0',
  amount int(11) NOT NULL DEFAULT '0',
  returned_amount int(11) NOT NULL DEFAULT '0',
  `status` char(1) NOT NULL DEFAULT 'R',
  reason int(11) NOT NULL DEFAULT '0',
  `action` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `date` int(11) NOT NULL DEFAULT '0',
  credit varchar(16) NOT NULL DEFAULT '',
  creator char(1) NOT NULL DEFAULT 'C',
  PRIMARY KEY (returnid),
  KEY itemid (itemid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_secure3d_data (
  tranid varchar(32) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  get_data mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  sessid binary(32) NOT NULL,
  session_data blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  form_data blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  form_url text NOT NULL,
  return_data mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  processor varchar(255) NOT NULL DEFAULT '',
  verify_funcname varchar(255) NOT NULL DEFAULT '',
  validate_funcname varchar(255) NOT NULL DEFAULT '',
  md varchar(255) NOT NULL DEFAULT '',
  no_iframe char(1) NOT NULL DEFAULT '',
  service_data blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (tranid),
  KEY sessid (sessid),
  KEY md (md)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_seller_addresses (
  userid int(11) NOT NULL DEFAULT '0',
  address varchar(255) NOT NULL DEFAULT '',
  city varchar(64) NOT NULL DEFAULT '',
  state varchar(32) NOT NULL DEFAULT '',
  country char(2) NOT NULL DEFAULT '',
  zipcode varchar(32) NOT NULL DEFAULT '',
  arb_id text NOT NULL,
  arb_password text NOT NULL,
  arb_account text NOT NULL,
  arb_shipping_key text NOT NULL,
  arb_shipping_key_intl text NOT NULL,
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_session_history (
  ip binary(32) NOT NULL COMMENT 'md5 is used to support IPv6 for mysql as INET6_ATON is added in MySQL 5.6.3 only',
  `host` varchar(255) NOT NULL DEFAULT '',
  xid binary(32) NOT NULL,
  dest_xid binary(32) NOT NULL,
  PRIMARY KEY (ip,`host`),
  KEY ihx (ip,`host`,xid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_session_unknown_sid (
  sessid binary(32) NOT NULL,
  ip binary(32) NOT NULL COMMENT 'md5 is used to support IPv6 for mysql as INET6_ATON is added in MySQL 5.6.3 only',
  cnt int(11) NOT NULL DEFAULT '0',
  expiry_date int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (ip,sessid),
  KEY expiry_date (expiry_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_sessions_data (
  sessid binary(32) NOT NULL,
  `start` int(11) NOT NULL DEFAULT '0',
  expiry int(11) NOT NULL DEFAULT '0',
  `data` mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  signature char(40) NOT NULL DEFAULT '' COMMENT 'Used to validate admin sessions',
  PRIMARY KEY (sessid),
  UNIQUE KEY expiry_sid (expiry,sessid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_setup_images (
  itype char(1) NOT NULL DEFAULT '',
  location char(2) NOT NULL DEFAULT 'DB',
  save_url char(1) NOT NULL DEFAULT '',
  size_limit int(11) NOT NULL DEFAULT '0',
  md5_check char(32) NOT NULL DEFAULT '',
  default_image varchar(255) NOT NULL DEFAULT './default_image.gif',
  UNIQUE KEY itype (itype)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_shipping (
  shippingid int(11) NOT NULL AUTO_INCREMENT,
  shipping varchar(255) NOT NULL DEFAULT '',
  shipping_time varchar(128) NOT NULL DEFAULT '',
  destination char(1) NOT NULL DEFAULT 'I',
  `code` varchar(32) NOT NULL DEFAULT '',
  subcode varchar(32) NOT NULL DEFAULT '',
  orderby int(11) NOT NULL DEFAULT '0',
  active char(1) NOT NULL DEFAULT 'Y',
  intershipper_code varchar(32) NOT NULL DEFAULT '',
  weight_min decimal(12,2) NOT NULL DEFAULT '0.00',
  weight_limit decimal(12,2) NOT NULL DEFAULT '0.00',
  service_code int(11) NOT NULL DEFAULT '0',
  is_cod char(1) NOT NULL DEFAULT '',
  is_new char(1) NOT NULL DEFAULT '',
  amazon_service varchar(32) NOT NULL DEFAULT 'Standard',
  gc_shipping varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (shippingid),
  KEY `code` (`code`),
  KEY orderby (orderby),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_shipping_cache (
  md5_request binary(32) NOT NULL,
  sessid binary(32) NOT NULL,
  response blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (sessid,md5_request)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_shipping_labels (
  labelid int(11) NOT NULL AUTO_INCREMENT,
  orderid int(11) NOT NULL DEFAULT '0',
  mime_type varchar(80) NOT NULL DEFAULT '',
  label mediumblob NOT NULL,
  `error` text NOT NULL,
  descr varchar(255) NOT NULL DEFAULT '',
  packages_number int(11) NOT NULL DEFAULT '0',
  is_first char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (labelid),
  KEY orderid (orderid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_shipping_options (
  carrier varchar(32) NOT NULL DEFAULT '',
  param00 blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  param01 varchar(128) NOT NULL DEFAULT '',
  param02 varchar(128) NOT NULL DEFAULT '',
  param03 varchar(128) NOT NULL DEFAULT '',
  param04 varchar(128) NOT NULL DEFAULT '',
  param05 varchar(128) NOT NULL DEFAULT '',
  param06 varchar(128) NOT NULL DEFAULT '',
  param07 varchar(128) NOT NULL DEFAULT '',
  param08 varchar(128) NOT NULL DEFAULT '',
  param09 varchar(128) NOT NULL DEFAULT '',
  param10 varchar(128) NOT NULL DEFAULT '',
  param11 varchar(128) NOT NULL DEFAULT '',
  currency_rate decimal(12,2) NOT NULL DEFAULT '1.00',
  PRIMARY KEY (carrier)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_shipping_rates (
  rateid int(11) NOT NULL AUTO_INCREMENT,
  shippingid int(11) NOT NULL DEFAULT '0',
  zoneid int(11) NOT NULL DEFAULT '0',
  minweight decimal(12,2) NOT NULL DEFAULT '0.00',
  maxweight decimal(12,2) NOT NULL DEFAULT '1000000.00',
  mintotal decimal(12,2) NOT NULL DEFAULT '0.00',
  maxtotal decimal(12,2) NOT NULL DEFAULT '0.00',
  rate decimal(12,2) NOT NULL DEFAULT '0.00',
  item_rate decimal(12,2) NOT NULL DEFAULT '0.00',
  weight_rate decimal(12,2) NOT NULL DEFAULT '0.00',
  rate_p decimal(12,2) NOT NULL DEFAULT '0.00',
  provider int(11) NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT 'D',
  apply_to char(6) NOT NULL DEFAULT 'DST',
  PRIMARY KEY (rateid),
  KEY provider (provider),
  KEY shippingid (shippingid),
  KEY maxweight (maxweight),
  KEY ztp (zoneid,`type`,provider)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='ztp is used in func_get_customer_zone_ship';





CREATE TABLE xcart_sitemap_extra (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_split_checkout (
  orderids varchar(255) NOT NULL DEFAULT '',
  `data` mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (orderids)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_states (
  stateid int(11) NOT NULL AUTO_INCREMENT,
  state varchar(64) NOT NULL DEFAULT '',
  `code` varchar(16) NOT NULL DEFAULT '' COMMENT 'Has child key in xcart_states_districts',
  country_code char(2) NOT NULL DEFAULT '' COMMENT 'Has child key in xcart_states_districts',
  PRIMARY KEY (stateid),
  UNIQUE KEY `code` (country_code,`code`),
  KEY state (state)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_states_districts (
  `code` varchar(16) NOT NULL DEFAULT '' COMMENT 'Has parent key in xcart_states table',
  country_code char(2) NOT NULL DEFAULT '' COMMENT 'Has parent key in xcart_states table',
  code_district varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (country_code,code_district,`code`(3))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Auxiliary table to search a state by postcode_district';





CREATE TABLE xcart_stats_adaptive (
  platform varchar(64) NOT NULL DEFAULT '',
  browser varchar(10) NOT NULL DEFAULT '',
  version varchar(16) NOT NULL DEFAULT '',
  java char(1) NOT NULL DEFAULT 'Y',
  js char(1) NOT NULL DEFAULT 'Y',
  count int(11) NOT NULL DEFAULT '1',
  cookie char(1) NOT NULL DEFAULT '',
  screen_x int(11) NOT NULL DEFAULT '0',
  screen_y int(11) NOT NULL DEFAULT '0',
  last_date int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (platform,browser,java,js,version,cookie,screen_x,screen_y),
  KEY last_date (last_date)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_stats_search (
  swordid int(11) NOT NULL AUTO_INCREMENT,
  search varchar(255) NOT NULL DEFAULT '',
  `date` int(11) NOT NULL DEFAULT '0',
  count int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (swordid),
  UNIQUE KEY search (search),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_stop_list (
  octet1 int(3) NOT NULL DEFAULT '0',
  octet2 int(3) NOT NULL DEFAULT '0',
  octet3 int(3) NOT NULL DEFAULT '0',
  octet4 int(3) NOT NULL DEFAULT '0',
  ip varchar(15) NOT NULL DEFAULT '',
  reason char(1) NOT NULL DEFAULT 'M',
  `date` int(11) NOT NULL DEFAULT '0',
  ipid int(11) NOT NULL AUTO_INCREMENT,
  ip_type char(1) NOT NULL DEFAULT 'B',
  PRIMARY KEY (ipid),
  UNIQUE KEY octet1 (octet1,octet2,octet3,octet4),
  KEY ip (ip)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_answers (
  answerid int(11) NOT NULL AUTO_INCREMENT,
  questionid int(11) NOT NULL DEFAULT '0',
  textbox_type char(1) NOT NULL DEFAULT 'N',
  orderby int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (answerid),
  KEY questionid (questionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_events (
  surveyid int(11) NOT NULL DEFAULT '0',
  param char(1) NOT NULL DEFAULT '',
  id varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (surveyid,param,id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_maillist (
  surveyid int(11) NOT NULL DEFAULT '0',
  email varchar(128) NOT NULL DEFAULT '',
  userid int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  access_key varchar(32) NOT NULL DEFAULT '',
  sent_date int(11) NOT NULL DEFAULT '0',
  complete_date int(11) NOT NULL DEFAULT '0',
  delay_date int(11) NOT NULL DEFAULT '0',
  as_result varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY se (surveyid,email),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_questions (
  questionid int(11) NOT NULL AUTO_INCREMENT,
  surveyid int(11) NOT NULL DEFAULT '0',
  answers_type char(1) NOT NULL DEFAULT 'R',
  required char(1) NOT NULL DEFAULT '',
  col int(3) NOT NULL DEFAULT '0',
  orderby int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (questionid),
  KEY surveyid (surveyid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_result_answers (
  sresultid int(11) NOT NULL DEFAULT '0',
  questionid int(11) NOT NULL DEFAULT '0',
  answerid int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  UNIQUE KEY main (sresultid,questionid,answerid),
  KEY qa (questionid,answerid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_survey_results (
  sresultid int(11) NOT NULL AUTO_INCREMENT,
  surveyid int(11) NOT NULL DEFAULT '0',
  `date` int(11) NOT NULL DEFAULT '0',
  ip int(11) unsigned NOT NULL DEFAULT '0',
  userid int(11) NOT NULL DEFAULT '0',
  `code` char(2) NOT NULL DEFAULT '',
  from_mail char(1) NOT NULL DEFAULT '',
  completed char(1) NOT NULL DEFAULT '',
  as_result varchar(32) NOT NULL DEFAULT '',
  PRIMARY KEY (sresultid),
  KEY sil (surveyid,ip,userid),
  KEY sc (surveyid,completed)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_surveys (
  surveyid int(11) NOT NULL AUTO_INCREMENT,
  survey_type char(1) NOT NULL DEFAULT 'D',
  created_date int(11) NOT NULL DEFAULT '0',
  valid_from_date int(11) NOT NULL DEFAULT '0',
  expires_data int(11) NOT NULL DEFAULT '0',
  publish_results char(1) NOT NULL DEFAULT '',
  display_on_frontpage char(1) NOT NULL DEFAULT '',
  event_type char(3) NOT NULL DEFAULT '',
  event_logic char(1) NOT NULL DEFAULT 'O',
  orderby int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (surveyid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_tax_rate_memberships (
  rateid int(11) NOT NULL DEFAULT '0',
  membershipid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (rateid,membershipid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_tax_rates (
  rateid int(11) NOT NULL AUTO_INCREMENT,
  taxid int(11) NOT NULL DEFAULT '0',
  zoneid int(11) NOT NULL DEFAULT '0',
  formula varchar(255) NOT NULL DEFAULT '',
  rate_value decimal(12,3) NOT NULL DEFAULT '0.000',
  rate_type char(1) NOT NULL DEFAULT '',
  provider int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (rateid),
  KEY provider (provider),
  KEY tax_rate (taxid,zoneid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_taxcloud_cache (
  sessid binary(32) NOT NULL,
  cell_key varchar(128) NOT NULL DEFAULT '',
  cell_value mediumblob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (sessid,cell_key)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_taxes (
  taxid int(11) NOT NULL AUTO_INCREMENT,
  tax_name varchar(10) NOT NULL DEFAULT '',
  formula varchar(255) NOT NULL DEFAULT '',
  address_type char(1) NOT NULL DEFAULT 'S',
  active char(1) NOT NULL DEFAULT 'N',
  price_includes_tax char(1) NOT NULL DEFAULT 'N',
  display_including_tax char(1) NOT NULL DEFAULT 'N',
  display_info char(1) NOT NULL DEFAULT '',
  regnumber varchar(255) NOT NULL DEFAULT '',
  priority int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (taxid),
  UNIQUE KEY tax_name (tax_name),
  KEY func_amazon_get_tax_options (display_including_tax),
  KEY active (active)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_temporary_data (
  id varchar(32) NOT NULL DEFAULT '',
  `data` blob COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  `expire` int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY `expire` (`expire`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_titles (
  titleid int(11) NOT NULL AUTO_INCREMENT,
  title varchar(64) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  orderby int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (titleid),
  UNIQUE KEY ia (titleid,active),
  KEY title (title),
  KEY orderby (orderby)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_users_online (
  sessid binary(32) NOT NULL,
  usertype char(1) NOT NULL DEFAULT '',
  is_registered char(1) NOT NULL DEFAULT '',
  expiry int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (sessid),
  KEY usertype (usertype),
  KEY ui (usertype,is_registered),
  KEY expiry (expiry)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_variant_backups (
  optionid int(11) NOT NULL DEFAULT '0',
  variantid int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  `data` blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (optionid,variantid),
  KEY variantid (variantid),
  KEY po (productid,optionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Auxiliary table to keep data for disabled variants';





CREATE TABLE xcart_variant_items (
  optionid int(11) NOT NULL DEFAULT '0',
  variantid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (optionid,variantid),
  UNIQUE KEY vo (variantid,optionid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_variants (
  variantid int(11) NOT NULL DEFAULT '0' COMMENT 'Use XCVariantsChange::addVariant to add a row;AUTO_INCREMENT is removed for performance purpose',
  productid int(11) NOT NULL DEFAULT '0',
  avail int(11) NOT NULL DEFAULT '0',
  weight decimal(12,2) NOT NULL DEFAULT '0.00',
  productcode varchar(32) NOT NULL DEFAULT '0' COMMENT 'Cannot be unique as used by different providers and may be duplicated in XCVariantsChange::repairIntegrity()',
  def char(1) NOT NULL DEFAULT '',
  is_product_row tinyint(1) NOT NULL DEFAULT '0' COMMENT 'For performance;For replace left join to inner join;Must duplicate product data;Only one such row is allowed per productid;def is 0 for the row',
  PRIMARY KEY (productid,variantid,is_product_row),
  UNIQUE KEY pp (productcode,productid),
  KEY pi (productid,is_product_row),
  KEY vi (variantid,is_product_row)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_wishlist (
  wishlistid int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  productid int(11) NOT NULL DEFAULT '0',
  amount int(11) NOT NULL DEFAULT '0',
  amount_purchased int(11) NOT NULL DEFAULT '0',
  `options` blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  event_id int(11) NOT NULL DEFAULT '0',
  object blob NOT NULL COMMENT 'binary-safe blob is used to avoid data corruption with php unserialize function',
  PRIMARY KEY (wishlistid),
  KEY userid_product (userid,productid),
  KEY `event` (event_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_xmlmap_extra (
  id int(11) NOT NULL AUTO_INCREMENT,
  url varchar(255) NOT NULL DEFAULT '',
  active char(1) NOT NULL DEFAULT 'Y',
  PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_xmlmap_lastmod (
  id int(11) NOT NULL DEFAULT '0',
  `type` char(1) NOT NULL DEFAULT '',
  `date` char(25) NOT NULL DEFAULT '',
  UNIQUE KEY it (id,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_xmonitoring_fsystem (
  filename varchar(255) NOT NULL,
  fowner varchar(50) NOT NULL,
  fperms char(6) NOT NULL,
  fmtime int(11) unsigned NOT NULL,
  fcontent mediumtext NOT NULL,
  fsign char(40) NOT NULL,
  signature char(40) NOT NULL,
  last_updated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (filename)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_xpc_saved_cards (
  id int(11) NOT NULL AUTO_INCREMENT,
  userid int(11) NOT NULL DEFAULT '0',
  card_num varchar(32) NOT NULL DEFAULT '',
  card_type varchar(32) NOT NULL DEFAULT '',
  xpc_txnid varchar(255) NOT NULL DEFAULT '',
  orderid int(11) NOT NULL DEFAULT '0',
  paymentid int(11) NOT NULL DEFAULT '0',
  date_added int(11) NOT NULL DEFAULT '0',
  is_default char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (id),
  UNIQUE KEY useridid (userid,id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='X-Payments Connector: saved credit card tokens for customers';





CREATE TABLE xcart_xpc_saved_cards_migrated (
  userid int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (userid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='X-Payments Connector: indicates if customer has migrated from old-style saved cards entries';





CREATE TABLE xcart_zone_element (
  zoneid int(11) NOT NULL DEFAULT '0',
  field varchar(36) NOT NULL DEFAULT '',
  field_type char(1) NOT NULL DEFAULT '',
  PRIMARY KEY (zoneid,field,field_type),
  KEY field (field_type,field),
  KEY field_type (zoneid,field_type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;





CREATE TABLE xcart_zones (
  zoneid int(11) NOT NULL AUTO_INCREMENT,
  zone_name varchar(255) NOT NULL DEFAULT '',
  zone_cache varchar(255) NOT NULL DEFAULT '',
  provider int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (zoneid),
  KEY zone_name (provider,zone_name)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


