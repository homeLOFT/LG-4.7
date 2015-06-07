CREATE TABLE xcart_abcr_abandoned_carts (
    email varchar(255) NOT NULL default '' PRIMARY KEY,
    cart_hash varchar(255) NOT NULL default '',
    userid int(11) NOT NULL default 0,
    customer_info text NOT NULL,
    abandoned_cart text NOT NULL,
    coupon varchar(255) NOT NULL default '',
    time int(11) NOT NULL default 0
);

CREATE TABLE xcart_abcr_notifications (
    email varchar(255) NOT NULL default '',
    coupon varchar(255) NOT NULL default '',
    time int(11) NOT NULL default 0
);

CREATE TABLE xcart_abcr_order_statistic (
    orderid int(11) NOT NULL default 0
);

INSERT INTO xcart_modules SET module_name='Abandoned_Cart_Reminder', module_descr='This module allows to set up notifications about abandoned carts, so customers could get back to your store and complete the purchase', active='N', author='qtmsoft', tags='marketing,userexp';
INSERT INTO xcart_config VALUES ('abcr_work_mode','Handle sending out notifications manually or automatically?','manual','Abandoned_Cart_Reminder',0,'selector','','manual:Manually\nauto:Automatically','','');
INSERT INTO xcart_config VALUES ('abcr_notify_after','After how many hours customer should be notified of abandoned cart','12','Abandoned_Cart_Reminder',1,'numeric','','','uintz','');
INSERT INTO xcart_config VALUES ('abcr_notification_count','How many notifications should be sent','2','Abandoned_Cart_Reminder',2,'numeric','','','uint','');
INSERT INTO xcart_config VALUES ('abcr_notification_delay','Delay between notifications (in hours)','12','Abandoned_Cart_Reminder',3,'numeric','','','uint','');
INSERT INTO xcart_config VALUES ('abcr_coupon_type','Type of discount coupon that should be attached to notifications','no','Abandoned_Cart_Reminder',4,'selector','','no:None\nabsolute:$ off\npercent:% off\nfree_ship:Free shipping','','');
INSERT INTO xcart_config VALUES ('abcr_coupon_value','Discount coupon value','','Abandoned_Cart_Reminder',5,'numeric','','','udouble','');
INSERT INTO xcart_config VALUES ('abcr_expire_after','Period of abandoned cart expiration (Number of days after which email notifications to customer will no longer be sent and the coupon associated with the abandoned cart will become invalid)','7','Abandoned_Cart_Reminder',6,'numeric','','','uintz','');
INSERT INTO xcart_config VALUES ('abcr_daily_task_time','When cron task was executed last time',0,'',0,'numeric','','','','');
INSERT INTO xcart_config VALUES ('abcr_ajax_save','Save cart using AJAX when customer entered e-mail but not yet submitted it','N','Abandoned_Cart_Reminder',10,'checkbox','N','','','');
INSERT INTO xcart_config VALUES ('abcr_show_address','Show customer address in abandoned carts list','N','Abandoned_Cart_Reminder',20,'selector','N','N:Disabled\nA:Show only for anonymous\nY:Show for all','','');
