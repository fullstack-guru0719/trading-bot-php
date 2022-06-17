<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title><?php echo $data['site_info']['title']; ?></title>

    <?php include 'includes/head.php'; ?>
</head>

<body class="cryptobot">
<?php include 'navbar.php'; ?>

<div class="container" style="width:100%; display: flex;">
    <?php if ($data['result_array']['page_success'] == false) : ?>
        <div class="alert alert-danger"
             role="alert"><?php echo $data['result_array']['page_errors'][0]; ?></div>
    <?php else: ?>

    <?php if ($data['page_vars']['is_form_submitted'] == true
        && $data['page_vars']['form_success'] == false
    ) : ?>
        <div class="alert alert-danger"
             role="alert"><?php echo $data['page_vars']['form_errors'][0]; ?></div>
    <?php endif; ?>

    <div class="well cryptobot-well" >

        <form action="" method="post" enctype="multipart/form-data" role="form" autocomplete="off">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo Translations::get('Is Running?', CRYPTOBOT_LANGUAGE); ?></label>
                        <div>
                                <span id="span_is_running"
                                      class="form-control-static cryptobot-margin-right-10"><?php echo htmlspecialchars($data['page_vars']['system_settings']['is_running']
                                    == true ? Translations::get('Yes', CRYPTOBOT_LANGUAGE) : Translations::get('No', CRYPTOBOT_LANGUAGE)); ?></span>
                            <button id="btn_start_processing" type="button"
                                    class="btn btn-primary btn-sm"><i class="fa fa-play"></i> <?php echo Translations::get('Start', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                            <button id="btn_pause_processing" type="button"
                                    class="btn btn-primary btn-sm"><i class="fa fa-pause"></i> <?php echo Translations::get('Pause', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                            <button id="btn_stop_processing" type="button"
                                    class="btn btn-danger btn-sm"><i class="fa fa-stop"></i> <?php echo Translations::get('Stop', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo Translations::get('Status', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_status"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['is_running']
                            == true ? Functions::statusToText($data['page_vars']['system_settings']['status']) : ''); ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo Translations::get('Interval Start Date', CRYPTOBOT_LANGUAGE); ?></label>
                        <div>
                                <span id="span_interval_start_date"
                                      class="form-control-static"></span>
                            <button id="btn_reset_interval" type="button"
                                    class="btn btn-primary"><?php echo Translations::get('Reset Interval', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="base_asset"><?php echo Translations::get('Base Asset', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="base_asset" id="base_asset"
                               value="<?php if (isset($data['page_vars']['post_values']['base_asset'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['base_asset']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['base_asset']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="quote_asset"><?php echo Translations::get('Quote Asset', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="quote_asset" id="quote_asset"
                               value="<?php if (isset($data['page_vars']['post_values']['quote_asset'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['quote_asset']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['quote_asset']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="base_asset_balance"><?php echo Translations::get('Base Asset Trading Balance', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="base_asset_balance" id="base_asset_balance"
                            <?php if ($data['page_vars']['system_settings']['is_running'] == true) {
                                echo ' disabled ';
                            } ?>
                               value="<?php if (isset($data['page_vars']['post_values']['base_asset_balance'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['base_asset_balance']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['base_asset_balance']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="quote_asset_balance"><?php echo Translations::get('Quote Asset Trading Balance', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="quote_asset_balance" id="quote_asset_balance"
                            <?php if ($data['page_vars']['system_settings']['is_running'] == true) {
                                echo ' disabled ';
                            } ?>
                               value="<?php if (isset($data['page_vars']['post_values']['quote_asset_balance'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['quote_asset_balance']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['quote_asset_balance']);
                               } ?>"/>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="asset_price_tick_size"><?php echo Translations::get('Asset Price Tick Size', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="asset_price_tick_size" id="asset_price_tick_size"
                               value="<?php if (isset($data['page_vars']['post_values']['asset_price_tick_size'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['asset_price_tick_size']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['asset_price_tick_size']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="asset_lot_step_size"><?php echo Translations::get('Asset Lot Step Size', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="asset_lot_step_size" id="asset_lot_step_size"
                               value="<?php if (isset($data['page_vars']['post_values']['asset_lot_step_size'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['asset_lot_step_size']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['asset_lot_step_size']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="trading_fee_rate"><?php echo Translations::get('Trading Fee Rate', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="trading_fee_rate" id="trading_fee_rate"
                                   value="<?php if (isset($data['page_vars']['post_values']['trading_fee_rate'])) {
                                       echo htmlspecialchars($data['page_vars']['post_values']['trading_fee_rate']);
                                   } else {
                                       echo htmlspecialchars($data['page_vars']['system_settings']['trading_fee_rate']);
                                   } ?>"/>
                            <span class="input-group-addon">%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_number_of_trades"><?php echo Translations::get('Number of Trades', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_number_of_trades"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['number_of_trades']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="last_sell_price"><?php echo Translations::get('Last Sell Price', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="last_sell_price" id="last_sell_price"
                               value="<?php if (isset($data['page_vars']['post_values']['last_sell_price'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['last_sell_price']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['last_sell_price']);
                               } ?>"/>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="asset_price"><?php echo Translations::get('Current Asset Price', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="span_asset_price" class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['asset_price']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_target_base_sell_price"><?php echo Translations::get('Target Sell Price', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_target_base_sell_price"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['target_base_sell_price']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_target_base_buy_price"><?php echo Translations::get('Target Buy Price', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_target_base_buy_price"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['target_base_buy_price']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_interval_h"><?php echo Translations::get('Interval High', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_interval_h"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['interval_h']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_interval_l"><?php echo Translations::get('Interval Low', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_interval_l"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['interval_l']); ?></p>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="p_interval_hl_ratio"><?php echo Translations::get('Interval High/Low Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_interval_hl_ratio"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['interval_hl_ratio']); ?></p>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo Translations::get('Manual Actions', CRYPTOBOT_LANGUAGE); ?></label>
                        <div>
                            <button id="btn_activate_buy_now" type="button"
                                    disabled
                                    class="btn btn-primary"><?php echo Translations::get('Buy Now', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                            <button id="btn_activate_sell_now" type="button"
                                    disabled
                                    class="btn btn-primary"><?php echo Translations::get('Sell Now', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                        </div>
                    </div>
                    <h3 id="demo">Wait!</h3>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="span_buy_order"><?php echo Translations::get('Current Buy Order', CRYPTOBOT_LANGUAGE); ?></label>
                        <div>
                            <span id="span_buy_order"
                                  class="form-control-static"></span>
                            <button id="btn_activate_cancel_buy_order_now" type="button"
                                    disabled
                                    class="btn btn-primary"><?php echo Translations::get('Cancel', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><?php echo Translations::get('Current Sell Order', CRYPTOBOT_LANGUAGE); ?></label>
                        <div>
                            <span id="span_sell_order"
                                  class="form-control-static"></span>
                            <button id="btn_activate_cancel_sell_order_now" type="button"
                                    disabled
                                    class="btn btn-primary"><?php echo Translations::get('Cancel', CRYPTOBOT_LANGUAGE); ?>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sell_buy_price_ratio"><?php echo Translations::get('(Sell Price/Buy Price) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <input class="form-control" type="text" name="sell_buy_price_ratio" id="sell_buy_price_ratio"
                               value="<?php if (isset($data['page_vars']['post_values']['sell_buy_price_ratio'])) {
                                   echo htmlspecialchars($data['page_vars']['post_values']['sell_buy_price_ratio']);
                               } else {
                                   echo htmlspecialchars($data['page_vars']['system_settings']['sell_buy_price_ratio']);
                               } ?>"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="p_current_buy_price_ratio"><?php echo Translations::get('(Current Price/Buy Price) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <p id="p_current_buy_price_ratio"
                           class="form-control-static"><?php echo htmlspecialchars($data['page_vars']['system_settings']['current_buy_price_ratio']); ?></p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label><?php echo Translations::get('Current Trend', CRYPTOBOT_LANGUAGE); ?></label>
                        <p class="form-control-static">
                            <span id="span_current_trend" class="form-control-static cryptobot-margin-right-10"><?php echo htmlspecialchars(Functions::trendToText($data['page_vars']['system_settings']['current_trend'])); ?></span>
                            <span class="form-control-static"><a href="https://www.binance.com/en/trade/<?php echo $data['page_vars']['system_settings']['base_asset'] . $data['page_vars']['system_settings']['quote_asset']; ?>?layout=pro&amp;type=spot" target="_blank">Binance <i class="fa fa-external-link"></i></a></span>
                        </p>
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="trend_calculation_first_duration"><?php echo Translations::get('Trend Calculation First Duration', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="trend_calculation_first_duration" id="trend_calculation_first_duration"
                                   value="<?php if (isset($data['page_vars']['post_values']['trend_calculation_first_duration'])) {
                                       echo htmlspecialchars($data['page_vars']['post_values']['trend_calculation_first_duration']);
                                   } else {
                                       echo htmlspecialchars($data['page_vars']['system_settings']['trend_calculation_first_duration']);
                                   } ?>"/>
                            <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="trend_calculation_second_duration"><?php echo Translations::get('Trend Calculation Second Duration', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="trend_calculation_second_duration" id="trend_calculation_second_duration"
                                   value="<?php if (isset($data['page_vars']['post_values']['trend_calculation_second_duration'])) {
                                       echo htmlspecialchars($data['page_vars']['post_values']['trend_calculation_second_duration']);
                                   } else {
                                       echo htmlspecialchars($data['page_vars']['system_settings']['trend_calculation_second_duration']);
                                   } ?>"/>
                            <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="buy_order_check_interval"><?php echo Translations::get('Buy Order Check Interval', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="buy_order_check_interval" id="buy_order_check_interval"
                                   value="<?php if (isset($data['page_vars']['post_values']['buy_order_check_interval'])) {
                                       echo htmlspecialchars($data['page_vars']['post_values']['buy_order_check_interval']);
                                   } else {
                                       echo htmlspecialchars($data['page_vars']['system_settings']['buy_order_check_interval']);
                                   } ?>"/>
                            <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="sell_order_check_interval"><?php echo Translations::get('Sell Order Check Interval', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="input-group">
                            <input class="form-control" type="text" name="sell_order_check_interval" id="sell_order_check_interval"
                                   value="<?php if (isset($data['page_vars']['post_values']['sell_order_check_interval'])) {
                                       echo htmlspecialchars($data['page_vars']['post_values']['sell_order_check_interval']);
                                   } else {
                                       echo htmlspecialchars($data['page_vars']['system_settings']['sell_order_check_interval']);
                                   } ?>"/>
                            <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="is_enabled_auto_buy"><?php echo Translations::get('Auto-Buy', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="checkbox">
                            <label>
                                <input name="is_enabled_auto_buy"
                                       type="checkbox"
                                    <?php if (isset($data['page_vars']['post_values']['is_enabled_auto_buy']) || ($data['page_vars']['system_settings']['is_enabled_auto_buy'] == true)) {
                                        echo 'checked';
                                    } ?>
                                /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="is_enabled_auto_buy"><?php echo Translations::get('Auto-Sell', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="checkbox">
                            <label>
                                <input name="is_enabled_auto_sell"
                                       type="checkbox"
                                    <?php if (isset($data['page_vars']['post_values']['is_enabled_auto_sell']) || ($data['page_vars']['system_settings']['is_enabled_auto_sell'] == true)) {
                                        echo 'checked';
                                    } ?>
                                /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-12">
                    <button type="submit" name="submit_btn" class="btn btn-primary"><?php echo Translations::get('Save Changes', CRYPTOBOT_LANGUAGE); ?></button>
                </div>
            </div>

            <hr>

            <h2><?php echo Translations::get('RULES', CRYPTOBOT_LANGUAGE); ?></h2>

            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_hl_ratio"><?php echo Translations::get('Interval Reset Rule: Max. (Interval High/Low) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_2"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_2']) || ($data['page_vars']['system_settings']['is_enabled_rule_2'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="max_hl_ratio" id="max_hl_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['max_hl_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['max_hl_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['max_hl_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_interval_duration"><?php echo Translations::get('Interval Reset Rule: Max. Interval Duration', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_6"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_6']) || ($data['page_vars']['system_settings']['is_enabled_rule_6'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input class="form-control" type="text" name="max_interval_duration" id="max_interval_duration"
                                           value="<?php if (isset($data['page_vars']['post_values']['max_interval_duration'])) {
                                               echo htmlspecialchars($data['page_vars']['post_values']['max_interval_duration']);
                                           } else {
                                               echo htmlspecialchars($data['page_vars']['system_settings']['max_interval_duration']);
                                           } ?>"/>
                                    <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_hl_ratio"><?php echo Translations::get('Buying Rule: Min. (Interval High/Low) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_1"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_1']) || ($data['page_vars']['system_settings']['is_enabled_rule_1'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="min_hl_ratio" id="min_hl_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['min_hl_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['min_hl_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['min_hl_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_current_interval_low_ratio"><?php echo Translations::get('Buying Rule: Min. (Current Price/Interval Low) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_3"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_3']) || ($data['page_vars']['system_settings']['is_enabled_rule_3'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="min_current_interval_low_ratio" id="min_current_interval_low_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['min_current_interval_low_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['min_current_interval_low_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['min_current_interval_low_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_current_interval_low_ratio"><?php echo Translations::get('Buying Rule: Max. (Current Price/Interval Low) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_4"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_4']) || ($data['page_vars']['system_settings']['is_enabled_rule_4'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="max_current_interval_low_ratio" id="max_current_interval_low_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['max_current_interval_low_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['max_current_interval_low_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['max_current_interval_low_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_interval_duration"><?php echo Translations::get('Buying Rule: Min. Interval Duration', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_5"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_5']) || ($data['page_vars']['system_settings']['is_enabled_rule_5'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input class="form-control" type="text" name="min_interval_duration" id="min_interval_duration"
                                           value="<?php if (isset($data['page_vars']['post_values']['min_interval_duration'])) {
                                               echo htmlspecialchars($data['page_vars']['post_values']['min_interval_duration']);
                                           } else {
                                               echo htmlspecialchars($data['page_vars']['system_settings']['min_interval_duration']);
                                           } ?>"/>
                                    <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="buy_order_validity"><?php echo Translations::get('Buying Rule: Buy Order Validity', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_7"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_7']) || ($data['page_vars']['system_settings']['is_enabled_rule_7'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input class="form-control" type="text" name="buy_order_validity" id="buy_order_validity"
                                           value="<?php if (isset($data['page_vars']['post_values']['buy_order_validity'])) {
                                               echo htmlspecialchars($data['page_vars']['post_values']['buy_order_validity']);
                                           } else {
                                               echo htmlspecialchars($data['page_vars']['system_settings']['buy_order_validity']);
                                           } ?>"/>
                                    <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="sell_order_validity"><?php echo Translations::get('Selling Rule: Sell Order Validity', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_8"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_8']) || ($data['page_vars']['system_settings']['is_enabled_rule_8'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input class="form-control" type="text" name="sell_order_validity" id="sell_order_validity"
                                           value="<?php if (isset($data['page_vars']['post_values']['sell_order_validity'])) {
                                               echo htmlspecialchars($data['page_vars']['post_values']['sell_order_validity']);
                                           } else {
                                               echo htmlspecialchars($data['page_vars']['system_settings']['sell_order_validity']);
                                           } ?>"/>
                                    <span class="input-group-addon"><?php echo Translations::get('seconds', CRYPTOBOT_LANGUAGE); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_number_of_trades"><?php echo Translations::get('Stop Rule: Max. Number of Trades', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_9"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_9']) || ($data['page_vars']['system_settings']['is_enabled_rule_9'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="max_number_of_trades" id="max_number_of_trades"
                                       value="<?php if (isset($data['page_vars']['post_values']['max_number_of_trades'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['max_number_of_trades']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['max_number_of_trades']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_buy_current_ratio"><?php echo Translations::get('Escape Plan: Max. (Buy Price/Current Price) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_10"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_10']) || ($data['page_vars']['system_settings']['is_enabled_rule_10'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="max_buy_current_ratio" id="max_buy_current_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['max_buy_current_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['max_buy_current_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['max_buy_current_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo Translations::get('Stop Rule: Stop On Escape', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_14"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_14']) || ($data['page_vars']['system_settings']['is_enabled_rule_14'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo Translations::get('Buying Rule: Current Trend is Downward', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_11"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_11']) || ($data['page_vars']['system_settings']['is_enabled_rule_11'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo Translations::get('Buying Rule: Current Trend is Upward', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_12"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_12']) || ($data['page_vars']['system_settings']['is_enabled_rule_12'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo Translations::get('Buying Rule: Last Set Boundary is Bottom', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_13"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_13']) || ($data['page_vars']['system_settings']['is_enabled_rule_13'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="min_last_sell_current_ratio"><?php echo Translations::get('Buying Rule: Min. (Last Sell Price/Current Price) Ratio', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_16"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_16']) || ($data['page_vars']['system_settings']['is_enabled_rule_16'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="min_last_sell_current_ratio" id="min_last_sell_current_ratio"
                                       value="<?php if (isset($data['page_vars']['post_values']['min_last_sell_current_ratio'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['min_last_sell_current_ratio']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['min_last_sell_current_ratio']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="max_buying_price"><?php echo Translations::get('Buying Rule: Max. Current Price', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_17"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_17']) || ($data['page_vars']['system_settings']['is_enabled_rule_17'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="max_buying_price" id="max_buying_price"
                                       value="<?php if (isset($data['page_vars']['post_values']['max_buying_price'])) {
                                           echo htmlspecialchars($data['page_vars']['post_values']['max_buying_price']);
                                       } else {
                                           echo htmlspecialchars($data['page_vars']['system_settings']['max_buying_price']);
                                       } ?>"/>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label><?php echo Translations::get('Interval Reset Rule: After Successful Sale', CRYPTOBOT_LANGUAGE); ?></label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox">
                                    <label>
                                        <input name="is_enabled_rule_15"
                                               type="checkbox"
                                            <?php if (isset($data['page_vars']['post_values']['is_enabled_rule_15']) || ($data['page_vars']['system_settings']['is_enabled_rule_15'] == true)) {
                                                echo 'checked';
                                            } ?>
                                        /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-12">
                    <button type="submit" name="submit_btn" class="btn btn-primary"><?php echo Translations::get('Save Changes', CRYPTOBOT_LANGUAGE); ?></button>
                </div>
            </div>
        </form>
    </div>
    <div class="well cryptobot-well" style="position:relative;">

        <!-- <form action="" method="post" enctype="multipart/form-data" role="form" autocomplete="off"> -->
            <table class="table data" id="table">
                <tr>
                   <th>Base Asset</th>
                   <th>Quote Asset</th>                               
                   <th>Sell Price</th>                               
                   <th>Buy Price</th>                               
                   <th>Action</th>                               
                </tr>
              
                <?php 
                    // if($data['page_vars']['price_settings']->num_rows > 0) {
                        foreach($data['page_vars']['price_settings'] as $item) {
                ?>
               
                <tr id="<?= $item['id'] ?>" name="<?= $item['id'] ?>">
                    <td class="data" name="p_base_asset" id="p_base_asset<?=$item['id'] ?>"><?php echo $item['base_asset']; ?></td>
                    <td class="data" name="p_quote_asset" id="p_quote_asset<?=$item['id'] ?>""><?php echo $item['quote_asset']; ?></td>
                    <td class="data" name="p_sell_price" id="p_sell_price<?=$item['id'] ?>"><?php echo $item['sell_price']; ?></td>
                    <td class="data" name="p_buy_price" id="p_buy_price<?=$item['id'] ?>"><?php echo $item['buy_price']; ?></td>
                    <td style="display: flex;">
                        <button type="button" class="start btn-success" >Start</button>
                        <button type="button" class="edit btn-primary" >Edit</button>
                        <button type="button" class="save btn-warning" >Save</button>
                    </td>
                </tr>
            
                <?php } ?>
                <p id="demo"></p>
            </table>
            
                <button type="submit" name="submit_btn" class="btn btn-primary"><?php echo Translations::get('Save Changes', CRYPTOBOT_LANGUAGE); ?></button>
                <button id="Add" class="btn btn-success" ><?php echo Translations::get('Add', CRYPTOBOT_LANGUAGE); ?></button>
        <!-- </form> -->
    </div>                             

</div>

<?php if ($data['page_vars']['is_form_submitted'] == true
    && $data['page_vars']['form_success'] == false
) : ?>
    <div class="alert alert-danger"
         role="alert"><?php echo $data['page_vars']['form_errors'][0]; ?></div>
<?php endif; ?>

<?php endif; ?>
<?php include 'page_footer.php'; ?>

<?php
include 'footer.php';
include 'includes/footer.php';
?>

<script>
    var api_url = '<?php echo $data['page_vars']['db_settings']['system_api_url']; ?>';
    var price_check_interval = <?php echo $data['page_vars']['db_settings']['price_check_interval']; ?>;
    var current_language_code = '<?php echo CRYPTOBOT_LANGUAGE; ?>';

    <?php if (empty($data['page_vars']['system_settings']) === false && $data['page_vars']['system_settings']['is_running'] == true): ?>
    var is_running = true;
    <?php else: ?>
    var is_running = false;
    <?php endif; ?>
    var is_running_text_yes = '<?php echo Translations::get('Yes', CRYPTOBOT_LANGUAGE); ?>';
    var is_running_text_no = '<?php echo Translations::get('No', CRYPTOBOT_LANGUAGE); ?>';


    $('.edit').click(function() {
        $(this).parent().siblings('td.data').each(function() {  
            var content = $(this).html();  
            $(this).html('<input value="' + content + '" />');  
        });
        // alert("asdf");
        $(this).siblings('#save').show();  
        $(this).siblings('#start').hide();  
        $(this).hide();  
    });  
    $('.save').click(function() {  
        $('input').each(function() {  
            var content = $(this).val();
        
            $(this).html(content);  
            $(this).contents().unwrap();  
        });  
        var id  = $(this).parent().parent().attr('id');
        // console.log('p_sell_price' + id);
        var sell_price = parseFloat($('#p_sell_price'+id).text());
        var buy_price = parseFloat($('#p_buy_price' + id).text());

        $.post("updatePrice.php",
                {
                    id: id,
                    base_asset: "NEO",
                    quote_asset: "USDT",
                    sell_price: sell_price,
                    buy_price: buy_price
                },
                function(data,status){
                    alert("Data: " + data + "\nStatus: " + status);
            });

            
        
        $(this).siblings('#Edit').show();  
        $(this).siblings('#Start').show();  
        $(this).hide();  
    });

    $('.start').click(function() {
        var id  = $(this).parent().parent().attr('id');
        // console.log('p_sell_price' + id);
        var sell_price = parseFloat($('#p_sell_price'+id).text());
        var buy_price = parseFloat($('#p_buy_price' + id).text());

        $.post("update_price_in_system.php",
                {
                    id: id,
                    sell_price: sell_price,
                    buy_price: buy_price
                },
                function(data,status){
                    alert("Data: " + data + "\nStatus: " + status);
            });


    })
   
    $('#Add').click(function() {
        
        $('#table tr:last').after('<tr><td class="data"><input name="n_base_asset"/></td><td class="data"><input name="n_quote_asset"/></td><td class="data"><input name="n_sell_price"/></td><td class="data"><input name="n_buy_price"/></td><td style="display:flex;"><button class="start">start</button><button class="create">Create</button></td></tr>');  
        
        $('.create').click(function() {
            var base_asset = document.getElementsByName('n_base_asset')[0].value;
            var quote_asset = document.getElementsByName('n_quote_asset')[0].value;
            var sell_price = document.getElementsByName('n_sell_price')[0].value;
            var buy_price = document.getElementsByName('n_buy_price')[0].value;
            $.post("addPrice.php",
                {
                    id: 12,
                    base_asset: "NEO",
                    quote_asset: "USDT",
                    sell_price: sell_price,
                    buy_price: buy_price
                },
                function(data,status){
                    alert("Data: " + data + "\nStatus: " + status);
                });
        })
     
    });
</script>

<script src="ui/js/index.js"></script>

</body>
</html>