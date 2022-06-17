<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo $data['site_info']['title']; ?></title>

    <?php include 'includes/head.php'; ?>
</head>

<body class="cryptobot">
<?php include 'navbar.php'; ?>

<div class="container">

    <?php if ($data['result_array']['page_success'] == false) : ?>
        <div class="alert alert-danger"
             role="alert"><?php echo $data['result_array']['page_errors'][0]; ?></div>
    <?php else: ?>
        <h1 class="cryptobot cryptobot-margin-bottom-20"><?php echo Translations::get('Settings', CRYPTOBOT_LANGUAGE); ?></h1>

        <?php if ($data['page_vars']['is_form_submitted'] == true
            && $data['page_vars']['form_success'] == false
        ) : ?>
            <div class="alert alert-danger"
                 role="alert"><?php echo $data['page_vars']['form_errors'][0]; ?></div>
        <?php endif; ?>

        <div class="well cryptobot-well">
            <form action="" method="post" enctype="multipart/form-data" role="form" autocomplete="off">
                <div class="row">
                    <div class="row container">
                        <div class="form-group col-sm-6 clearfix">
                            <label for="system_api_url"><?php echo Translations::get("System's Api Url", CRYPTOBOT_LANGUAGE); ?></label>
                            <input class="form-control" type="text" name="system_api_url" id="system_api_url"
                                value="<?php if (isset($data['page_vars']['post_values']['system_api_url'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['system_api_url']);
                                } else {
                                    echo htmlspecialchars($data['page_vars']['system_settings']['system_api_url']);
                                } ?>"/>
                        </div>                 
                        <div class="form-group col-sm-6 clearfix">
                            <label for="binance_api_base_url"><?php echo Translations::get('Binance Api Base Url', CRYPTOBOT_LANGUAGE); ?></label>
                            <input class="form-control" type="text" name="binance_api_base_url" id="binance_api_base_url"
                                value="<?php if (isset($data['page_vars']['post_values']['binance_api_base_url'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['binance_api_base_url']);
                                } else {
                                    echo htmlspecialchars($data['page_vars']['system_settings']['binance_api_base_url']);
                                } ?>"/>
                        </div>
                    </div>
                    <div class="row container">
                        <div class="form-group col-sm-6">
                            <label for="binance_api_key"><?php echo Translations::get('Binance Api Key', CRYPTOBOT_LANGUAGE); ?> <a href="https://www.binance.com/en/my/settings/api-management" target="_blank">Binance <i class="fa fa-external-link"></i></a></label>
                            <input class="form-control" type="text" name="binance_api_key" id="binance_api_key"
                                value="<?php if (isset($data['page_vars']['post_values']['binance_api_key'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['binance_api_key']);
                                } else {
                                    echo ! empty($data['page_vars']['system_settings']['binance_api_key']) ? '*****' : htmlspecialchars($data['page_vars']['system_settings']['binance_api_key']);
                                } ?>"/>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="binance_api_secret_key"><?php echo Translations::get('Binance Api Secret Key', CRYPTOBOT_LANGUAGE); ?> <a href="https://www.binance.com/en/my/settings/api-management" target="_blank">Binance <i class="fa fa-external-link"></i></a></label>
                            <input class="form-control" type="text" name="binance_api_secret_key" id="binance_api_secret_key"
                                value="<?php if (isset($data['page_vars']['post_values']['binance_api_secret_key'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['binance_api_secret_key']);
                                } else {
                                    echo ! empty($data['page_vars']['system_settings']['binance_api_secret_key']) ? '*****' : htmlspecialchars($data['page_vars']['system_settings']['binance_api_secret_key']);
                                } ?>"/>
                        </div>
                    </div>
                    <div class="row container">
                        <div class="form-group col-sm-3">
                            <label for="target_base_buy_price"><?php echo Translations::get('Target Buy Price', CRYPTOBOT_LANGUAGE); ?></label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="target_base_buy_price" id="target_base_buy_price"
                                    value="<?php if (isset($data['page_vars']['post_values']['target_base_buy_price'])) {
                                        echo htmlspecialchars($data['page_vars']['post_values']['target_base_buy_price']);
                                    } else {
                                        echo htmlspecialchars($data['page_vars']['system_settings']['target_base_buy_price']);
                                    } ?>"/>
                            </div>
                        </div>

                        <div class="form-group col-sm-3">
                            <label for="target_base_sell_price"><?php echo Translations::get('Target Sell Price', CRYPTOBOT_LANGUAGE); ?></label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="target_base_sell_price" id="target_base_sell_price"
                                    value="<?php if (isset($data['page_vars']['post_values']['target_base_sell_price'])) {
                                        echo htmlspecialchars($data['page_vars']['post_values']['target_base_sell_price']);
                                    } else {
                                        echo htmlspecialchars($data['page_vars']['system_settings']['target_base_sell_price']);
                                    } ?>"/>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="price_check_interval"><?php echo Translations::get('Price Check Interval', CRYPTOBOT_LANGUAGE); ?></label>
                            <div class="input-group">
                                <input class="form-control" type="text" name="price_check_interval" id="price_check_interval"
                                    value="<?php if (isset($data['page_vars']['post_values']['price_check_interval'])) {
                                        echo htmlspecialchars($data['page_vars']['post_values']['price_check_interval']);
                                    } else {
                                        echo htmlspecialchars($data['page_vars']['system_settings']['price_check_interval']);
                                    } ?>"/>
                                <span class="input-group-addon"><?php echo Translations::get('milliseconds', CRYPTOBOT_LANGUAGE); ?></span>
                            </div>
                        </div>
                    </div>
                   
                    <div class="row container">
                        <div class="form-group col-sm-6">
                            <label for="date_timezone"><?php echo Translations::get('Date Timezone', CRYPTOBOT_LANGUAGE); ?> <a href="https://www.php.net/manual/en/timezones.php" target="_blank">php.net <i class="fa fa-external-link"></i></a></label>
                            <input class="form-control" type="text" name="date_timezone" id="date_timezone"
                                value="<?php if (isset($data['page_vars']['post_values']['date_timezone'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['date_timezone']);
                                } else {
                                    echo htmlspecialchars($data['page_vars']['system_settings']['date_timezone']);
                                } ?>"/>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="date_format"><?php echo Translations::get('Date Format', CRYPTOBOT_LANGUAGE); ?> <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">php.net <i class="fa fa-external-link"></i></a></label>
                            <input class="form-control" type="text" name="date_format" id="date_format"
                                value="<?php if (isset($data['page_vars']['post_values']['date_format'])) {
                                    echo htmlspecialchars($data['page_vars']['post_values']['date_format']);
                                } else {
                                    echo htmlspecialchars($data['page_vars']['system_settings']['date_format']);
                                } ?>"/>
                        </div>
                    </div>
                    
                    <div class="form-group col-md-6">
                        <label for="is_enabled_auto_fetch_trading_rules"><?php echo Translations::get('Automatically Fetch Trading Rules On Pair Change', CRYPTOBOT_LANGUAGE); ?> <a href="https://www.binance.com/en/trade-rule" target="_blank"><?php echo Translations::get('Trading Rules', CRYPTOBOT_LANGUAGE); ?> <i class="fa fa-external-link"></i></a></label>
                        <div class="checkbox">
                            <label>
                                <input name="is_enabled_auto_fetch_trading_rules"
                                       type="checkbox"
                                    <?php if (isset($data['page_vars']['post_values']['is_enabled_auto_fetch_trading_rules']) || ($data['page_vars']['system_settings']['is_enabled_auto_fetch_trading_rules'] == true)) {
                                        echo 'checked';
                                    } ?>
                                /><?php echo Translations::get('Enabled', CRYPTOBOT_LANGUAGE); ?>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="submit_btn" class="btn btn-primary"><?php echo Translations::get('Save Changes', CRYPTOBOT_LANGUAGE); ?></button>
            </form>
        </div>

        <?php if ($data['page_vars']['is_form_submitted'] == true
            && $data['page_vars']['form_success'] == false
        ) : ?>
            <div class="alert alert-danger"
                 role="alert"><?php echo $data['page_vars']['form_errors'][0]; ?></div>
        <?php endif; ?>

    <?php endif; ?>
    <?php include 'page_footer.php'; ?>
</div>

<?php
include 'footer.php';
include 'includes/footer.php';
?>

</body>
</html>