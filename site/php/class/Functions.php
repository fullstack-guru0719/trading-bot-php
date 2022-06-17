<?php

class Functions
{
    private static $instance = null;

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Functions();
        }

        return self::$instance;
    }

    public static function callback_array_walk_trim(&$item_value, $item_key)
    {
        if (is_array($item_value)) {
            array_walk($item_value, 'Functions::callback_array_walk_trim');
        } else {
            $item_value = trim($item_value);
        }
    }

    public static function error_log($err_text)
    {
        error_log($err_text);
    }

    public static function canBuy($system_settings)
    {
        $c                              = $system_settings['asset_price'];
        $l                              = $system_settings['interval_l'];
        $h                              = $system_settings['interval_h'];
        $min_hl_ratio                   = $system_settings['min_hl_ratio'];
        $max_hl_ratio                   = $system_settings['max_hl_ratio'];
        $min_current_interval_low_ratio = $system_settings['min_current_interval_low_ratio'];
        $max_current_interval_low_ratio = $system_settings['max_current_interval_low_ratio'];
        $interval_start_date            = $system_settings['interval_start_date'];
        $min_interval_duration          = $system_settings['min_interval_duration'];
        $current_trend                  = $system_settings['current_trend'];
        $last_set_boundary              = $system_settings['last_set_boundary'];
        $last_sell_price                = $system_settings['last_sell_price'];
        $min_last_sell_current_ratio    = $system_settings['min_last_sell_current_ratio'];
        $max_buying_price               = $system_settings['max_buying_price'];

        // check if auto-buy is enabled - begin
        if ($system_settings['is_enabled_auto_buy'] == false) {
            return false;
        }
        // check if auto-buy is enabled - end

        // check interval duration - begin
        if ($system_settings['is_enabled_rule_5'] == true && time() < $interval_start_date + $min_interval_duration) {
            return false;
        }
        // check interval duration - end

        // interval difference check - begin
        if ($system_settings['is_enabled_rule_1'] == true && (empty($h) || empty($l) || empty($min_hl_ratio) || $h / $l < $min_hl_ratio)) {
            return false;
        } elseif ($system_settings['is_enabled_rule_2'] == true && (empty($h) || empty($l) || empty($min_hl_ratio) || $h / $l > $max_hl_ratio)) {
            return false;
        } // interval difference check - end

        // current price / interval low ratio check - begin
        if ($system_settings['is_enabled_rule_3'] == true && (empty($c) || empty($l) || empty($min_current_interval_low_ratio) || $c / $l < $min_current_interval_low_ratio)) {
            return false;
        } elseif ($system_settings['is_enabled_rule_4'] == true && (empty($c) || empty($l) || empty($max_current_interval_low_ratio) || $c / $l > $max_current_interval_low_ratio)) {
            return false;
        } // current price / interval low ratio check - end

        // current trend check - begin
        if ($system_settings['is_enabled_rule_11'] == true && $current_trend != 'downward') {
            return false;
        } elseif ($system_settings['is_enabled_rule_12'] == true && $current_trend != 'upward') {
            return false;
        } // current trend check - end

        // last set boundary check - begin
        if ($system_settings['is_enabled_rule_13'] == true && $last_set_boundary != 'low') {
            return false;
        } // last set boundary check - end

        // last sell price / current price ratio check - begin
        if ($system_settings['is_enabled_rule_16'] == true && empty($last_sell_price) === false && (empty($c) || $last_sell_price / $c < $min_last_sell_current_ratio)) {
            return false;
        } // last sell price / current price ratio check - end

        // max buying price check - begin
        if ($system_settings['is_enabled_rule_17'] == true && empty($max_buying_price) === false && (empty($c) || $c > $max_buying_price)) {
            return false;
        } // max buying price check - end

        return true;
    }

    public static function statusToText($status)
    {
        $text = $status;

        switch ($status) {
            case 'waiting_for_quote_asset_balance':
                $text = Translations::get('Waiting for quote balance', CRYPTOBOT_LANGUAGE);
                break;
            case 'waiting_for_opportunity':
                $text = Translations::get('Waiting for opportunity', CRYPTOBOT_LANGUAGE);
                break;
            case 'created_buy_order':
                $text = Translations::get('Created limit buy order', CRYPTOBOT_LANGUAGE);
                break;
            case 'waiting_to_sell':
                $text = Translations::get('Waiting to sell', CRYPTOBOT_LANGUAGE);
                break;
            case 'created_sell_order':
                $text = Translations::get('Created limit sell order', CRYPTOBOT_LANGUAGE);
                break;
            case 'finished':
                $text = Translations::get('Finished', CRYPTOBOT_LANGUAGE);
                break;
        }

        return $text;
    }

    public static function targetSellPrice($system_settings)
    {
        $target_price = null;

        $status               = $system_settings['status'];
        $buy_price            = $system_settings['buy_price'];
        $sell_buy_price_ratio = $system_settings['sell_buy_price_ratio'];

        if (empty($system_settings['buy_price']) || empty($system_settings['sell_buy_price_ratio'])) {
            return $target_price;
        }

        if ($status == 'waiting_to_sell' || $status == 'created_sell_order') {
            $asset_price_tick_size = floatval($system_settings['asset_price_tick_size']);

            $target_price = $buy_price * $sell_buy_price_ratio;
            if ($system_settings['asset_price_tick_size'] > 0) {
                $target_price = $target_price - fmod($target_price, $asset_price_tick_size);
            }
        }

        return $target_price;
    }

    public static function trendToText($trend)
    {
        $text = $trend;

        switch ($trend) {
            case 'upward':
                $text = Translations::get('Upward', CRYPTOBOT_LANGUAGE);
                break;
            case 'downward':
                $text = Translations::get('Downward', CRYPTOBOT_LANGUAGE);
                break;
        }

        return $text;
    }

    public static function formatSystemSettings($system_settings)
    {
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
        if (empty($system_settings['interval_h']) === false && empty($system_settings['interval_l']) === false) {
            $system_settings['interval_hl_ratio'] = $system_settings['interval_h'] / $system_settings['interval_l'];
        }

        $system_settings['current_buy_price_ratio'] = '';
        if (empty($system_settings['asset_price']) === false && empty($system_settings['buy_price']) === false) {
            $system_settings['current_buy_price_ratio'] = $system_settings['asset_price'] / $system_settings['buy_price'];
        }

        return $system_settings;
    }
}