DROP TABLE IF EXISTS xcart_abcr_abandoned_carts;
DROP TABLE IF EXISTS xcart_abcr_notifications;
DROP TABLE IF EXISTS xcart_abcr_order_statistic;

DELETE FROM xcart_modules WHERE module_name='Abandoned_Cart_Reminder';
DELETE FROM xcart_config WHERE name IN (
'abcr_notify_after',
'abcr_notification_delay',
'abcr_notification_count',
'abcr_coupon_type',
'abcr_coupon_value',
'abcr_expire_after',
'abcr_work_mode',
'abcr_daily_task_time'
);
