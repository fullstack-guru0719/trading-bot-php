<?php

class Translations
{
    private static $instance = null;

    private static $translated_strings
        = array(
            'tr' => array(
                'Settings'                                                => 'Ayarlar',
                'Is Running?'                                             => 'Çalışıyor Mu?',
                'Yes'                                                     => 'Evet',
                'No'                                                      => 'Hayır',
                'Start'                                                   => 'Başlat',
                'Pause'                                                   => 'Ara Ver',
                'Stop'                                                    => 'Durdur',
                'Status'                                                  => 'Durum',
                'Interval Start Date'                                     => 'Aralık Başlangıç Tarihi',
                'Reset Interval'                                          => 'Aralığı Sıfırla',
                'Base Asset'                                              => 'Seçili Coin',
                'Quote Asset'                                             => 'Ödeme Birimi',
                'Base Asset Trading Balance'                              => 'Alım-Satımdaki Seçili Coin Bakiyesi',
                'Quote Asset Trading Balance'                             => 'Alım-Satıma Ayrılmış Ödeme Birimi Bakiyesi',
                'Asset Lot Step Size'                                     => 'Min. Alım-Satım Miktarı',
                'Asset Price Tick Size'                                   => 'Min. Fiyat Hareketi',
                'Trading Fee Rate'                                        => 'Komisyon Oranı',
                'Number of Trades'                                        => 'Yapılan İşlem Sayısı',
                'Last Sell Price'                                         => 'Son Satış Fiyatı',
                'Current Asset Price'                                     => 'Güncel Coin Fiyatı',
                'Target Sell Price'                                       => 'Hedef Satış Fiyatı',
                'Interval High'                                           => 'En Yüksek Fiyat',
                'Interval Low'                                            => 'En Düşük Fiyat',
                'Interval High/Low Ratio'                                 => 'En Yüksek/En Düşük Fiyat Oranı',
                'Manual Actions'                                          => 'Manuel İşlemler',
                'Buy Now'                                                 => 'Hemen Al',
                'Sell Now'                                                => 'Hemen Sat',
                'Current Buy Order'                                       => 'Alış Emri',
                'Current Sell Order'                                      => 'Satış Emri',
                'Cancel'                                                  => 'İptal Et',
                '(Sell Price/Buy Price) Ratio'                            => 'İstenen (Satış Fiyatı / Alış Fiyatı) Oranı',
                '(Current Price/Buy Price) Ratio'                         => '(Güncel Fiyat / Alış Fiyatı) Oranı',
                'Current Trend'                                           => 'Güncel Trend',
                'Trend Calculation First Duration'                        => 'Trend Belirleme İçin Birinci Süre',
                'Trend Calculation Second Duration'                       => 'Trend Belirleme İçin İkinci Süre',
                'seconds'                                                 => 'saniye',
                'Buy Order Check Interval'                                => 'Alış Emri Kontrol Periyodu',
                'Sell Order Check Interval'                               => 'Satış Emri Kontrol Periyodu',
                'Auto-Buy'                                                => 'Otomatik Al',
                'Auto-Sell'                                               => 'Otomatik Sat',
                'Enabled'                                                 => 'Aktif',
                'Save Changes'                                            => 'Değişiklikleri Kaydet',
                'RULES'                                                   => 'KURALLAR',
                'Interval Reset Rule: Max. (Interval High/Low) Ratio'     => 'Aralık Sıfırlama Kuralı: Maksimum (En Yüksek / En Düşük) Fiyat Oranı',
                'Interval Reset Rule: Max. Interval Duration'             => 'Aralık Sıfırlama Kuralı: Maksimum Aralık Süresi',
                'Buying Rule: Min. (Interval High/Low) Ratio'             => 'Alış Kuralı: Minimum (En Yüksek / En Düşük) Fiyat Oranı',
                'Buying Rule: Min. (Current Price/Interval Low) Ratio'    => 'Alış Kuralı: Minimum (Güncel Fiyat / En Düşük Fiyat) Oranı',
                'Buying Rule: Max. (Current Price/Interval Low) Ratio'    => 'Alış Kuralı: Maksimum (Güncel Fiyat / En Düşük Fiyat) Oranı',
                'Buying Rule: Min. Interval Duration'                     => 'Alış Kuralı: Minimum Aralık Süresi',
                'Buying Rule: Buy Order Validity'                         => 'Alış Kuralı: Alış Emri Geçerlilik Süresi',
                'Selling Rule: Sell Order Validity'                       => 'Satış Kuralı: Satış Emri Geçerlilik Süresi',
                'Stop Rule: Max. Number of Trades'                        => 'Durma Kuralı: Maksimum İşlem Sayısı',
                'Escape Plan: Max. (Buy Price/Current Price) Ratio'       => 'Kaçış Planı: (Alış Fiyatı / Güncel Fiyat) Oranı',
                'Stop Rule: Stop On Escape'                               => 'Durma Kuralı: Kaçış Planı Gerçekleşirse',
                'Buying Rule: Current Trend is Downward'                  => 'Alış Kuralı: Güncel Trend Aşağı Yönlü',
                'Buying Rule: Current Trend is Upward'                    => 'Alış Kuralı: Güncel Trend Yukarı Yönlü',
                'Buying Rule: Last Set Boundary is Bottom'                => 'Alış Kuralı: Aralık Yüksek Değerlerinde En Son Alt Limit Belirlenmiş',
                'Buying Rule: Max. Current Price'                         => 'Alış Kuralı: Maksimum Güncel Fiyat',
                'Buying Rule: Min. (Last Sell Price/Current Price) Ratio' => 'Alış Kuralı: (Son Satış Fiyatı / Güncel Fiyat) Oranı',
                "System's Api Url"                                        => 'Sistem Api Linki',
                'Binance Api Base Url'                                    => 'Binance Api Linki',
                'Binance Api Key'                                         => 'Binance Api Key',
                'Binance Api Secret Key'                                  => 'Binance Api Secret Key',
                'Price Check Interval'                                    => 'Fiyat Güncelleme Periyodu',
                'milliseconds'                                            => 'milisaniye',
                'Date Format'                                             => 'Tarih Formatı',
                'Date Timezone'                                           => 'Tarih Zaman Dilimi',
                'Trading Rules'                                           => 'Alım-Satım Kuralları',
                'Automatically Fetch Trading Rules On Pair Change'        => 'İkili değişimlerinde Alım-Satım kurallarını otomatik çek',
                'Waiting for quote balance'                               => 'Ödeme birimi bakiyesi bekleniyor',
                'Waiting for opportunity'                                 => 'Fırsat bekleniyor',
                'Created limit buy order'                                 => 'Limit alış emri oluşturuldu',
                'Waiting to sell'                                         => 'Satış bekleniyor',
                'Created limit sell order'                                => 'Limit satış emri oluşturuldu',
                'Finished'                                                => 'Bitti',
                'Interval Reset Rule: After Successful Sale'              => 'Aralık Sıfırlama Kuralı: Başarılı Satış İşleminden Sonra',
                'Turkish'                                                 => 'Türkçe',
                'English'                                                 => 'İngilizce',
                'Upward'                                                  => 'Yukarı Yönlü',
                'Downward'                                                => 'Aşağı Yönlü',
            ),

        );

    private function __construct()
    {

    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Translations();
        }

        return self::$instance;
    }

    public static function get($string, $language_code)
    {
        if (empty(self::$translated_strings[$language_code][$string]) === false) {
            return self::$translated_strings[$language_code][$string];
        }

        return $string;
    }


}