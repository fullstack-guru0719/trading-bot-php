var processing_timeout_id = null;
var target_buy_price = parseFloat(document.getElementById("p_target_base_buy_price").innerHTML);
var target_sell_price = parseFloat(document.getElementById("p_target_base_sell_price").innerHTML);
// console.log(target_buy_price);
// console.log(target_sell_price);

$(document).ready(function () {
    "use strict";

    $('#btn_start_processing').on('click', CryptoBotStartProcessing);
    $('#btn_pause_processing').on('click', CryptoBotPauseProcessing);
    $('#btn_stop_processing').on('click', CryptoBotStopProcessing);
    $('#btn_reset_interval').on('click', CryptoBotResetInterval);
    $('#btn_activate_buy_now').on('click', CryptoBotActivateBuyNow);
    $('#btn_activate_sell_now').on('click', CryptoBotActivateSellNow);
    $('#btn_activate_cancel_buy_order_now').on('click', CryptoBotActivateCancelBuyOrderNow);
    $('#btn_activate_cancel_sell_order_now').on('click', CryptoBotActivateCancelSellOrderNow);
    if (is_running === true) {
        processing_timeout_id = setTimeout(CryptoBotProcess, price_check_interval);
    }
});

function CryptoBotUpdateUI(msg) {
    var asset_price = msg.values.info.asset_price;
    if(asset_price > target_sell_price) {
        $('#demo').html("Sell Now!");
    }

    if(asset_price < target_buy_price) {
        $('#demo').html("Buy Now!");
    }
    
    // console.log(asset_price);
    var btn_activate_buy_now = $('#btn_activate_buy_now');
    var btn_activate_sell_now = $('#btn_activate_sell_now');
    var btn_activate_cancel_buy_order_now = $('#btn_activate_cancel_buy_order_now');
    var btn_activate_cancel_sell_order_now = $('#btn_activate_cancel_sell_order_now');

    try {
        if (msg.values.info.is_running === '1') {
            $('#span_is_running').html(is_running_text_yes);
            $('#p_status').html(msg.values.info.status_text);

            if (msg.values.info.status === 'waiting_for_opportunity' || msg.values.info.status === 'created_buy_order') {
                btn_activate_buy_now.prop('disabled', false);
                btn_activate_sell_now.prop('disabled', true);
            }
            else if (msg.values.info.status === 'waiting_to_sell' || msg.values.info.status === 'created_sell_order') {
                btn_activate_buy_now.prop('disabled', true);
                btn_activate_sell_now.prop('disabled', false);
            }

            btn_activate_cancel_buy_order_now.prop('disabled', true);
            btn_activate_cancel_sell_order_now.prop('disabled', true);
            if (msg.values.info.status === 'created_buy_order') {
                btn_activate_cancel_buy_order_now.prop('disabled', false);
            }
            else if (msg.values.info.status === 'created_sell_order') {
                btn_activate_cancel_sell_order_now.prop('disabled', false);
            }
        }
        else {
            $('#span_is_running').html(is_running_text_no);
            $('#p_status').html('');
            btn_activate_buy_now.prop('disabled', true);
            btn_activate_sell_now.prop('disabled', true);
            btn_activate_cancel_buy_order_now.prop('disabled', true);
            btn_activate_cancel_sell_order_now.prop('disabled', true);
        }
        $('#span_asset_price').removeClass();
        if (msg.values.info.price_diff > 0) {
            $('#span_asset_price').addClass('form-control-static');
            $('#span_asset_price').addClass('cryptobot-color-green');
        }
        else if (msg.values.info.price_diff < 0) {
            $('#span_asset_price').addClass('form-control-static');
            $('#span_asset_price').addClass('cryptobot-color-red');
        }
        else {
            $('#span_asset_price').addClass('form-control-static');
            $('#span_asset_price').addClass('cryptobot-color-black');
        }
        $('#span_asset_price').html(msg.values.info.asset_price);
        $('#span_current_trend').html(msg.values.info.current_trend_text);
        $('title').html(msg.values.info.asset_price);
        $('#p_interval_h').html(msg.values.info.interval_h);
        $('#p_interval_l').html(msg.values.info.interval_l);
        $('#p_interval_hl_ratio').html(msg.values.info.interval_hl_ratio);
        $('#p_target_sell_price').html(msg.values.info.target_sell_price);
        $('#p_number_of_trades').html(msg.values.info.number_of_trades);
        $('#p_current_buy_price_ratio').html(msg.values.info.current_buy_price_ratio);
        $('#base_asset_balance').val(msg.values.info.base_asset_balance);
        $('#quote_asset_balance').val(msg.values.info.quote_asset_balance);
        $('#last_sell_price').val(msg.values.info.last_sell_price);

        var interval_start_date = msg.values.info.interval_start_date_formatted;
        if (interval_start_date.length > 0) {
            $('#span_interval_start_date').addClass('cryptobot-margin-right-10');
        }
        else {
            $('#span_interval_start_date').removeClass('cryptobot-margin-right-10');
        }
        $('#span_interval_start_date').html(interval_start_date);

        var buy_order = msg.values.info.buy_price;
        var sell_order = msg.values.info.sell_price;
        if (buy_order !== null && buy_order.length > 0) {
            buy_order += ' (' + msg.values.info.buy_order_date_formatted + ')';
            $('#span_buy_order').addClass('cryptobot-margin-right-10');
        }
        else {
            $('#span_buy_order').removeClass('cryptobot-margin-right-10');
        }
        if (sell_order !== null && sell_order.length > 0) {
            sell_order += ' (' + msg.values.info.sell_order_date_formatted + ')';
            $('#span_sell_order').addClass('cryptobot-margin-right-10');
        }
        else {
            $('#span_sell_order').removeClass('cryptobot-margin-right-10');
        }
        $('#span_buy_order').html(buy_order);
        $('#span_sell_order').html(sell_order);
    }
    catch (err) {
    }
}

function CryptoBotStartProcessing() {
    var btn_start_processing = $('#btn_start_processing');
    btn_start_processing.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=startProcessing' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            location.reload();
        }
        btn_start_processing.prop('disabled', false);
    });
}

function CryptoBotPauseProcessing() {
    if (processing_timeout_id !== null) {
        clearTimeout(processing_timeout_id);
    }
    processing_timeout_id = null;

    var btn_pause_processing = $('#btn_pause_processing');
    btn_pause_processing.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=pauseProcessing' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            location.reload();
        }
        btn_pause_processing.prop('disabled', false);
    });
}

function CryptoBotStopProcessing() {
    if (processing_timeout_id !== null) {
        clearTimeout(processing_timeout_id);
    }
    processing_timeout_id = null;

    var btn_stop_processing = $('#btn_stop_processing');
    btn_stop_processing.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=stopProcessing' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            location.reload();
        }
        btn_stop_processing.prop('disabled', false);
    });
}

function CryptoBotActivateBuyNow() {
    var btn_activate_buy_now = $('#btn_activate_buy_now');
    btn_activate_buy_now.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=activateBuyNow' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            CryptoBotUpdateUI(msg);
        }
        btn_activate_buy_now.prop('disabled', false);
    });
}

function CryptoBotActivateSellNow() {
    var btn_activate_sell_now = $('#btn_activate_sell_now');
    btn_activate_sell_now.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=activateSellNow' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            CryptoBotUpdateUI(msg);
        }
        btn_activate_sell_now.prop('disabled', false);
    });
}

function CryptoBotActivateCancelBuyOrderNow() {
    var btn_activate_cancel_buy_order_now = $('#btn_activate_cancel_buy_order_now');
    btn_activate_cancel_buy_order_now.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=activateCancelNow' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            CryptoBotUpdateUI(msg);
        }
        btn_activate_cancel_buy_order_now.prop('disabled', false);
    });
}

function CryptoBotActivateCancelSellOrderNow() {
    var btn_activate_cancel_sell_order_now = $('#btn_activate_cancel_sell_order_now');
    btn_activate_cancel_sell_order_now.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=activateCancelNow' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            updateUI(msg);
        }
        btn_activate_cancel_sell_order_now.prop('disabled', false);
    });
}

function CryptoBotResetInterval() {
    var btn_reset_interval = $('#btn_reset_interval');
    btn_reset_interval.prop('disabled', true);

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=resetInterval' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            CryptoBotUpdateUI(msg);
        }
        btn_reset_interval.prop('disabled', false);
    });
}

function CryptoBotProcess() {
    if (processing_timeout_id !== null) {
        clearTimeout(processing_timeout_id);
    }

    var btn_activate_buy_now = $('#btn_activate_buy_now');
    var btn_activate_sell_now = $('#btn_activate_sell_now');
    var btn_activate_cancel_buy_order_now = $('#btn_activate_cancel_buy_order_now');
    var btn_activate_cancel_sell_order_now = $('#btn_activate_cancel_sell_order_now');

    jQuery.ajax({
        method: 'get',
        url: api_url + '?action=process' + '&l=' + current_language_code,
        data: {},
        dataType: 'json'
    }).done(function (msg) {
        if (msg.result === 'success') {
            if (msg.values['info']['status'] === 'finished') {
                if (processing_timeout_id !== null) {
                    clearTimeout(processing_timeout_id);
                }
                processing_timeout_id = null;
            }
            CryptoBotUpdateUI(msg);
            var asset_price = parseFloat(msg.values.info.asset_price);
            if(asset_price > target_sell_price) {
                console.log("sell now !");
                CryptoBotActivateSellNow();
                
            }
            if(asset_price < target_buy_price ) {
                console.log("buy now!");
                CryptoBotActivateBuyNow();
            }
            else{
                console.log("wait now!")
            }
            
        }
        else {
            btn_activate_buy_now.prop('disabled', true);
            btn_activate_sell_now.prop('disabled', true);
            btn_activate_cancel_buy_order_now.prop('disabled', true);
            btn_activate_cancel_sell_order_now.prop('disabled', true);
        }

        if (processing_timeout_id !== null) {
            processing_timeout_id = setTimeout(CryptoBotProcess, price_check_interval);
        }
    });
}