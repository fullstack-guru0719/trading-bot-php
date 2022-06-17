<?php
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
    echo $sql;
?>