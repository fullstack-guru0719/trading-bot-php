<?php
if (defined('CRYPTOBOT') === false) {
    exit("invalid request");
}

array_walk($_POST, 'Functions::callback_array_walk_trim');
array_walk($_GET, 'Functions::callback_array_walk_trim');

$page_vars                         = array();
$page_vars['is_form_submitted']    = false;
$page_vars['form_success']         = true;
$page_vars['form_errors']          = array();
$page_vars['form_hidden_elements'] = array();
$page_vars['form_error_inputs']    = array();
$page_vars['post_values']          = $_POST;
$page_vars['get_values']           = $_GET;
$page_vars['system_settings']      = array();

// db connection
if (file_exists($sqlite_db_location) === false) {
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = "sqlite database file doesn't exist at the specified location.";
    goto end_of_page;
}
try {
    $db = new PDO('sqlite:' . $sqlite_db_location, null, null);
} catch (PDOException $exception) {
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = $exception->getCode() . ' - ' . $exception->getMessage();
    goto end_of_page;
}

// system settings - begin
$system_settings = array();

$sql
    = <<<EOF
SELECT
t1.id,
t1.system_api_url,
t1.binance_api_base_url,
t1.binance_api_key,
t1.binance_api_secret_key,
t1.price_check_interval,
t1.date_timezone,
t1.date_format,
t1.is_enabled_auto_fetch_trading_rules,
t1.target_base_buy_price,
t1.target_base_sell_price
FROM system_settings t1
LIMIT 1;
EOF;

?>
end_of_page;