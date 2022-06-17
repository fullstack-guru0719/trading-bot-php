<?php
if (defined('CRYPTOBOT') === false) {
    exit("invalid request");
}

array_walk($_POST, 'Functions::callback_array_walk_trim');
array_walk($_GET, 'Functions::callback_array_walk_trim');

if (empty($argv[1]) === false && $argv[1] == '--process') {
    $_GET['action'] = 'process';
}

$method_to_call = '';
// methods - begin
if (isset($_GET['action'])) {
    if (in_array($_GET['action'],
            array(
                'cancelOrder',
                'getOrderDetails',
                'createBuyOrder',
                'createSellOrder',
                'resetInterval',
                'process',
                'startProcessing',
                'pauseProcessing',
                'stopProcessing',
                'updateAccountBalance',
                'updateAssetPrice',
                'getAccountInfo',
                'getAveragePrice',
                'getLatestPrice',
                'getExchangeInfo',
                'getServerTime',
                'getExchangeInfo',
                'activateBuyNow',
                'activateSellNow',
                'activateCancelNow',
            )) === false
    ) {
        $has_error         = true;
        $response['error'] = 'invalid_action';
        goto page_end;
    }
} else {
    $has_error         = true;
    $response['error'] = 'required_action';
    goto page_end;
}
// methods - end

// db connection
if (file_exists($sqlite_db_location) === false) {
    $has_error         = true;
    $response['error'] = "sqlite database file doesn't exist at the specified location.";
    goto page_end;
}
try {
    $db = new PDO('sqlite:' . $sqlite_db_location, null, null);
} catch (PDOException $exception) {
    $has_error         = true;
    $response['error'] = $exception->getCode() . ' - ' . $exception->getMessage();
    goto page_end;
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
t1.target_buy_price,
t1.date_timezone,
t1.date_format,
t1.is_enabled_auto_fetch_trading_rules
FROM system_settings t1
LIMIT 1;
EOF;

$stmt = $db->prepare($sql);
if ($stmt === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
    $has_error         = true;
    $response['error'] = 'fetchDBSettings';
    goto page_end;
}
$r = $stmt->execute();
if ($r === false) {
    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
    $has_error         = true;
    $response['error'] = 'fetchDBSettings';
    goto page_end;
}
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $db_settings = $row;
}
$stmt->closeCursor();
// db settings - end

ini_set('date.timezone', $db_settings['date_timezone']);

$sql_system_settings
    = <<<EOF
SELECT
t1.id,
t1.is_running,
t1.operation_mode,
t1.status,
t1.date_timezone,
t1.date_format,
t1.is_enabled_auto_fetch_trading_rules,
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
t1.is_active_buy_now,
t1.is_active_sell_now,
t1.is_active_cancel_now
FROM system_settings t1
LIMIT 1;
EOF;

if ($_GET['action'] == 'resetInterval') { // reset interval - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['price_diff'] = floatval($row['asset_price']) - floatval($row['previous_asset_price']);
        $system_settings   = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info']                       = $system_settings;
    $response['values']['info']['status_text']        = Functions::statusToText($response['values']['info']['status']);
    $response['values']['info']['current_trend_text'] = Functions::trendToText($response['values']['info']['current_trend']);
    $response['values']['info']['target_sell_price']  = Functions::targetSellPrice($system_settings);

    // update database - begin
    $time_now = time();
    $id       = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
asset_price = NULL,
previous_asset_price = NULL,
interval_start_date = :interval_start_date,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':interval_start_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end

    $response['values']['info']['interval_h']                    = null;
    $response['values']['info']['interval_l']                    = null;
    $response['values']['info']['interval_hl_ratio']             = null;
    $response['values']['info']['current_buy_price_ratio']       = null;
    $response['values']['info']['asset_price']                   = null;
    $response['values']['info']['previous_asset_price']          = null;
    $response['values']['info']['price_diff']                    = 0;
    $response['values']['info']['interval_start_date']           = $time_now;
    $response['values']['info']['interval_start_date_formatted'] = date($system_settings['date_format'], $time_now);

} // reset interval - end
elseif ($_GET['action'] == 'process') { // process - begin
    $response['values']['info'] = array();

    $is_active_buy_now    = false;
    $is_active_sell_now   = false;
    $is_active_cancel_now = false;

    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['price_diff'] = floatval($row['asset_price']) - floatval($row['previous_asset_price']);
        $system_settings   = $row;

        $is_active_buy_now    = $row['is_active_buy_now'];
        $is_active_sell_now   = $row['is_active_sell_now'];
        $is_active_cancel_now = $row['is_active_cancel_now'];
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info']                       = $system_settings;
    $response['values']['info']['status_text']        = Functions::statusToText($response['values']['info']['status']);
    $response['values']['info']['current_trend_text'] = Functions::trendToText($response['values']['info']['current_trend']);
    $response['values']['info']['target_sell_price']  = Functions::targetSellPrice($system_settings);

    if ($system_settings['is_running'] == false) {
        goto page_end;
    }

    if ($system_settings['operation_mode'] == 'update_asset_price') { // process - operation_mode - update_asset_price - begin
        // update asset price - begin
        $get_vars           = array();
        $get_vars['action'] = 'updateAssetPrice';

        $post_vars = array();

        $_response = ApiClient::makeRequest($db_settings['system_api_url'], $get_vars, $post_vars);
        if ($_response === null) {
            Functions::error_log('updateAssetPrice api_error' . json_encode($get_vars) . json_encode($post_vars));
            $has_error                     = true;
            $response['error']             = $_response['error'];
            $response['error_description'] = 'updateAssetPrice';
            goto page_end;
        } elseif ($_response['result'] != 'success') {
            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
            $has_error                     = true;
            $response['error']             = $_response['error'];
            $response['error_description'] = 'updateAssetPrice';
            goto page_end;
        }
        $response['values']['info']['interval_h']        = $_response['values']['info']['interval_h'];
        $response['values']['info']['interval_l']        = $_response['values']['info']['interval_l'];
        $response['values']['info']['interval_hl_ratio'] = '';
        if (empty($_response['values']['info']['interval_h']) === false && empty($_response['values']['info']['interval_l']) === false) {
            $response['values']['info']['interval_hl_ratio'] = $_response['values']['info']['interval_h'] / $_response['values']['info']['interval_l'];
        }
        $response['values']['info']['asset_price']          = $_response['values']['info']['asset_price'];
        $response['values']['info']['previous_asset_price'] = $_response['values']['info']['previous_asset_price'];
        $response['values']['info']['price_diff']           = $_response['values']['info']['price_diff'];
        $response['values']['info']['operation_mode']       = 'update_asset_price';
        $response['values']['info']['status']               = $_response['values']['info']['status'];
        $response['values']['info']['status_text']          = Functions::statusToText($response['values']['info']['status']);
        $response['values']['info']['current_trend_text']   = Functions::trendToText($response['values']['info']['current_trend']);
        $response['values']['info']['target_sell_price']    = Functions::targetSellPrice($system_settings);

        $response['values']['info']['current_buy_price_ratio'] = '';
        if (empty($response['values']['info']['asset_price']) === false && empty($response['values']['info']['buy_price']) === false) {
            $response['values']['info']['current_buy_price_ratio'] = $response['values']['info']['asset_price'] / $response['values']['info']['buy_price'];
        }
        // update asset price - end

        goto page_end;
    } // process - operation_mode - update_asset_price - end
    elseif ($system_settings['operation_mode'] == 'trade') { // process - operation_mode - trade - begin
        // update asset price - begin
        $get_vars           = array();
        $get_vars['action'] = 'updateAssetPrice';

        $post_vars = array();

        $_response = ApiClient::makeRequest($db_settings['system_api_url'], $get_vars, $post_vars);
        if ($_response === null) {
            Functions::error_log('updateAssetPrice api_error');
            $has_error                     = true;
            $response['error']             = $_response['error'];
            $response['error_description'] = 'updateAssetPrice';
            goto page_end;
        } elseif ($_response['result'] != 'success') {
            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
            $has_error                     = true;
            $response['error']             = $_response['error'];
            $response['error_description'] = 'updateAssetPrice';
            goto page_end;
        }
        $response['values']['info']['interval_h']        = $_response['values']['info']['interval_h'];
        $response['values']['info']['interval_l']        = $_response['values']['info']['interval_l'];
        $response['values']['info']['interval_hl_ratio'] = '';
        if (empty($_response['values']['info']['interval_h']) === false && empty($_response['values']['info']['interval_l']) === false) {
            $response['values']['info']['interval_hl_ratio'] = $_response['values']['info']['interval_h'] / $_response['values']['info']['interval_l'];
        }
        $response['values']['info']['asset_price']                   = $_response['values']['info']['asset_price'];
        $response['values']['info']['previous_asset_price']          = $_response['values']['info']['previous_asset_price'];
        $response['values']['info']['price_diff']                    = $_response['values']['info']['price_diff'];
        $response['values']['info']['operation_mode']                = 'trade';
        $response['values']['info']['status']                        = $_response['values']['info']['status'];
        $response['values']['info']['status_text']                   = Functions::statusToText($response['values']['info']['status']);
        $response['values']['info']['current_trend_text']            = Functions::trendToText($response['values']['info']['current_trend']);
        $response['values']['info']['target_sell_price']             = Functions::targetSellPrice($system_settings);
        $response['values']['info']['interval_start_date_formatted'] = $_response['values']['info']['interval_start_date_formatted'];
        $response['values']['info']['buy_order_date_formatted']      = $_response['values']['info']['buy_order_date_formatted'];
        $response['values']['info']['sell_order_date_formatted']     = $_response['values']['info']['sell_order_date_formatted'];

        $response['values']['info']['current_buy_price_ratio'] = '';
        if (empty($response['values']['info']['asset_price']) === false && empty($response['values']['info']['buy_price']) === false) {
            $response['values']['info']['current_buy_price_ratio'] = $response['values']['info']['asset_price'] / $response['values']['info']['buy_price'];
        }
        // update asset price - end

        if (empty($system_settings['status'])) { // process - operation_mode - trade - status - empty - begin
            // update status - begin
            $id = $system_settings['id'];

            $sql
                = <<<EOF
UPDATE system_settings
SET
status = 'waiting_for_opportunity',
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                $has_error         = true;
                $response['error'] = 'updateSystemSettings';
                goto page_end;
            }

            // Bind values
            $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $r = $stmt->execute();
            if ($r === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                $page_vars['form_success']  = false;
                $page_vars['form_errors'][] = $db->errorCode();
                goto page_end;
            }
            // update status - end

            $response['values']['info']['status']             = 'waiting_for_opportunity';
            $response['values']['info']['status_text']        = Functions::statusToText($response['values']['info']['status']);
            $response['values']['info']['current_trend_text'] = Functions::trendToText($response['values']['info']['current_trend']);
            $response['values']['info']['target_sell_price']  = Functions::targetSellPrice($system_settings);

            goto page_end;
        } // process - operation_mode - trade - status - empty - end
        elseif ($system_settings['status'] == 'waiting_for_opportunity') { // process - operation_mode - trade - status - waiting_for_opportunity - begin
            waiting_for_opportunity:

            // get system settings - begin
            $sql = $sql_system_settings;

            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            $r = $stmt->execute();
            if ($r === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $row['price_diff'] = floatval($row['asset_price']) - floatval($row['previous_asset_price']);
                $system_settings   = $row;
            }
            $stmt->closeCursor();
            $system_settings = Functions::formatSystemSettings($system_settings);
            // get system settings - end

            $response['values']['info']['status_text']        = Functions::statusToText($response['values']['info']['status']);
            $response['values']['info']['current_trend_text'] = Functions::trendToText($response['values']['info']['current_trend']);
            $response['values']['info']['target_sell_price']  = Functions::targetSellPrice($system_settings);
            $system_settings['interval_hl_ratio']             = '';
            if (empty($system_settings['interval_h']) === false && empty($system_settings['interval_l']) === false) {
                $system_settings['interval_hl_ratio'] = $system_settings['interval_h'] / $system_settings['interval_l'];
            }

            $system_settings['current_buy_price_ratio'] = '';
            if (empty($system_settings['asset_price']) === false && empty($system_settings['buy_price']) === false) {
                $system_settings['current_buy_price_ratio'] = $system_settings['asset_price'] / $system_settings['buy_price'];
            }

            // check max. number of trades - begin
            if ($system_settings['is_enabled_rule_9'] == true && $system_settings['number_of_trades'] >= $system_settings['max_number_of_trades']) {
                // stop processing - begin
                $id = $system_settings['id'];

                $sql
                    = <<<EOF
UPDATE system_settings
SET
is_running = 0,
`status` = 'waiting_for_opportunity',
asset_price = NULL,
buy_order_date = NULL,
sell_order_date = NULL,
interval_start_date = NULL,
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
last_order_check_date = NULL,
last_order_id = NULL,
buy_price = NULL,
sell_price = NULL,
last_sell_price = NULL,
number_of_trades = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                    $has_error         = true;
                    $response['error'] = 'updateSystemSettings';
                    goto page_end;
                }

                // Bind values
                $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                $r = $stmt->execute();
                if ($r === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                    $page_vars['form_success']  = false;
                    $page_vars['form_errors'][] = $db->errorCode();
                    goto page_end;
                }
                // update database - end

                $response['values']['info']                                  = $system_settings;
                $response['values']['info']['is_running']                    = 0;
                $response['values']['info']['status']                        = 'waiting_for_opportunity';
                $response['values']['info']['status_text']                   = Functions::statusToText($response['values']['info']['status']);
                $response['values']['info']['target_sell_price']             = Functions::targetSellPrice($system_settings);
                $response['values']['info']['buy_order_date']                = null;
                $response['values']['info']['buy_order_date_formatted']      = '';
                $response['values']['info']['sell_order_date']               = null;
                $response['values']['info']['sell_order_date_formatted']     = '';
                $response['values']['info']['interval_start_date']           = null;
                $response['values']['info']['interval_start_date_formatted'] = '';
                $response['values']['info']['interval_h']                    = null;
                $response['values']['info']['interval_l']                    = null;
                $response['values']['info']['interval_hl_ratio']             = null;
                $response['values']['info']['last_order_check_date']         = null;
                $response['values']['info']['last_order_id']                 = null;
                $response['values']['info']['buy_price']                     = null;
                $response['values']['info']['sell_price']                    = null;
                $response['values']['info']['number_of_trades']              = 0;
                $response['values']['info']['current_buy_price_ratio']       = null;

                Functions::error_log('reached maximum number of trades');
                goto page_end;
                // stop processing - end
            }
            // check max. number of trades - end

            // check quote asset balance - begin
            if (empty($system_settings['quote_asset_balance'])) {
                Functions::error_log('insufficient quote asset balance');
                $has_error                     = true;
                $response['error']             = 'insufficient_quote_asset_balance';
                $response['error_description'] = 'AccountBalance';
                goto page_end;
            }
            // check quote asset balance - end

            // buy now - begin
            if ($is_active_buy_now == true) {
                goto waiting_for_opportunity_end_of_interval_check;
            }
            // buy now - end

            $reset_interval = false;
            // H/L max ratio check - begin
            if ($system_settings['is_enabled_rule_2'] == true && empty($system_settings['interval_h']) === false && empty($system_settings['interval_l']) === false) {
                if ($system_settings['interval_h'] / $system_settings['interval_l'] > $system_settings['max_hl_ratio']) {
                    $reset_interval = true;
                }
            }
            // H/L max ratio check - end

            // check interval duration - begin
            if ($reset_interval === false
                && $system_settings['is_enabled_rule_6'] == true
                && time() - $system_settings['interval_start_date'] > $system_settings['max_interval_duration']
            ) {
                $reset_interval = true;
            }
            // check interval duration - end

            if ($reset_interval === true) {
                // reset interval - begin
                $id       = $system_settings['id'];
                $time_now = time();

                $sql
                    = <<<EOF
UPDATE system_settings
SET
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
asset_price = NULL,
interval_start_date = :interval_start_date,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                    $has_error         = true;
                    $response['error'] = 'updateSystemSettings';
                    goto page_end;
                }

                // Bind values
                $stmt->bindValue(':interval_start_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                $r = $stmt->execute();
                if ($r === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                    $page_vars['form_success']  = false;
                    $page_vars['form_errors'][] = $db->errorCode();
                    goto page_end;
                }
                // reset interval - end

                $response['values']['info']['interval_h']                    = null;
                $response['values']['info']['interval_l']                    = null;
                $response['values']['info']['interval_hl_ratio']             = null;
                $response['values']['info']['asset_price']                   = null;
                $response['values']['info']['previous_asset_price']          = null;
                $response['values']['info']['price_diff']                    = 0;
                $response['values']['info']['interval_start_date']           = $time_now;
                $response['values']['info']['interval_start_date_formatted'] = date($system_settings['date_format'], $time_now);

                $response['values']['info']['current_buy_price_ratio'] = '';
                if (empty($response['values']['info']['asset_price']) === false && empty($response['values']['info']['buy_price']) === false) {
                    $response['values']['info']['current_buy_price_ratio'] = $response['values']['info']['asset_price'] / $response['values']['info']['buy_price'];
                }

                goto page_end;
            }

            waiting_for_opportunity_end_of_interval_check:

            $can_buy = Functions::canBuy($system_settings);

            $response['values']['info']                       = $system_settings;
            $response['values']['info']['status_text']        = Functions::statusToText($response['values']['info']['status']);
            $response['values']['info']['current_trend_text'] = Functions::trendToText($response['values']['info']['current_trend']);
            $response['values']['info']['target_sell_price']  = Functions::targetSellPrice($system_settings);
            $response['values']['info']['can_buy']            = $can_buy;

            if ($can_buy === true || $is_active_buy_now == true) {

                if ($is_active_buy_now == false) {
                    // create buy order - begin
                    $new_order_id = (int)(microtime(true) * 1000);

                    $system_settings['asset_price']           = floatval($system_settings['asset_price']);
                    $system_settings['asset_price_tick_size'] = floatval($system_settings['asset_price_tick_size']);
                    $system_settings['asset_lot_step_size']   = floatval($system_settings['asset_lot_step_size']);

                    $lot_precision   = abs(log($system_settings['asset_lot_step_size'], 10));
                    $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                    $buy_price = $system_settings['asset_price'];
                    if ($system_settings['asset_price_tick_size'] > 0) {
                        $buy_price = $buy_price - fmod($buy_price, $system_settings['asset_price_tick_size']);
                    }
                    $buy_price = number_format($buy_price, $price_precision, '.', '');

                    $asset_quantity = $system_settings['quote_asset_balance'] / $buy_price;
                    $asset_quantity = $asset_quantity * (1 - ($system_settings['trading_fee_rate'] / 100));
                    if ($system_settings['asset_lot_step_size'] > 0) {
                        $asset_quantity = $asset_quantity - fmod($asset_quantity, $system_settings['asset_lot_step_size']);
                    }
                    $asset_quantity = number_format($asset_quantity, $lot_precision, '.', '');

                    $get_vars           = array();
                    $get_vars['action'] = 'createBuyOrder';

                    $post_vars                     = array();
                    $post_vars['price']            = $buy_price;
                    $post_vars['quantity']         = $asset_quantity;
                    $post_vars['newClientOrderId'] = $new_order_id;
                    $post_vars['type']             = 'LIMIT';
                    $post_vars['timeInForce']      = 'GTC';

                    $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                    if ($_response === null) {
                        Functions::error_log('createBuyOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createBuyOrder';
                        goto page_end;
                    } elseif ($_response['result'] != 'success') {
                        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createBuyOrder';
                        goto page_end;
                    }
                    // create buy order - end

                    // check if buy order request returned status - begin
                    if (empty($_response['values']['info']['status']) == true) {
                        Functions::error_log('EMPTY_STATUS_CREATE_BUY_ORDER:' . $_response);
                        goto page_end;
                    }
                    // check if buy order request returned status - end

                    $order_info = $_response['values']['info'];

                    // update status - begin
                    $id               = $system_settings['id'];
                    $time_now         = time();
                    $binance_order_id = $order_info['orderId'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'created_buy_order',
buy_order_date = :buy_order_date,
buy_price = :buy_price,
last_order_id = :new_order_id,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':buy_order_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':buy_price', $buy_price, $buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':new_order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
INSERT INTO transactions(
insert_date,
order_type,
price,
quantity,
order_id,
binance_order_id,
order_status
)
VALUES
(
:insert_date,
'buy',
:price,
:quantity,
:order_id,
:binance_order_id,
'new'
);
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':insert_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':price', $buy_price, $buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':quantity', $asset_quantity, $asset_quantity !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':binance_order_id', $binance_order_id, $binance_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                             = $system_settings;
                    $response['values']['info']['status']                   = 'created_buy_order';
                    $response['values']['info']['status_text']              = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']       = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']        = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['buy_price']                = $buy_price;
                    $response['values']['info']['last_order_id']            = $new_order_id;
                    $response['values']['info']['buy_order_date']           = $time_now;
                    $response['values']['info']['buy_order_date_formatted'] = date($system_settings['date_format'], $time_now);

                    goto page_end;
                } elseif ($is_active_buy_now == true) {
                    // create buy order - begin
                    $new_order_id = (int)(microtime(true) * 1000);

                    $system_settings['asset_lot_step_size'] = floatval($system_settings['asset_lot_step_size']);

                    $lot_precision   = abs(log($system_settings['asset_lot_step_size'], 10));
                    $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                    $asset_quantity = $system_settings['quote_asset_balance'];
                    $asset_quantity = $asset_quantity * (1 - ($system_settings['trading_fee_rate'] / 100));
                    if ($system_settings['asset_lot_step_size'] > 0) {
                        $asset_quantity = $asset_quantity - fmod($asset_quantity, $system_settings['asset_lot_step_size']);
                    }
                    $asset_quantity = number_format($asset_quantity, $lot_precision, '.', '');

                    $get_vars           = array();
                    $get_vars['action'] = 'createBuyOrder';

                    $post_vars                     = array();
                    $post_vars['quoteOrderQty']    = $asset_quantity;
                    $post_vars['newClientOrderId'] = $new_order_id;
                    $post_vars['type']             = 'MARKET';

                    $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                    if ($_response === null) {
                        Functions::error_log('createBuyOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createBuyOrder';
                        goto page_end;
                    } elseif ($_response['result'] != 'success') {
                        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createBuyOrder';
                        goto page_end;
                    }
                    // create buy order - end

                    // check if buy order request returned status - begin
                    if (empty($_response['values']['info']['status']) == true) {
                        Functions::error_log('EMPTY_STATUS_CREATE_BUY_ORDER:' . $_response);
                        goto page_end;
                    }
                    // check if buy order request returned status - end

                    Functions::error_log('MARKET_BUY_ORDER_RESPONSE:' . json_encode($_response['values']['info']));

                    $order_info = $_response['values']['info'];
                    if ($order_info['status'] !== 'FILLED') {
                        Functions::error_log('LIMIT_BUY_ORDER_ERROR:' . json_encode($order_info));
                        goto page_end;
                    }

                    // calculate balance - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  += $order_info['executedQty'];
                    $diff_quote_asset_balance -= $order_info['cummulativeQuoteQty'];

                    foreach ($order_info['fills'] as $fill) {
                        if ($fill['commissionAsset'] == $system_settings['base_asset']) {
                            $diff_base_asset_balance -= $fill['commission'];
                        } elseif ($fill['commissionAsset'] == $system_settings['quote_asset']) {
                            $diff_quote_asset_balance -= $fill['commission'];
                        }
                    }
                    // calculate balance - end

                    // update status - begin
                    $id               = $system_settings['id'];
                    $time_now         = time();
                    $binance_order_id = $order_info['orderId'];
                    $buy_price        = $order_info['cummulativeQuoteQty'] / $order_info['executedQty'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_to_sell',
buy_order_date = :buy_order_date,
buy_price = :buy_price,
last_order_id = :new_order_id,
number_of_trades = number_of_trades + 1,
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':buy_order_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':buy_price', $buy_price, $buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':new_order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
INSERT INTO transactions(
insert_date,
order_type,
price,
quantity,
order_id,
binance_order_id,
order_status
)
VALUES
(
:insert_date,
'buy',
:price,
:quantity,
:order_id,
:binance_order_id,
'done'
);
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':insert_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':price', $buy_price, $buy_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':quantity', $asset_quantity, $asset_quantity !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':binance_order_id', $binance_order_id, $binance_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                             = $system_settings;
                    $response['values']['info']['status']                   = 'waiting_to_sell';
                    $response['values']['info']['status_text']              = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']       = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']        = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['buy_price']                = $buy_price;
                    $response['values']['info']['last_order_id']            = $new_order_id;
                    $response['values']['info']['buy_order_date']           = $time_now;
                    $response['values']['info']['buy_order_date_formatted'] = date($system_settings['date_format'], $time_now);

                    goto page_end;
                }

                goto page_end;
            }
        } // process - operation_mode - trade - status - waiting_for_opportunity - end
        elseif ($system_settings['status'] == 'created_buy_order') { // process - operation_mode - trade - status - created_buy_order - begin
            // get system settings - begin
            $sql = $sql_system_settings;

            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            $r = $stmt->execute();
            if ($r === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $system_settings = $row;
            }
            $stmt->closeCursor();
            $system_settings = Functions::formatSystemSettings($system_settings);
            // get system settings - end

            // only check order if defined seconds have passed after last check - begin
            $can_check_order = false;
            if (time() > (int)$system_settings['last_order_check_date'] + (int)$system_settings['buy_order_check_interval']) {
                $can_check_order = true;
            }
            // only check order if defined seconds have passed after last check - end

            if ($can_check_order === true) {
                check_buy_order_status:

                // check order status - begin
                $get_vars           = array();
                $get_vars['action'] = 'getOrderDetails';

                $post_vars             = array();
                $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                $post_vars['order_id'] = $system_settings['last_order_id'];

                $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                if ($_response === null) {
                    Functions::error_log('getOrderDetails api_error' . json_encode($get_vars) . json_encode($post_vars));
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'getOrderDetails';
                    goto page_end;
                } elseif ($_response['result'] != 'success') {
                    Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'getOrderDetails';
                    goto page_end;
                }
                $order_details = $_response['values']['info'];
                // check order status - end

                $status = empty($order_details['status']) === false ? $order_details['status'] : null;
                if ($status === 'FILLED') { // status - 'FILLED' - begin

                    // guarantee trading fees without a request to myTrades method of binance api - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  += $order_details['executedQty'];
                    $diff_quote_asset_balance -= $order_details['cummulativeQuoteQty'];

                    $diff_base_asset_balance  -= abs($diff_base_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    $diff_quote_asset_balance -= abs($diff_quote_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    // guarantee trading fees without a request to myTrades method of binance api - end

                    // update status - begin
                    $id = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_to_sell',
last_order_check_date = 0,
number_of_trades = number_of_trades + 1,
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
UPDATE transactions
SET
order_status = 'done'
WHERE
order_id = :order_id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':order_id', $system_settings['last_order_id'], $system_settings['last_order_id'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                          = $system_settings;
                    $response['values']['info']['status']                = 'waiting_to_sell';
                    $response['values']['info']['status_text']           = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']    = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']     = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['last_order_check_date'] = 0;

                    goto page_end;
                } // status - 'FILLED' - end
                elseif ($status === 'CANCELED') { // status - 'CANCELED' - begin

                    // guarantee trading fees without a request to myTrades method of binance api - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  += $order_details['executedQty'];
                    $diff_quote_asset_balance -= $order_details['cummulativeQuoteQty'];

                    $diff_base_asset_balance  -= abs($diff_base_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    $diff_quote_asset_balance -= abs($diff_quote_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    // guarantee trading fees without a request to myTrades method of binance api - end

                    // update database - begin
                    $id = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_for_opportunity',
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
buy_order_date = NULL,
last_order_check_date = NULL,
last_order_id = NULL,
buy_price = NULL,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
UPDATE transactions
SET
order_status = 'cancelled'
WHERE
order_id = :order_id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':order_id', $system_settings['last_order_id'], $system_settings['last_order_id'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update database - end

                    $response['values']['info']                             = $system_settings;
                    $response['values']['info']['status']                   = 'waiting_for_opportunity';
                    $response['values']['info']['status_text']              = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['target_sell_price']        = null;
                    $response['values']['info']['buy_order_date']           = null;
                    $response['values']['info']['buy_order_date_formatted'] = '';
                    $response['values']['info']['last_order_check_date']    = null;
                    $response['values']['info']['last_order_id']            = null;
                    $response['values']['info']['buy_price']                = null;
                    $response['values']['info']['current_buy_price_ratio']  = null;
                    goto page_end;
                    // update database - end

                } // status - 'CANCELED' - end
                else { // status - ELSE - begin

                    // buy now - begin
                    if ($is_active_buy_now == true) {
                        // cancel order, set status = 'waiting_for_opportunity', set buy_now = true
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto check_buy_order_status;
                    }
                    // buy now - end

                    // cancel now - begin
                    if ($is_active_cancel_now == true) {
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto check_buy_order_status;
                    }
                    // cancel now - end

                    // if the permitted duration is exceeded, cancel order - begin
                    if ($system_settings['is_enabled_rule_7'] == true && time() > $system_settings['buy_order_date'] + $system_settings['buy_order_validity']) {
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto check_buy_order_status;
                    }
                    // if the permitted duration is exceeded, cancel order - end

                    // update last order check date - begin
                    $time_now = time();
                    $id       = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
last_order_check_date = :time_now
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':time_now', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update last order check date - end
                    $response['values']['info']['last_order_check_date'] = $time_now;
                } // status - ELSE - end

            }

            goto page_end;
        } // process - operation_mode - trade - status - created_buy_order - end
        elseif ($system_settings['status'] == 'waiting_to_sell') { // process - operation_mode - trade - status - waiting_to_sell - begin
            waiting_to_sell:

            // get system settings - begin
            $sql = $sql_system_settings;

            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            $r = $stmt->execute();
            if ($r === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $system_settings = $row;
            }
            $stmt->closeCursor();
            $system_settings = Functions::formatSystemSettings($system_settings);
            // get system settings - end

            // escape plan - begin
            if ($system_settings['is_enabled_auto_sell'] == true && $system_settings['is_enabled_rule_10'] == true && $system_settings['buy_price'] / $system_settings['asset_price'] > $system_settings['max_buy_current_ratio']) {

                // create market sell order - begin
                $new_order_id = (int)(microtime(true) * 1000);

                $system_settings['asset_lot_step_size'] = floatval($system_settings['asset_lot_step_size']);

                $lot_precision   = abs(log($system_settings['asset_lot_step_size'], 10));
                $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                $asset_quantity = $system_settings['base_asset_balance'];
                $asset_quantity = $asset_quantity * (1 - ($system_settings['trading_fee_rate'] / 100));
                if ($system_settings['asset_lot_step_size'] > 0) {
                    $asset_quantity = $asset_quantity - fmod($asset_quantity, $system_settings['asset_lot_step_size']);
                }
                $asset_quantity = number_format($asset_quantity, $lot_precision, '.', '');

                $get_vars           = array();
                $get_vars['action'] = 'createSellOrder';

                $post_vars                     = array();
                $post_vars['quantity']         = $asset_quantity;
                $post_vars['newClientOrderId'] = $new_order_id;
                $post_vars['type']             = 'MARKET';

                $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                if ($_response === null) {
                    Functions::error_log('createSellOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'createSellOrder';
                    goto page_end;
                } elseif ($_response['result'] != 'success') {
                    Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'createSellOrder';
                    goto page_end;
                }
                // create market sell order - end

                // check if sell order request returned status - begin
                if (empty($_response['values']['info']['status']) == true) {
                    Functions::error_log('EMPTY_STATUS_CREATE_SELL_ORDER:' . $_response);
                    goto page_end;
                }
                // check if sell order request returned status - end

                Functions::error_log('MARKET_SELL_ORDER_RESPONSE:' . json_encode($_response['values']['info']));

                $order_info = $_response['values']['info'];
                if ($order_info['status'] !== 'FILLED') {
                    Functions::error_log('MARKET_SELL_ORDER_ERROR:' . json_encode($order_info));
                    goto page_end;
                }

                // calculate balance - begin
                $diff_base_asset_balance  = 0;
                $diff_quote_asset_balance = 0;

                $diff_base_asset_balance  -= $order_info['executedQty'];
                $diff_quote_asset_balance += $order_info['cummulativeQuoteQty'];

                foreach ($order_info['fills'] as $fill) {
                    if ($fill['commissionAsset'] == $system_settings['base_asset']) {
                        $diff_base_asset_balance -= $fill['commission'];
                    } elseif ($fill['commissionAsset'] == $system_settings['quote_asset']) {
                        $diff_quote_asset_balance -= $fill['commission'];
                    }
                }
                // calculate balance - end

                // update status - begin
                $id               = $system_settings['id'];
                $time_now         = time();
                $binance_order_id = $order_info['orderId'];
                $sell_price       = $order_info['cummulativeQuoteQty'] / $order_info['executedQty'];
                if ($system_settings['asset_price_tick_size'] > 0) {
                    $sell_price = $sell_price - fmod($sell_price, $system_settings['asset_price_tick_size']);
                }
                $sell_price      = number_format($sell_price, $price_precision, '.', '');
                $last_sell_price = $sell_price;

                $sql
                    = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_for_opportunity',
number_of_trades = number_of_trades + 1,
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
asset_price = NULL,
buy_price = NULL,
sell_price = NULL,
last_sell_price = :last_sell_price,
buy_order_date = NULL,
sell_order_date = NULL,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                    $has_error         = true;
                    $response['error'] = 'updateSystemSettings';
                    goto page_end;
                }

                // Bind values
                $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':last_sell_price', $last_sell_price, $last_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                $r = $stmt->execute();
                if ($r === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                    $page_vars['form_success']  = false;
                    $page_vars['form_errors'][] = $db->errorCode();
                    goto page_end;
                }

                $sql
                    = <<<EOF
INSERT INTO transactions(
insert_date,
order_type,
price,
order_id,
binance_order_id,
order_status
)
VALUES
(
:insert_date,
'sell',
:price,
:order_id,
:binance_order_id,
'done'
);
EOF;

                $stmt = $db->prepare($sql);
                if ($stmt === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                    $has_error         = true;
                    $response['error'] = 'updateSystemSettings';
                    goto page_end;
                }

                // Bind values
                $stmt->bindValue(':insert_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                $stmt->bindValue(':price', $sell_price, $sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                $stmt->bindValue(':binance_order_id', $binance_order_id, $binance_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                $r = $stmt->execute();
                if ($r === false) {
                    Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                    $page_vars['form_success']  = false;
                    $page_vars['form_errors'][] = $db->errorCode();
                    goto page_end;
                }
                // update status - end

                $response['values']['info']                                  = $system_settings;
                $response['values']['info']['status']                        = 'waiting_for_opportunity';
                $response['values']['info']['status_text']                   = Functions::statusToText($response['values']['info']['status']);
                $response['values']['info']['current_trend_text']            = Functions::trendToText($response['values']['info']['current_trend']);
                $response['values']['info']['target_sell_price']             = Functions::targetSellPrice($system_settings);
                $response['values']['info']['number_of_trades']              = $system_settings['number_of_trades'] + 1;
                $response['values']['info']['last_order_check_date']         = 0;
                $response['values']['info']['interval_h']                    = null;
                $response['values']['info']['interval_l']                    = null;
                $response['values']['info']['interval_hl_ratio']             = null;
                $response['values']['info']['asset_price']                   = null;
                $response['values']['info']['previous_asset_price']          = null;
                $response['values']['info']['price_diff']                    = 0;
                $response['values']['info']['sell_price']                    = null;
                $response['values']['info']['last_sell_price']               = $last_sell_price;
                $response['values']['info']['interval_start_date']           = null;
                $response['values']['info']['interval_start_date_formatted'] = '';
                $response['values']['info']['buy_order_date']                = null;
                $response['values']['info']['buy_order_date_formatted']      = '';
                $response['values']['info']['sell_order_date']               = null;
                $response['values']['info']['sell_order_date_formatted']     = '';
                $response['values']['info']['current_buy_price_ratio']       = null;

                // stop if the rule is enabled - begin
                if ($system_settings['is_enabled_rule_14'] == true) {
                    // stop processing - begin
                    $id = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
is_running = 0,
`status` = 'waiting_for_opportunity',
asset_price = NULL,
buy_order_date = NULL,
sell_order_date = NULL,
interval_start_date = NULL,
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
last_order_check_date = NULL,
last_order_id = NULL,
number_of_trades = 0,
buy_price = NULL,
sell_price = NULL,
last_sell_price = NULL,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update database - end
                    $response['values']['info']                                  = $system_settings;
                    $response['values']['info']['is_running']                    = 0;
                    $response['values']['info']['status']                        = 'waiting_for_opportunity';
                    $response['values']['info']['status_text']                   = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['target_sell_price']             = null;
                    $response['values']['info']['buy_order_date']                = null;
                    $response['values']['info']['buy_order_date_formatted']      = '';
                    $response['values']['info']['sell_order_date']               = null;
                    $response['values']['info']['sell_order_date_formatted']     = '';
                    $response['values']['info']['interval_start_date']           = null;
                    $response['values']['info']['interval_start_date_formatted'] = '';
                    $response['values']['info']['interval_h']                    = null;
                    $response['values']['info']['interval_l']                    = null;
                    $response['values']['info']['interval_hl_ratio']             = null;
                    $response['values']['info']['last_order_check_date']         = null;
                    $response['values']['info']['last_order_id']                 = null;
                    $response['values']['info']['buy_price']                     = null;
                    $response['values']['info']['sell_price']                    = null;
                    $response['values']['info']['number_of_trades']              = 0;
                    $response['values']['info']['current_buy_price_ratio']       = null;
                    $response['values']['info']['current_trend']                 = null;
                    $response['values']['info']['last_set_boundary']             = null;
                    // stop processing - end

                    goto page_end;
                }
                // stop if the rule is enabled - end

                Functions::error_log('escaped');

                goto page_end;
            }
            // escape plan - end

            // sell now | profit sale - begin
            if ($is_active_sell_now == true || $system_settings['is_enabled_auto_sell'] == true) {

                if ($is_active_sell_now == false) {
                    // create sell order - begin
                    $lot_precision   = abs(log($system_settings['asset_lot_step_size'], 10));
                    $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                    $asset_quantity = $system_settings['base_asset_balance'];
                    $asset_quantity = $asset_quantity * (1 - ($system_settings['trading_fee_rate'] / 100));
                    if ($system_settings['asset_lot_step_size'] > 0) {
                        $asset_quantity = $asset_quantity - fmod($asset_quantity, $system_settings['asset_lot_step_size']);
                    }
                    $asset_quantity = number_format($asset_quantity, $lot_precision, '.', '');

                    $sell_price = $system_settings['buy_price'] * $system_settings['sell_buy_price_ratio'];
                    if ($system_settings['asset_price_tick_size'] > 0) {
                        $sell_price = $sell_price - fmod($sell_price, $system_settings['asset_price_tick_size']);
                    }
                    $sell_price = number_format($sell_price, $price_precision, '.', '');

                    $new_order_id = (int)(microtime(true) * 1000);

                    $get_vars           = array();
                    $get_vars['action'] = 'createSellOrder';

                    $post_vars                     = array();
                    $post_vars['price']            = $sell_price;
                    $post_vars['quantity']         = $asset_quantity;
                    $post_vars['newClientOrderId'] = $new_order_id;
                    $post_vars['type']             = 'LIMIT';
                    $post_vars['timeInForce']      = 'GTC';

                    $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                    if ($_response === null) {
                        Functions::error_log('createSellOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createSellOrder';
                        goto page_end;
                    } elseif ($_response['result'] != 'success') {
                        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createSellOrder';
                        goto page_end;
                    }
                    // create sell order - end

                    // check if sell order request returned status - begin
                    if (empty($_response['values']['info']['status']) == true) {
                        Functions::error_log('EMPTY_STATUS_CREATE_SELL_ORDER:' . $_response);
                        goto page_end;
                    }
                    // check if sell order request returned status - end

                    $order_info = $_response['values']['info'];

                    // update status - begin
                    $id               = $system_settings['id'];
                    $time_now         = time();
                    $binance_order_id = $order_info['orderId'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'created_sell_order',
sell_price = :sell_price,
sell_order_date = :sell_order_date,
last_order_id = :new_order_id,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':sell_price', $sell_price, $sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':sell_order_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':new_order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
INSERT INTO transactions(
insert_date,
order_type,
price,
quantity,
order_id,
binance_order_id,
order_status
)
VALUES
(
:insert_date,
'sell',
:price,
:quantity,
:order_id,
:binance_order_id,
'new'
);
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':insert_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':price', $sell_price, $sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':quantity', $asset_quantity, $asset_quantity !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':binance_order_id', $binance_order_id, $binance_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                              = $system_settings;
                    $response['values']['info']['status']                    = 'created_sell_order';
                    $response['values']['info']['status_text']               = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']        = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']         = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['sell_price']                = $sell_price;
                    $response['values']['info']['sell_order_date']           = $time_now;
                    $response['values']['info']['sell_order_date_formatted'] = date($system_settings['date_format'], $time_now);

                    goto page_end;
                } elseif ($is_active_sell_now == true) {
                    // create sell order - begin
                    $new_order_id = (int)(microtime(true) * 1000);

                    $system_settings['asset_lot_step_size'] = floatval($system_settings['asset_lot_step_size']);

                    $lot_precision   = abs(log($system_settings['asset_lot_step_size'], 10));
                    $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                    $asset_quantity = $system_settings['base_asset_balance'];
                    $asset_quantity = $asset_quantity * (1 - ($system_settings['trading_fee_rate'] / 100));
                    if ($system_settings['asset_lot_step_size'] > 0) {
                        $asset_quantity = $asset_quantity - fmod($asset_quantity, $system_settings['asset_lot_step_size']);
                    }
                    $asset_quantity = number_format($asset_quantity, $lot_precision, '.', '');

                    $get_vars           = array();
                    $get_vars['action'] = 'createSellOrder';

                    $post_vars                     = array();
                    $post_vars['quantity']         = $asset_quantity;
                    $post_vars['newClientOrderId'] = $new_order_id;
                    $post_vars['type']             = 'MARKET';

                    $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                    if ($_response === null) {
                        Functions::error_log('createSellOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createSellOrder';
                        goto page_end;
                    } elseif ($_response['result'] != 'success') {
                        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                        $has_error                     = true;
                        $response['error']             = $_response['error'];
                        $response['error_description'] = 'createSellOrder';
                        goto page_end;
                    }
                    // create sell order - end

                    // check if sell order request returned status - begin
                    if (empty($_response['values']['info']['status']) == true) {
                        Functions::error_log('EMPTY_STATUS_CREATE_SELL_ORDER:' . $_response);
                        goto page_end;
                    }
                    // check if sell order request returned status - end

                    Functions::error_log('MARKET_SELL_ORDER_RESPONSE:' . json_encode($_response['values']['info']));

                    $order_info = $_response['values']['info'];
                    if ($order_info['status'] !== 'FILLED') {
                        Functions::error_log('MARKET_SELL_ORDER_ERROR:' . json_encode($order_info));
                        goto page_end;
                    }

                    // calculate balance - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  -= $order_info['executedQty'];
                    $diff_quote_asset_balance += $order_info['cummulativeQuoteQty'];

                    foreach ($order_info['fills'] as $fill) {
                        if ($fill['commissionAsset'] == $system_settings['base_asset']) {
                            $diff_base_asset_balance -= $fill['commission'];
                        } elseif ($fill['commissionAsset'] == $system_settings['quote_asset']) {
                            $diff_quote_asset_balance -= $fill['commission'];
                        }
                    }
                    // calculate balance - end

                    // update status - begin
                    $id               = $system_settings['id'];
                    $time_now         = time();
                    $binance_order_id = $order_info['orderId'];
                    $sell_price       = $order_info['cummulativeQuoteQty'] / $order_info['executedQty'];
                    if ($system_settings['asset_price_tick_size'] > 0) {
                        $sell_price = $sell_price - fmod($sell_price, $system_settings['asset_price_tick_size']);
                    }
                    $sell_price      = number_format($sell_price, $price_precision, '.', '');
                    $last_sell_price = $sell_price;

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_for_opportunity',
number_of_trades = number_of_trades + 1,
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
asset_price = NULL,
buy_price = NULL,
sell_price = NULL,
last_sell_price = :last_sell_price,
buy_order_date = NULL,
sell_order_date = NULL,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':last_sell_price', $last_sell_price, $last_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
INSERT INTO transactions(
insert_date,
order_type,
price,
quantity,
order_id,
binance_order_id,
order_status
)
VALUES
(
:insert_date,
'sell',
:price,
:quantity,
:order_id,
:binance_order_id,
'done'
);
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':insert_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':price', $sell_price, $sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':quantity', $asset_quantity, $asset_quantity !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':order_id', $new_order_id, $new_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':binance_order_id', $binance_order_id, $binance_order_id !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                                  = $system_settings;
                    $response['values']['info']['status']                        = 'waiting_for_opportunity';
                    $response['values']['info']['status_text']                   = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']            = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']             = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['number_of_trades']              = $system_settings['number_of_trades'] + 1;
                    $response['values']['info']['last_order_check_date']         = 0;
                    $response['values']['info']['interval_h']                    = null;
                    $response['values']['info']['interval_l']                    = null;
                    $response['values']['info']['interval_hl_ratio']             = null;
                    $response['values']['info']['asset_price']                   = null;
                    $response['values']['info']['previous_asset_price']          = null;
                    $response['values']['info']['price_diff']                    = 0;
                    $response['values']['info']['sell_price']                    = null;
                    $response['values']['info']['last_sell_price']               = $last_sell_price;
                    $response['values']['info']['interval_start_date']           = null;
                    $response['values']['info']['interval_start_date_formatted'] = '';
                    $response['values']['info']['buy_order_date']                = null;
                    $response['values']['info']['buy_order_date_formatted']      = '';
                    $response['values']['info']['sell_order_date']               = null;
                    $response['values']['info']['sell_order_date_formatted']     = '';
                    $response['values']['info']['current_buy_price_ratio']       = null;

                    goto page_end;
                }
            }
            // sell now | profit sale - end

            goto page_end;

        } // process - operation_mode - trade - status - waiting_to_sell - end
        elseif ($system_settings['status'] == 'created_sell_order') { // process - operation_mode - trade - status - created_sell_order - begin
            // get system settings - begin
            $sql = $sql_system_settings;

            $stmt = $db->prepare($sql);
            if ($stmt === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            $r = $stmt->execute();
            if ($r === false) {
                Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                $has_error         = true;
                $response['error'] = 'fetchSystemSettings';
                goto page_end;
            }
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $system_settings = $row;
            }
            $stmt->closeCursor();
            $system_settings = Functions::formatSystemSettings($system_settings);
            // get system settings - end

            // only check order if defined seconds have passed after last check - begin
            $can_check_order = false;
            if (time() > (int)$system_settings['last_order_check_date'] + (int)$system_settings['sell_order_check_interval']) {
                $can_check_order = true;
            }
            // only check order if defined seconds have passed after last check - end

            if ($can_check_order === true) {
                check_sell_order_status:

                // check order status - begin
                $get_vars           = array();
                $get_vars['action'] = 'getOrderDetails';

                $post_vars             = array();
                $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                $post_vars['order_id'] = $system_settings['last_order_id'];

                $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                if ($_response === null) {
                    Functions::error_log('getOrderDetails api_error' . json_encode($get_vars) . json_encode($post_vars));
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'getOrderDetails';
                    goto page_end;
                } elseif ($_response['result'] != 'success') {
                    Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                    $has_error                     = true;
                    $response['error']             = $_response['error'];
                    $response['error_description'] = 'getOrderDetails';
                    goto page_end;
                }
                $order_details = $_response['values']['info'];
                // check order status - end

                $status = empty($order_details['status']) === false ? $order_details['status'] : null;
                if ($status === 'FILLED') { // status - 'FILLED' - begin

                    $price_precision = abs(log($system_settings['asset_price_tick_size'], 10));

                    // guarantee trading fees without a request to myTrades method of binance api - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  -= $order_details['executedQty'];
                    $diff_quote_asset_balance += $order_details['cummulativeQuoteQty'];

                    $diff_base_asset_balance  -= abs($diff_base_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    $diff_quote_asset_balance -= abs($diff_quote_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    // guarantee trading fees without a request to myTrades method of binance api - end

                    $last_sell_price = $system_settings['sell_price'];
                    if ($system_settings['asset_price_tick_size'] > 0) {
                        $last_sell_price = $last_sell_price - fmod($last_sell_price, $system_settings['asset_price_tick_size']);
                    }
                    $last_sell_price = number_format($last_sell_price, $price_precision, '.', '');

                    // update status - begin
                    $id = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_for_opportunity',
number_of_trades = number_of_trades + 1,
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
buy_price = NULL,
sell_price = NULL,
last_sell_price = :last_sell_price,
buy_order_date = NULL,
sell_order_date = NULL,
last_order_check_date = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':last_sell_price', $last_sell_price, $last_sell_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
UPDATE transactions
SET
order_status = 'done'
WHERE
order_id = :order_id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':order_id', $system_settings['last_order_id'], $system_settings['last_order_id'] !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update status - end

                    $response['values']['info']                              = $system_settings;
                    $response['values']['info']['status']                    = 'waiting_for_opportunity';
                    $response['values']['info']['status_text']               = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['current_trend_text']        = Functions::trendToText($response['values']['info']['current_trend']);
                    $response['values']['info']['target_sell_price']         = null;
                    $response['values']['info']['number_of_trades']          = $system_settings['number_of_trades'] + 1;
                    $response['values']['info']['last_order_check_date']     = 0;
                    $response['values']['info']['sell_price']                = null;
                    $response['values']['info']['last_sell_price']           = $last_sell_price;
                    $response['values']['info']['buy_order_date']            = null;
                    $response['values']['info']['buy_order_date_formatted']  = '';
                    $response['values']['info']['sell_order_date']           = null;
                    $response['values']['info']['sell_order_date_formatted'] = '';
                    $response['values']['info']['current_buy_price_ratio']   = null;

                    // reset interval - begin
                    if ($system_settings['is_enabled_rule_15'] == 1) {
                        $time_now = time();
                        $id       = $system_settings['id'];

                        $sql
                              = <<<EOF
UPDATE system_settings
SET
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
asset_price = NULL,
previous_asset_price = NULL,
interval_start_date = :interval_start_date,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;
                        $stmt = $db->prepare($sql);
                        if ($stmt === false) {
                            Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                            $has_error         = true;
                            $response['error'] = 'updateSystemSettings';
                            goto page_end;
                        }

                        // Bind values
                        $stmt->bindValue(':interval_start_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                        $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                        $r = $stmt->execute();
                        if ($r === false) {
                            Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                            $page_vars['form_success']  = false;
                            $page_vars['form_errors'][] = $db->errorCode();
                            goto page_end;
                        }

                        $response['values']['info']['interval_h']                    = null;
                        $response['values']['info']['interval_l']                    = null;
                        $response['values']['info']['interval_hl_ratio']             = null;
                        $response['values']['info']['current_buy_price_ratio']       = null;
                        $response['values']['info']['asset_price']                   = null;
                        $response['values']['info']['previous_asset_price']          = null;
                        $response['values']['info']['price_diff']                    = 0;
                        $response['values']['info']['interval_start_date']           = $time_now;
                        $response['values']['info']['interval_start_date_formatted'] = date($system_settings['date_format'], $time_now);
                    }
                    // reset interval - end

                    goto page_end;
                } // status - 'FILLED' - end
                elseif ($status === 'CANCELED') { // status - 'CANCELED' - begin

                    // guarantee trading fees without a request to myTrades method of binance api - begin
                    $diff_base_asset_balance  = 0;
                    $diff_quote_asset_balance = 0;

                    $diff_base_asset_balance  -= $order_details['executedQty'];
                    $diff_quote_asset_balance += $order_details['cummulativeQuoteQty'];

                    $diff_base_asset_balance  -= abs($diff_base_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    $diff_quote_asset_balance -= abs($diff_quote_asset_balance) * ($system_settings['trading_fee_rate'] / 100);
                    // guarantee trading fees without a request to myTrades method of binance api - end

                    // update database - begin
                    $id = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
`status` = 'waiting_to_sell',
base_asset_balance = base_asset_balance + :diff_base_asset_balance,
quote_asset_balance = quote_asset_balance + :diff_quote_asset_balance,
sell_order_date = NULL,
last_order_check_date = NULL,
last_order_id = NULL,
sell_price = NULL,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':diff_base_asset_balance', $diff_base_asset_balance, $diff_base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':diff_quote_asset_balance', $diff_quote_asset_balance, $diff_quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }

                    $sql
                        = <<<EOF
UPDATE transactions
SET
order_status = 'cancelled'
WHERE
order_id = :order_id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }
                    // update database - end

                    $response['values']['info']                              = $system_settings;
                    $response['values']['info']['status']                    = 'waiting_to_sell';
                    $response['values']['info']['status_text']               = Functions::statusToText($response['values']['info']['status']);
                    $response['values']['info']['target_sell_price']         = Functions::targetSellPrice($system_settings);
                    $response['values']['info']['sell_order_date']           = null;
                    $response['values']['info']['sell_order_date_formatted'] = '';
                    $response['values']['info']['last_order_check_date']     = null;
                    $response['values']['info']['last_order_id']             = null;
                    $response['values']['info']['sell_price']                = null;

                    if ($is_active_sell_now == true) {
                        goto waiting_to_sell;
                    }

                    goto page_end;
                    // update database - end

                } // status - 'CANCELED' - end
                else { // status - ELSE - begin

                    // escape condition - begin
                    if ($system_settings['is_enabled_auto_sell'] == true && $system_settings['is_enabled_rule_10'] == true && $system_settings['buy_price'] / $system_settings['asset_price'] > $system_settings['max_buy_current_ratio']) {
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto page_end;
                    } // escape condition - end

                    // sell now - begin
                    if ($is_active_sell_now == true) {
                        // cancel order, set status = 'waiting_to_sell', set sell_now = true
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto check_sell_order_status;
                    } // sell now - end

                    // cancel now - begin
                    if ($is_active_cancel_now == true) {
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        goto check_sell_order_status;
                    }
                    // cancel now - end

                    // if the permitted duration is exceeded, cancel order, create market-sell order - begin
                    if ($system_settings['is_enabled_rule_8'] == true && time() > $system_settings['sell_order_date'] + $system_settings['sell_order_validity']) {
                        $get_vars           = array();
                        $get_vars['action'] = 'cancelOrder';

                        $post_vars             = array();
                        $post_vars['symbol']   = $system_settings['base_asset'] . $system_settings['quote_asset'];
                        $post_vars['order_id'] = $system_settings['last_order_id'];

                        $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
                        if ($_response === null) {
                            Functions::error_log('cancelOrder api_error' . json_encode($get_vars) . json_encode($post_vars));
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        } elseif ($_response['result'] != 'success') {
                            Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
                            $has_error                     = true;
                            $response['error']             = $_response['error'];
                            $response['error_description'] = 'cancelOrder';
                            goto page_end;
                        }
                        $is_active_sell_now = 1;
                        goto check_sell_order_status;
                    } // if the permitted duration is exceeded, cancel order, create market-sell order - end

                    // update last order check date - begin
                    $time_now = time();
                    $id       = $system_settings['id'];

                    $sql
                        = <<<EOF
UPDATE system_settings
SET
last_order_check_date = :time_now
WHERE
id = :id;
EOF;

                    $stmt = $db->prepare($sql);
                    if ($stmt === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
                        $has_error         = true;
                        $response['error'] = 'updateSystemSettings';
                        goto page_end;
                    }

                    // Bind values
                    $stmt->bindValue(':time_now', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
                    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

                    $r = $stmt->execute();
                    if ($r === false) {
                        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
                        $page_vars['form_success']  = false;
                        $page_vars['form_errors'][] = $db->errorCode();
                        goto page_end;
                    }
                    // update last order check date - end

                    $response['values']['info']['last_order_check_date'] = $time_now;

                    goto page_end;
                } // status - ELSE - end

            } // process - operation_mode - trade - status - created_sell_order - end

        } // process - operation_mode - trade - end

    }
} // process - end
elseif ($_GET['action'] == 'pauseProcessing') { // pause processing - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    if ($system_settings['is_running'] == false) {
        goto page_end;
    }

    // update database - begin
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_running = 0,
asset_price = NULL,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_running'] = 0;

} // pause processing - end
elseif ($_GET['action'] == 'startProcessing') { // start processing - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    if ($system_settings['is_running'] == true) {
        goto page_end;
    }

    // update database - begin
    $time_now = null;
    if (empty($system_settings['interval_start_date']) == true) {
        $time_now = time();
    }
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_running = 1,
interval_start_date = CASE WHEN :interval_start_date IS NULL THEN interval_start_date ELSE :interval_start_date2 END,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':interval_start_date', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':interval_start_date2', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_running'] = 1;

    if ($time_now !== null) {
        $response['values']['info']['interval_start_date']           = $time_now;
        $response['values']['info']['interval_start_date_formatted'] = date($system_settings['date_format'], $time_now);
    }

} // start processing - end
elseif ($_GET['action'] == 'stopProcessing') { // stop processing - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    // update database - begin
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_running = 0,
`status` = 'waiting_for_opportunity',
asset_price = NULL,
buy_order_date = NULL,
sell_order_date = NULL,
interval_start_date = NULL,
interval_h = NULL,
interval_l = NULL,
current_trend = NULL,
last_set_boundary = NULL,
last_order_check_date = NULL,
last_order_id = NULL,
buy_price = NULL,
sell_price = NULL,
last_sell_price = NULL,
number_of_trades = 0,
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_running']                    = 0;
    $response['values']['info']['interval_start_date']           = null;
    $response['values']['info']['interval_start_date_formatted'] = '';

} // stop processing - end
elseif ($_GET['action'] == 'updateAccountBalance') { // update account balance - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    // get account info - begin
    $get_vars           = array();
    $get_vars['action'] = 'getAccountInfo';

    $post_vars = array();

    $_response = ApiClient::makeRequest($db_settings['system_api_url'], $get_vars, $post_vars);
    if ($_response === null) {
        Functions::error_log('getAccountInfo api_error');
        $has_error                     = true;
        $response['error']             = $_response['error'];
        $response['error_description'] = 'getAccountInfo';
        goto page_end;
    } elseif ($_response['result'] != 'success') {
        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
        $has_error                     = true;
        $response['error']             = $_response['error'];
        $response['error_description'] = 'getAccountInfo';
        goto page_end;
    }
    $account_info = $_response['values']['info'];
    // get account info - end

    // update database - begin
    $id                  = $system_settings['id'];
    $quote_asset_balance = 0;
    $base_asset_balance  = 0;

    foreach ($account_info['balances'] as $v) {
        if ($v['asset'] == $system_settings['quote_asset']) {
            $quote_asset_balance = $v['free'];
        }

        if ($v['asset'] == $system_settings['base_asset']) {
            $base_asset_balance = $v['free'];
        }
    }

    $sql
        = <<<EOF
UPDATE system_settings
SET
quote_asset_balance = :quote_asset_balance,
base_asset_balance = :base_asset_balance
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':quote_asset_balance', $quote_asset_balance, $quote_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':base_asset_balance', $base_asset_balance, $base_asset_balance !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['quote_asset_balance'] = $quote_asset_balance;
    $response['values']['info']['base_asset_balance']  = $base_asset_balance;
} // update account balance - end
elseif ($_GET['action'] == 'updateAssetPrice') { // update asset price - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    // get latest price - begin
    $get_vars           = array();
    $get_vars['action'] = 'getLatestPrice';

    $post_vars           = array();
    $post_vars['symbol'] = $system_settings['base_asset'] . $system_settings['quote_asset'];

    $_response = ApiClient::makePostRequest($db_settings['system_api_url'], $get_vars, $post_vars);
    if ($_response === null) {
        Functions::error_log('getLatestPrice api_error' . json_encode($get_vars) . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = $_response['error'];
        $response['error_description'] = 'getLatestPrice';
        goto page_end;
    } elseif ($_response['result'] != 'success') {
        Functions::error_log(json_encode($_response) . json_encode($get_vars) . json_encode($post_vars) . ' status=' . $system_settings['status']);
        $has_error                     = true;
        $response['error']             = $_response['error'];
        $response['error_description'] = 'getLatestPrice';
        goto page_end;
    }
    $latest_price = $_response['values']['info']['price'];
    // get latest price - end

    // decide current direction - begin
    $time_now = time();

    $sql
        = <<<EOF
INSERT INTO price_history
(
insert_date,
price
)
VALUES
(
:time_now,
:price
);
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':time_now', $time_now, $time_now !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':price', $latest_price, $latest_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    $insert_id = $db->lastInsertId();

    $current_trend        = null;
    $last_set_boundary    = $system_settings['last_set_boundary'];
    $first_average_price  = 0;
    $second_average_price = 0;

    # first average price
    $upper_time = time();
    $lower_time = $upper_time - $system_settings['trend_calculation_first_duration'];

    $sql
        = <<<EOF
SELECT
AVG(price) AS average_price
FROM price_history
WHERE
insert_date > :lower_date
AND insert_date <= :upper_date;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'averagePrices';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':upper_date', $upper_time, $upper_time !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':lower_date', $lower_time, $lower_time !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'averagePrices';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($row['average_price']) === false) {
            $first_average_price = floatval($row['average_price']);
        }
    }
    $stmt->closeCursor();

    # second average price
    $upper_time = $lower_time;
    $lower_time = $upper_time - $system_settings['trend_calculation_second_duration'];

    $sql
        = <<<EOF
SELECT
AVG(price) AS average_price
FROM price_history
WHERE
insert_date > :lower_date
AND insert_date <= :upper_date;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'averagePrices';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':upper_date', $upper_time, $upper_time !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
    $stmt->bindValue(':lower_date', $lower_time, $lower_time !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'averagePrices';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (empty($row['average_price']) === false) {
            $second_average_price = floatval($row['average_price']);
        }
    }
    $stmt->closeCursor();

    if ($first_average_price == 0 || $second_average_price == 0) {
        $current_trend = null;
    } elseif ($first_average_price > $second_average_price) {
        $current_trend = 'upward';
    } elseif ($first_average_price < $second_average_price) {
        $current_trend = 'downward';
    }
    // decide current trend - end

    // update database - begin
    $id          = $system_settings['id'];
    $asset_price = $latest_price;
    $interval_h  = $latest_price > $system_settings['interval_h'] || empty($system_settings['interval_h']) ? $latest_price : null;
    $interval_l  = $latest_price < $system_settings['interval_l'] || empty($system_settings['interval_l']) ? $latest_price : null;

    if ($interval_h !== null) {
        $last_set_boundary = 'high';
    } elseif ($interval_l !== null) {
        $last_set_boundary = 'low';
    }

    $sql
        = <<<EOF
UPDATE system_settings
SET
previous_asset_price = asset_price,
asset_price = :asset_price,
interval_h = (CASE WHEN :interval_h IS NULL THEN interval_h ELSE :interval_h2 END),
interval_l = (CASE WHEN :interval_l IS NULL THEN interval_l ELSE :interval_l2 END),
current_trend = :current_trend,
last_set_boundary = (CASE WHEN :last_set_boundary IS NULL THEN last_set_boundary ELSE :last_set_boundary2 END)
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':asset_price', $asset_price, $asset_price !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':interval_h', $interval_h, $interval_h !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':interval_h2', $interval_h, $interval_h !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':interval_l', $interval_l, $interval_l !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':interval_l2', $interval_l, $interval_l !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':current_trend', $current_trend, $current_trend !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':last_set_boundary', $last_set_boundary, $last_set_boundary !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':last_set_boundary2', $last_set_boundary, $last_set_boundary !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }

    $sql
        = <<<EOF
DELETE FROM price_history
WHERE
insert_date <= :lower_date;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':lower_date', $lower_time, $lower_time !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end

    if ($interval_h !== null) {
        $response['values']['info']['interval_h'] = $interval_h;
    }
    if ($interval_l !== null) {
        $response['values']['info']['interval_l'] = $interval_l;
    }
    if (empty($interval_h) === false && empty($interval_l) === false) {
        $response['values']['info']['interval_hl_ratio'] = $interval_h / $interval_l;
    }

    $response['values']['info']['current_buy_price_ratio'] = '';
    if (empty($response['values']['info']['asset_price']) === false && empty($response['values']['info']['buy_price']) === false) {
        $response['values']['info']['current_buy_price_ratio'] = $response['values']['info']['asset_price'] / $response['values']['info']['buy_price'];
    }

    if ($asset_price !== null) {
        $response['values']['info']['asset_price']          = $asset_price;
        $response['values']['info']['previous_asset_price'] = $system_settings['asset_price'];
        $response['values']['info']['price_diff']           = floatval($response['values']['info']['asset_price']) - floatval($response['values']['info']['previous_asset_price']);
        $response['values']['info']['current_trend']        = $current_trend;
        $response['values']['info']['last_set_boundary']    = $last_set_boundary;
    }

} // update asset price - end
elseif ($_GET['action'] == 'getAccountInfo') { // account info - begin
    $get_vars              = array();
    $get_vars['timestamp'] = (int)(microtime(true) * 1000);

    $queries = array();
    foreach ($get_vars as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $query     = implode('&', $queries);
    $signature = hash_hmac('SHA256', $query, $db_settings['binance_api_secret_key']);

    $get_vars['signature'] = $signature;

    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/account
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/account';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true);

    $response['binance_response'] = $api_response;
    $response['time']             = (int)(microtime(true) * 1000);

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // account info - end
elseif ($_GET['action'] == 'getAveragePrice') { // average price - begin
    $symbol = isset($_POST['symbol']) ? $_POST['symbol'] : null;

    $get_vars           = array();
    $get_vars['symbol'] = $symbol;

    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/avgPrice
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/avgPrice';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true);

    $response['binance_response'] = $api_response;

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // average price - end
elseif ($_GET['action'] == 'getLatestPrice') { // latest price - begin
    $symbol = isset($_POST['symbol']) ? $_POST['symbol'] : null;

    $get_vars           = array();
    $get_vars['symbol'] = $symbol;

    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/ticker/price
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/ticker/price';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true);

    $response['binance_response'] = $api_response;

    $r = json_decode($api_response, true);
    if ($r === null) {
        // Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // latest price - end
elseif ($_GET['action'] == 'createBuyOrder') { // create buy order - begin

    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $get_vars = array();

    $post_vars              = array();
    $post_vars['symbol']    = $system_settings['base_asset'] . $system_settings['quote_asset'];
    $post_vars['timestamp'] = (int)(microtime(true) * 1000);
    $post_vars['side']      = 'BUY';
    foreach ($_POST as $k => $v) {
        $post_vars[$k] = $v;
    }

    $queries = array();
    foreach ($post_vars as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $query                  = implode('&', $queries);
    $response['query']      = $query;
    $signature              = hash_hmac('SHA256', $query, $db_settings['binance_api_secret_key']);
    $post_vars['signature'] = $signature;

    $headers = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/order
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/order';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, true, true);

    $response['binance_response'] = $api_response;
    $response['time']             = $post_vars['timestamp'];

    Functions::error_log('CREATE_BUY_ORDER:' . $api_response);
    Functions::error_log('CREATE_BUY_ORDER:' . json_encode($post_vars));
    Functions::error_log('CREATE_BUY_ORDER:' . json_encode($get_vars));

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info']             = $r;
    $response['values']['info']['order_id'] = (empty($_POST['newClientOrderId']) ? '' : $_POST['newClientOrderId']);
} // create buy order - end
elseif ($_GET['action'] == 'createSellOrder') { // create sell order - begin

    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $get_vars = array();

    $post_vars              = array();
    $post_vars['symbol']    = $system_settings['base_asset'] . $system_settings['quote_asset'];
    $post_vars['timestamp'] = (int)(microtime(true) * 1000);
    $post_vars['side']      = 'SELL';
    foreach ($_POST as $k => $v) {
        $post_vars[$k] = $v;
    }

    $queries = array();
    foreach ($post_vars as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $query                  = implode('&', $queries);
    $response['query']      = $query;
    $signature              = hash_hmac('SHA256', $query, $db_settings['binance_api_secret_key']);
    $post_vars['signature'] = $signature;

    $headers = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/order
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/order';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, true, true);

    $response['binance_response'] = $api_response;
    $response['time']             = $post_vars['timestamp'];

    Functions::error_log('CREATE_SELL_ORDER:' . $api_response);
    Functions::error_log('CREATE_SELL_ORDER:' . json_encode($post_vars));
    Functions::error_log('CREATE_SELL_ORDER:' . json_encode($get_vars));

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info']             = $r;
    $response['values']['info']['order_id'] = (empty($_POST['newClientOrderId']) ? '' : $_POST['newClientOrderId']);
} // create sell order - end
elseif ($_GET['action'] == 'getOrderDetails') { // order details - begin
    $symbol           = isset($_POST['symbol']) ? $_POST['symbol'] : null;
    $order_id         = isset($_POST['order_id']) ? $_POST['order_id'] : null;
    $binance_order_id = isset($_POST['binance_order_id']) ? $_POST['binance_order_id'] : null;

    $get_vars              = array();
    $get_vars['timestamp'] = (int)(microtime(true) * 1000);
    $get_vars['symbol']    = $symbol;

    if (empty($binance_order_id) == false) {
        $get_vars['orderId'] = $binance_order_id;
    } else {
        $get_vars['origClientOrderId'] = $order_id;
    }

    $queries = array();
    foreach ($get_vars as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $query     = implode('&', $queries);
    $signature = hash_hmac('SHA256', $query, $db_settings['binance_api_secret_key']);

    $get_vars['signature'] = $signature;

    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/order
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/order';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true);

    $response['binance_response'] = $api_response;
    $response['time']             = (int)(microtime(true) * 1000);

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        if ($r['code'] != '-2013') {
            Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        }
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // order details - end
elseif ($_GET['action'] == 'cancelOrder') { // cancel order - begin
    $symbol   = isset($_POST['symbol']) ? $_POST['symbol'] : null;
    $order_id = isset($_POST['order_id']) ? $_POST['order_id'] : null;

    $get_vars                      = array();
    $get_vars['timestamp']         = (int)(microtime(true) * 1000);
    $get_vars['symbol']            = $symbol;
    $get_vars['origClientOrderId'] = $order_id;

    $queries = array();
    foreach ($get_vars as $k => $v) {
        $queries[] = $k . '=' . $v;
    }
    $query     = implode('&', $queries);
    $signature = hash_hmac('SHA256', $query, $db_settings['binance_api_secret_key']);

    $get_vars['signature'] = $signature;

    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/order
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/order';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true, 'DELETE');

    $response['binance_response'] = $api_response;
    $response['time']             = (int)(microtime(true) * 1000);

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // cancel order - end
elseif ($_GET['action'] == 'getExchangeInfo') { // exchange info - begin

    $get_vars  = array();
    $post_vars = array();
    $headers   = array("X-MBX-APIKEY: " . $db_settings['binance_api_key']);

    // https://api.binance.com/api/v3/exchangeInfo
    $binance_api_url = $db_settings['binance_api_base_url'] . 'api/v3/exchangeInfo';
    $api_response    = ApiClient::makeRequest($binance_api_url, $get_vars, $post_vars, $headers, false, true);

    $response['binance_response'] = $api_response;

    $r = json_decode($api_response, true);
    if ($r === null) {
        Functions::error_log($api_response . ' ' . json_encode($get_vars) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'request_error';
        $response['error_description'] = $_GET['action'];
        goto page_end;
    } elseif (empty($r['code']) === false) {
        Functions::error_log(json_encode($r) . ' ' . json_encode($post_vars));
        $has_error                     = true;
        $response['error']             = 'api_error';
        $response['error_description'] = empty($r['msg']) === false ? $r['msg'] : $_GET['action'];
        goto page_end;
    }
    $response['values']['info'] = $r;
} // exchange info - end
elseif ($_GET['action'] == 'activateBuyNow') { // activate buy now - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    if ($system_settings['is_running'] == false) {
        goto page_end;
    }

    // update database - begin
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_active_buy_now = 1,
is_active_sell_now = 0,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_active_buy_now']    = 1;
    $response['values']['info']['is_active_sell_now']   = 0;
    $response['values']['info']['is_active_cancel_now'] = 0;

} // activate buy now - end
elseif ($_GET['action'] == 'activateSellNow') { // activate sell now - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    if ($system_settings['is_running'] == false) {
        goto page_end;
    }

    // update database - begin
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_active_buy_now = 0,
is_active_sell_now = 1,
is_active_cancel_now = 0
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_active_buy_now']    = 0;
    $response['values']['info']['is_active_sell_now']   = 1;
    $response['values']['info']['is_active_cancel_now'] = 0;

} // activate sell now - end
elseif ($_GET['action'] == 'activateCancelNow') { // activate cancel now - begin
    $system_settings = array();

    // get system settings - begin
    $sql = $sql_system_settings;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $has_error         = true;
        $response['error'] = 'fetchSystemSettings';
        goto page_end;
    }
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $system_settings = $row;
    }
    $stmt->closeCursor();
    $system_settings = Functions::formatSystemSettings($system_settings);
    // get system settings - end

    $response['values']['info'] = $system_settings;

    if ($system_settings['is_running'] == false) {
        goto page_end;
    }

    // update database - begin
    $id = $system_settings['id'];

    $sql
        = <<<EOF
UPDATE system_settings
SET
is_active_buy_now = 0,
is_active_sell_now = 0,
is_active_cancel_now = 1
WHERE
id = :id;
EOF;

    $stmt = $db->prepare($sql);
    if ($stmt === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $db->errorInfo())));
        $has_error         = true;
        $response['error'] = 'updateSystemSettings';
        goto page_end;
    }

    // Bind values
    $stmt->bindValue(':id', $id, $id !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

    $r = $stmt->execute();
    if ($r === false) {
        Functions::error_log($sql . " " . base64_encode(implode(',', $stmt->errorInfo())));
        $page_vars['form_success']  = false;
        $page_vars['form_errors'][] = $db->errorCode();
        goto page_end;
    }
    // update database - end
    $response['values']['info']['is_active_buy_now']    = 0;
    $response['values']['info']['is_active_sell_now']   = 0;
    $response['values']['info']['is_active_cancel_now'] = 1;

} // activate cancel now - end

page_end:
