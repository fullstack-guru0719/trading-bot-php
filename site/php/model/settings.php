<?php
if ( ! defined('CRYPTOBOT')) {
    exit("invalid request");
}

array_walk($_POST, 'Functions::callback_array_walk_trim');
array_walk($_GET, 'Functions::callback_array_walk_trim');

// page_vars defaults
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

$stmt = $db->prepare($sql);
if ($stmt === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'listSystemSettings';
    goto end_of_page;
}
$r = $stmt->execute();
if ($r === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'listSystemSettings';
    goto end_of_page;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $system_settings = $row;
}
$stmt->closeCursor();
if (count($system_settings) == 0) {
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'not_found';
    goto end_of_page;
}
$system_settings = Functions::formatSystemSettings($system_settings);

$page_vars['system_settings'] = $system_settings;
// system settings - end

if ($result_array['page_success'] == true && isset($_POST['submit_btn'])) {
    $page_vars['is_form_submitted'] = true;

    // update
    $system_api_url                      = isset($_POST['system_api_url']) && ! empty($_POST['system_api_url']) ? $_POST['system_api_url'] : null;
    $binance_api_base_url                = isset($_POST['binance_api_base_url']) && ! empty($_POST['binance_api_base_url']) ? $_POST['binance_api_base_url'] : null;
    $binance_api_key                     = isset($_POST['binance_api_key']) && ! empty($_POST['binance_api_key']) ? $_POST['binance_api_key'] : null;
    $binance_api_secret_key              = isset($_POST['binance_api_secret_key']) && ! empty($_POST['binance_api_secret_key']) ? $_POST['binance_api_secret_key'] : null;
    
    $price_check_interval                = isset($_POST['price_check_interval']) && ! empty($_POST['price_check_interval']) ? intval($_POST['price_check_interval']) : 150;
    // add target_price_key
    $target_base_buy_price               = isset($_POST['target_base_buy_price']) && ! empty($_POST['target_base_buy_price']) ? $_POST['target_base_buy_price'] : null;
    $target_base_sell_price              = isset($_POST['target_base_sell_price']) && ! empty($_POST['target_base_sell_price']) ? $_POST['target_base_sell_price'] : null;
    // end
    $date_timezone                       = isset($_POST['date_timezone']) && ! empty($_POST['date_timezone']) ? $_POST['date_timezone'] : null;
    $date_format                         = isset($_POST['date_format']) && ! empty($_POST['date_format']) ? $_POST['date_format'] : null;
    $is_enabled_auto_fetch_trading_rules = isset($_POST['is_enabled_auto_fetch_trading_rules']) ? true : false;

    if ($binance_api_base_url !== null) {
        if (substr($binance_api_base_url, -1) != '/') {
            $binance_api_base_url .= '/';
        }
    }
    if ($binance_api_key == '*****') {
        $binance_api_key = $system_settings['binance_api_key'];
    }
    if ($binance_api_secret_key == '*****') {
        $binance_api_secret_key = $system_settings['binance_api_secret_key'];
    }

    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
system_api_url = :system_api_url,
binance_api_base_url = :binance_api_base_url,
binance_api_key = :binance_api_key,
binance_api_secret_key = :binance_api_secret_key,
price_check_interval = :price_check_interval,
target_base_sell_price = :target_base_sell_price,
target_base_buy_price = :target_base_buy_price,
date_timezone = :date_timezone,
date_format = :date_format,
is_enabled_auto_fetch_trading_rules = :is_enabled_auto_fetch_trading_rules
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = 'editSystemSettings';
        goto end_of_page;
    }

    // Bind values
    $stmt->bindValue(':system_api_url', $system_api_url, $system_api_url !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':binance_api_base_url', $binance_api_base_url, $binance_api_base_url !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':binance_api_key', $binance_api_key, $binance_api_key !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':binance_api_secret_key', $binance_api_secret_key, $binance_api_secret_key !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    // add
    $stmt->bindValue(':target_base_buy_price', $target_base_buy_price, $target_base_buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':target_base_sell_price', $target_base_sell_price, $target_base_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    // end
    $stmt->bindValue(':price_check_interval', $price_check_interval, $price_check_interval !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':date_timezone', $date_timezone, $date_timezone !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':date_format', $date_format, $date_format !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_auto_fetch_trading_rules', $is_enabled_auto_fetch_trading_rules, PDO::PARAM_BOOL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto end_of_page;
    } else {
        header('Location: settings.php?l=' . CRYPTOBOT_LANGUAGE);
        exit();
    }
}
end_of_page:
