<nav class="navbar navbar-inverse cryptobot-navbar">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php?l=<?php echo CRYPTOBOT_LANGUAGE; ?>">
                CryptoBot For Binance
            </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="settings.php?l=<?php echo CRYPTOBOT_LANGUAGE; ?>"><i class="fa fa-cog"></i> <?php echo Translations::get('Settings', CRYPTOBOT_LANGUAGE); ?></a></li>
            </ul>
            <ul class="nav navbar-nav pull-right">
                <?php foreach (array('tr' => 'Türkçe', 'en' => 'English') as $language_code => $language): ?>
                    <?php
                    if (CRYPTOBOT_LANGUAGE == $language_code) {
                        continue;
                    }
                    ?>
                    <li><a class="nav-link" href="?l=<?php echo $language_code; ?>"><?php echo $language; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
