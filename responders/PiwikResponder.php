<?php
namespace jt2k\Jarvis;

class PiwikResponder extends Responder
{
    public static $pattern = '^piwik (\w+)$';

    public function respond()
    {
        if (!$this->requireConfig(array('piwik'))) {
            return 'piwik not configured';
        }

        $config = $this->config['piwik'];
        if (!isset($config['url']) || !isset($config['token']) || !is_array($config['sites'])) {
            return 'piwik not configured';
        }

        $site = strtolower($this->matches[1]);
        if (!array_key_exists($site, $config['sites'])) {
            return "{$site} not configured";
        }

        $siteId = urlencode($config['sites'][$site]);

        $url = "{$config['url']}?module=API&method=VisitsSummary.getVisits&idSite={$siteId}&date=last8&period=day&format=json&token_auth={$config['token']}";

        $stats = (array)$this->request($url);
        if (isset($stats['result']) && $stats['result'] == 'error') {
            return 'API error. Make sure site is configured correctly and token user has access.';
        }
        $today = array_pop($stats);
        $week = array_sum($stats);
        $yesterday = end($stats);
        return sprintf(
            "%s visits\nToday (so far): %s\nYesterday: %s\nLast week: %s",
            $site,
            number_format($today),
            number_format($yesterday),
            number_format($week)
        );
    }
}
