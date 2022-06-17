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
$page_vars['db_settings']          = array();
$page_vars['price_settings']       = array();

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

// db settings - begin
$db_settings = array();

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
    $result_array['page_errors'][] = 'fetchDBSettings';
    goto end_of_page;
}
$r = $stmt->execute();
if ($r === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'fetchDBSettings';
    goto end_of_page;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $db_settings = $row;
}
$stmt->closeCursor();
if (count($db_settings) == 0) {
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'not_found';
    goto end_of_page;
}
$page_vars['db_settings'] = $db_settings;

$price_settings = array();

$sql
    = <<<EOF
SELECT
t2.id,
t2.base_asset,
t2.quote_asset,
t2.sell_price,
t2.buy_price
FROM price_settings t2;

EOF;


$stmt = $db->prepare($sql);
if ($stmt === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'fetchDBSettings';
    goto end_of_page;
}
$r = $stmt->execute();
if ($r === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'fetchDBSettings';
    goto end_of_page;
}

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    array_push($price_settings, $row);
    // $price_settings = $row;
    // var_dump($price_settings);  
}
$stmt->closeCursor();
if (count($db_settings) == 0) {
    $result_array['page_success']  = false;
    $result_array['page_errors'][] = 'not_found';
    goto end_of_page;
}
// var_dump($price_settings);
// exit();
$page_vars['price_settings'] = $price_settings;

ini_set('date.timezone', $db_settings['date_timezone']);

// system settings - begin
$system_settings = array();

$sql
    = <<<EOF
SELECT
t1.id,
t1.is_running,
t1.operation_mode,
t1.status,
t1.buy_order_date,
t1.sell_order_date,
t1.interval_start_date,
t1.last_successful_trade_date,
t1.buy_order_check_interval,
t1.sell_order_check_interval,
t1.interval_h,
t1.interval_l,
t1.trading_fee_rate,
t1.last_sell_price,
t1.quote_asset,
t1.base_asset,
t1.quote_asset_balance,
t1.base_asset_balance,
t1.asset_price_tick_size,
t1.asset_lot_step_size,
t1.asset_price,
t1.previous_asset_price,
t1.current_trend,
t1.buy_price,
t1.sell_price,
t1.last_order_id,
t1.last_order_check_date,
t1.sell_buy_price_ratio,
t1.is_enabled_rule_1,
t1.min_hl_ratio,
t1.is_enabled_rule_2,
t1.max_hl_ratio,
t1.is_enabled_rule_3,
t1.min_current_interval_low_ratio,
t1.is_enabled_rule_4,
t1.max_current_interval_low_ratio,
t1.is_enabled_rule_5,
t1.min_interval_duration,
t1.is_enabled_rule_6,
t1.max_interval_duration,
t1.is_enabled_rule_7,
t1.buy_order_validity,
t1.is_enabled_rule_8,
t1.sell_order_validity,
t1.is_enabled_rule_9,
t1.max_number_of_trades,
t1.number_of_trades,
t1.is_enabled_rule_10,
t1.max_buy_current_ratio,
t1.is_enabled_rule_11,
t1.is_enabled_rule_12,
t1.is_enabled_rule_13,
t1.is_enabled_rule_14,
t1.is_enabled_rule_15,
t1.is_enabled_rule_16,
t1.min_last_sell_current_ratio,
t1.is_enabled_rule_17,
t1.max_buying_price,
t1.is_enabled_auto_buy,
t1.is_enabled_auto_sell,
t1.last_set_boundary,
t1.trend_calculation_first_duration,
t1.trend_calculation_second_duration,
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

$interval_start_date_formatted = '';
if (empty($system_settings['interval_start_date']) === false) {
    $interval_start_date_formatted = date($system_settings['date_format'], $system_settings['interval_start_date']);
}
$system_settings['interval_start_date_formatted'] = $interval_start_date_formatted;

$buy_order_date_formatted = '';
if (empty($system_settings['buy_order_date']) === false) {
    $buy_order_date_formatted = date($system_settings['date_format'], $system_settings['buy_order_date']);
}
$system_settings['buy_order_date_formatted'] = $buy_order_date_formatted;

$sell_order_date_formatted = '';
if (empty($system_settings['sell_order_date']) === false) {
    $sell_order_date_formatted = date($system_settings['date_format'], $system_settings['sell_order_date']);
}
$system_settings['sell_order_date_formatted'] = $sell_order_date_formatted;

$system_settings['interval_hl_ratio'] = '';
if ( ! empty($system_settings['interval_h']) && ! empty($system_settings['interval_l'])) {
    $system_settings['interval_hl_ratio'] = $system_settings['interval_h'] / $system_settings['interval_l'];
}

$system_settings['current_buy_price_ratio'] = '';
if ( ! empty($system_settings['asset_price']) && ! empty($system_settings['buy_price'])) {
    $system_settings['current_buy_price_ratio'] = $system_settings['asset_price'] / $system_settings['buy_price'];
}

$page_vars['system_settings'] = $system_settings;
// system settings - end

if ($result_array['page_success'] == true && isset($_POST['submit_btn'])) {
    $page_vars['is_form_submitted'] = true;

    // update
    $trading_fee_rate                  = isset($_POST['trading_fee_rate']) && ! empty($_POST['trading_fee_rate']) ? $_POST['trading_fee_rate'] : null;
    $last_sell_price                   = empty($_POST['last_sell_price']) ? null : $_POST['last_sell_price'];
    
    $target_base_buy_price             = isset($_POST['target_base_buy_price']) && ! empty($_POST['target_base_buy_price']) ? $_POST['target_base_buy_price'] : null;
    $target_base_sell_price            = isset($_POST['target_base_sell_price']) && ! empty($_POST['target_base_sell_price']) ? $_POST['target_base_sell_price'] : null;

    $quote_asset                       = isset($_POST['quote_asset']) && ! empty($_POST['quote_asset']) ? $_POST['quote_asset'] : null;
    $base_asset                        = isset($_POST['base_asset']) && ! empty($_POST['base_asset']) ? $_POST['base_asset'] : null;
    $base_asset_balance                = isset($_POST['base_asset_balance']) ? $_POST['base_asset_balance'] : null;
    $quote_asset_balance               = isset($_POST['quote_asset_balance']) ? $_POST['quote_asset_balance'] : null;
    $asset_price_tick_size             = isset($_POST['asset_price_tick_size']) && ! empty($_POST['asset_price_tick_size']) ? $_POST['asset_price_tick_size'] : null;
    $asset_lot_step_size               = isset($_POST['asset_lot_step_size']) && ! empty($_POST['asset_lot_step_size']) ? $_POST['asset_lot_step_size'] : null;
    $sell_buy_price_ratio              = isset($_POST['sell_buy_price_ratio']) && ! empty($_POST['sell_buy_price_ratio']) ? $_POST['sell_buy_price_ratio'] : null;
    $buy_order_check_interval          = isset($_POST['buy_order_check_interval']) && ! empty($_POST['buy_order_check_interval']) ? $_POST['buy_order_check_interval'] : null;
    $sell_order_check_interval         = isset($_POST['sell_order_check_interval']) && ! empty($_POST['sell_order_check_interval']) ? $_POST['sell_order_check_interval'] : null;
    $is_enabled_rule_1                 = isset($_POST['is_enabled_rule_1']) ? true : false;
    $min_hl_ratio                      = isset($_POST['min_hl_ratio']) && ! empty($_POST['min_hl_ratio']) ? $_POST['min_hl_ratio'] : null;
    $is_enabled_rule_2                 = isset($_POST['is_enabled_rule_2']) ? true : false;
    $max_hl_ratio                      = isset($_POST['max_hl_ratio']) && ! empty($_POST['max_hl_ratio']) ? $_POST['max_hl_ratio'] : null;
    $is_enabled_rule_3                 = isset($_POST['is_enabled_rule_3']) ? true : false;
    $min_current_interval_low_ratio    = isset($_POST['min_current_interval_low_ratio']) && ! empty($_POST['min_current_interval_low_ratio']) ? $_POST['min_current_interval_low_ratio'] : null;
    $is_enabled_rule_4                 = isset($_POST['is_enabled_rule_4']) ? true : false;
    $max_current_interval_low_ratio    = isset($_POST['max_current_interval_low_ratio']) && ! empty($_POST['max_current_interval_low_ratio']) ? $_POST['max_current_interval_low_ratio'] : null;
    $is_enabled_rule_5                 = isset($_POST['is_enabled_rule_5']) ? true : false;
    $min_interval_duration             = isset($_POST['min_interval_duration']) && ! empty($_POST['min_interval_duration']) ? $_POST['min_interval_duration'] : null;
    $is_enabled_rule_6                 = isset($_POST['is_enabled_rule_6']) ? true : false;
    $max_interval_duration             = isset($_POST['max_interval_duration']) && ! empty($_POST['max_interval_duration']) ? $_POST['max_interval_duration'] : null;
    $is_enabled_rule_7                 = isset($_POST['is_enabled_rule_7']) ? true : false;
    $buy_order_validity                = isset($_POST['buy_order_validity']) && ! empty($_POST['buy_order_validity']) ? $_POST['buy_order_validity'] : null;
    $is_enabled_rule_8                 = isset($_POST['is_enabled_rule_8']) ? true : false;
    $sell_order_validity               = isset($_POST['sell_order_validity']) && ! empty($_POST['sell_order_validity']) ? $_POST['sell_order_validity'] : null;
    $is_enabled_rule_9                 = isset($_POST['is_enabled_rule_9']) ? true : false;
    $max_number_of_trades              = isset($_POST['max_number_of_trades']) && ! empty($_POST['max_number_of_trades']) ? $_POST['max_number_of_trades'] : null;
    $is_enabled_rule_10                = isset($_POST['is_enabled_rule_10']) ? true : false;
    $max_buy_current_ratio             = isset($_POST['max_buy_current_ratio']) && ! empty($_POST['max_buy_current_ratio']) ? $_POST['max_buy_current_ratio'] : null;
    $is_enabled_rule_11                = isset($_POST['is_enabled_rule_11']) ? true : false;
    $is_enabled_rule_12                = isset($_POST['is_enabled_rule_12']) ? true : false;
    $is_enabled_rule_13                = isset($_POST['is_enabled_rule_13']) ? true : false;
    $is_enabled_rule_14                = isset($_POST['is_enabled_rule_14']) ? true : false;
    $is_enabled_rule_15                = isset($_POST['is_enabled_rule_15']) ? true : false;
    $is_enabled_rule_16                = isset($_POST['is_enabled_rule_16']) ? true : false;
    $min_last_sell_current_ratio       = isset($_POST['min_last_sell_current_ratio']) && ! empty($_POST['min_last_sell_current_ratio']) ? $_POST['min_last_sell_current_ratio'] : null;
    $is_enabled_rule_17                = isset($_POST['is_enabled_rule_17']) ? true : false;
    $max_buying_price                  = isset($_POST['max_buying_price']) && ! empty($_POST['max_buying_price']) ? $_POST['max_buying_price'] : null;
    $is_enabled_auto_buy               = isset($_POST['is_enabled_auto_buy']) ? true : false;
    $is_enabled_auto_sell              = isset($_POST['is_enabled_auto_sell']) ? true : false;
    $trend_calculation_first_duration  = isset($_POST['trend_calculation_first_duration']) && ! empty($_POST['trend_calculation_first_duration']) ? $_POST['trend_calculation_first_duration'] : null;
    $trend_calculation_second_duration = isset($_POST['trend_calculation_second_duration']) && ! empty($_POST['trend_calculation_second_duration']) ? $_POST['trend_calculation_second_duration'] : null;

    $is_pair_changed = false;
    if ( ! empty($quote_asset) && ! empty($base_asset) && ($system_settings['quote_asset'] != $quote_asset || $system_settings['base_asset'] != $base_asset)) {
        $is_pair_changed = true;
    }

    if ($is_pair_changed === true) {
        $last_sell_price = null;

        // get exchange info - begin
        if ($db_settings['is_enabled_auto_fetch_trading_rules'] == true) {
            $get_vars           = array();
            $get_vars['action'] = 'getExchangeInfo';

            $post_vars = array();

            $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
            if ($_response === null) {
                Functions::error_log('getExchangeInfo api_error' . json_encode($get_vars) . json_encode($post_vars));
                $page_vars['form_success']  = false;
                $page_vars['form_errors'][] = 'getExchangeInfo';
                goto end_of_page;
            } elseif ($_response['result'] != 'success') {
                Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                $page_vars['form_success']  = false;
                $page_vars['form_errors'][] = 'getExchangeInfo';
                goto end_of_page;
            }
            $exchange_info = $_response['values']['info'];

            if ( ! empty($exchange_info['symbols'])) {
                foreach ($exchange_info['symbols'] as $symbol) {
                    if ($symbol['symbol'] == $base_asset . $quote_asset) {
                        if ( ! empty($symbol['filters'])) {
                            foreach ($symbol['filters'] as $filter) {
                                if ($filter['filterType'] == 'PRICE_FILTER') {
                                    $asset_price_tick_size = $filter['tickSize'];
                                } elseif ($filter['filterType'] == 'LOT_SIZE') {
                                    $asset_lot_step_size = $filter['stepSize'];
                                }
                            }
                        }
                        break;
                    }
                }
            }
        } // get exchange info - end
    }

    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
trading_fee_rate = :trading_fee_rate,
last_sell_price = :last_sell_price,
`status` = CASE WHEN base_asset = :base_asset2 AND quote_asset = :quote_asset2 THEN `status` ELSE 'waiting_for_opportunity' END,
interval_h = CASE WHEN base_asset = :base_asset3 AND quote_asset = :quote_asset3 THEN interval_h ELSE NULL END,
interval_l = CASE WHEN base_asset = :base_asset4 AND quote_asset = :quote_asset4 THEN interval_l ELSE NULL END,
asset_price = CASE WHEN base_asset = :base_asset5 AND quote_asset = :quote_asset5 THEN asset_price ELSE NULL END,
previous_asset_price = CASE WHEN base_asset = :base_asset6 AND quote_asset = :quote_asset6 THEN previous_asset_price ELSE NULL END,
buy_price = CASE WHEN base_asset = :base_asset7 AND quote_asset = :quote_asset7 THEN buy_price ELSE NULL END,
base_asset_balance = CASE WHEN base_asset = :base_asset8 AND quote_asset = :quote_asset8 THEN CASE WHEN is_running = 0 THEN :base_asset_balance ELSE base_asset_balance END ELSE 0 END,
quote_asset_balance = CASE WHEN base_asset = :base_asset9 AND quote_asset = :quote_asset9 THEN CASE WHEN is_running = 0 THEN :quote_asset_balance ELSE quote_asset_balance END ELSE 0 END,
buy_order_date = CASE WHEN base_asset = :base_asset10 AND quote_asset = :quote_asset10 THEN buy_order_date ELSE NULL END,
sell_order_date = CASE WHEN base_asset = :base_asset11 AND quote_asset = :quote_asset11 THEN sell_order_date ELSE NULL END,
last_order_id = CASE WHEN base_asset = :base_asset12 AND quote_asset = :quote_asset12 THEN last_order_id ELSE NULL END,
quote_asset = :quote_asset,
base_asset = :base_asset,
target_base_buy_price = :target_base_buy_price,
target_base_sell_price = :target_base_sell_price,

asset_price_tick_size = :asset_price_tick_size,
asset_lot_step_size = :asset_lot_step_size,
sell_buy_price_ratio = :sell_buy_price_ratio,
buy_order_check_interval = :buy_order_check_interval,
sell_order_check_interval = :sell_order_check_interval,
is_enabled_rule_1 = :is_enabled_rule_1,
min_hl_ratio = :min_hl_ratio,
is_enabled_rule_2 = :is_enabled_rule_2,
max_hl_ratio = :max_hl_ratio,
is_enabled_rule_3 = :is_enabled_rule_3,
min_current_interval_low_ratio = :min_current_interval_low_ratio,
is_enabled_rule_4 = :is_enabled_rule_4,
max_current_interval_low_ratio = :max_current_interval_low_ratio,
is_enabled_rule_5 = :is_enabled_rule_5,
min_interval_duration = :min_interval_duration,
is_enabled_rule_6 = :is_enabled_rule_6,
max_interval_duration = :max_interval_duration,
is_enabled_rule_7 = :is_enabled_rule_7,
buy_order_validity = :buy_order_validity,
is_enabled_rule_8 = :is_enabled_rule_8,
sell_order_validity = :sell_order_validity,
is_enabled_rule_9 = :is_enabled_rule_9,
max_number_of_trades = :max_number_of_trades,
is_enabled_rule_10 = :is_enabled_rule_10,
max_buy_current_ratio = :max_buy_current_ratio,
is_enabled_rule_11 = :is_enabled_rule_11,
is_enabled_rule_12 = :is_enabled_rule_12,
is_enabled_rule_13 = :is_enabled_rule_13,
is_enabled_rule_14 = :is_enabled_rule_14,
is_enabled_rule_15 = :is_enabled_rule_15,
is_enabled_rule_16 = :is_enabled_rule_16,
min_last_sell_current_ratio = :min_last_sell_current_ratio,
is_enabled_rule_17 = :is_enabled_rule_17,
max_buying_price = :max_buying_price,
is_enabled_auto_buy = :is_enabled_auto_buy,
is_enabled_auto_sell = :is_enabled_auto_sell,
trend_calculation_first_duration = :trend_calculation_first_duration,
trend_calculation_second_duration = :trend_calculation_second_duration,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
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
    $stmt->bindValue(':trading_fee_rate', $trading_fee_rate, $trading_fee_rate !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':last_sell_price', $last_sell_price, $last_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    
    $stmt->bindValue(':target_base_buy_price', $target_base_buy_price, $target_base_buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':target_base_sell_price', $target_base_sell_price, $target_base_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

    
    $stmt->bindValue(':quote_asset', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset2', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset3', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset4', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset5', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset6', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset7', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset8', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset9', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset10', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset11', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset12', $quote_asset, $quote_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset2', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset3', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset4', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset5', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset6', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset7', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset8', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset9', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset10', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset11', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset12', $base_asset, $base_asset !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset_balance', $base_asset_balance, $base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':quote_asset_balance', $quote_asset_balance, $quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':asset_price_tick_size', $asset_price_tick_size, $asset_price_tick_size !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':asset_lot_step_size', $asset_lot_step_size, $asset_lot_step_size !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':sell_buy_price_ratio', $sell_buy_price_ratio, $sell_buy_price_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':buy_order_check_interval', $buy_order_check_interval, $buy_order_check_interval !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':sell_order_check_interval', $sell_order_check_interval, $sell_order_check_interval !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_1', $is_enabled_rule_1, PDO::PARAM_BOOL);
    $stmt->bindValue(':min_hl_ratio', $min_hl_ratio, $min_hl_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_2', $is_enabled_rule_2, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_hl_ratio', $max_hl_ratio, $max_hl_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_3', $is_enabled_rule_3, PDO::PARAM_BOOL);
    $stmt->bindValue(':min_current_interval_low_ratio', $min_current_interval_low_ratio, $min_current_interval_low_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_4', $is_enabled_rule_4, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_current_interval_low_ratio', $max_current_interval_low_ratio, $max_current_interval_low_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_5', $is_enabled_rule_5, PDO::PARAM_BOOL);
    $stmt->bindValue(':min_interval_duration', $min_interval_duration, $min_interval_duration !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_6', $is_enabled_rule_6, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_interval_duration', $max_interval_duration, $max_interval_duration !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_7', $is_enabled_rule_7, PDO::PARAM_BOOL);
    $stmt->bindValue(':buy_order_validity', $buy_order_validity, $buy_order_validity !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_8', $is_enabled_rule_8, PDO::PARAM_BOOL);
    $stmt->bindValue(':sell_order_validity', $sell_order_validity, $sell_order_validity !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_9', $is_enabled_rule_9, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_number_of_trades', $max_number_of_trades, $max_number_of_trades !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_10', $is_enabled_rule_10, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_buy_current_ratio', $max_buy_current_ratio, $max_buy_current_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_11', $is_enabled_rule_11, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_rule_12', $is_enabled_rule_12, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_rule_13', $is_enabled_rule_13, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_rule_14', $is_enabled_rule_14, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_rule_15', $is_enabled_rule_15, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_rule_16', $is_enabled_rule_16, PDO::PARAM_BOOL);
    $stmt->bindValue(':min_last_sell_current_ratio', $min_last_sell_current_ratio, $min_last_sell_current_ratio !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_rule_17', $is_enabled_rule_17, PDO::PARAM_BOOL);
    $stmt->bindValue(':max_buying_price', $max_buying_price, $max_buying_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':is_enabled_auto_buy', $is_enabled_auto_buy, PDO::PARAM_BOOL);
    $stmt->bindValue(':is_enabled_auto_sell', $is_enabled_auto_sell, PDO::PARAM_BOOL);
    $stmt->bindValue(':trend_calculation_first_duration', $trend_calculation_first_duration, $trend_calculation_first_duration !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':trend_calculation_second_duration', $trend_calculation_second_duration, $trend_calculation_second_duration !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto end_of_page;
    } else {
        header('Location: index.php?l=' . CRYPTOBOT_LANGUAGE);
        exit();
    }

    // if ($result_array['page_success'] == true && isset($_POST['submit_btn'])) {
    //     $page_vars['is_form_submitted'] = true;
    //     $trading_fee_rate                  = isset($_POST['trading_fee_rate']) && ! empty($_POST['trading_fee_rate']) ? $_POST['trading_fee_rate'] : null;
    //     $last_sell_price                   = empty($_POST['last_sell_price']) ? null : $_POST['last_sell_price'];
    
}

end_of_page: