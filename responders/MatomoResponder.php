<?php
namespace jt2k\Jarvis;

class MatomoResponder extends Responder
{
    public static $pattern = '^(?:matomo|piwik) (\w+)(?: (visits|page ?views))?$';

    public function respond()
    {
        if ($this->requireConfig(['matomo'])) {
            $config = $this->config['matomo'];
        } elseif ($this->requireConfig(['piwik'])) {
            $config = $this->config['piwik'];
        } else {
            return 'matomo configuration is missing';
        }

        if (!isset($config['url']) || !isset($config['token']) || !is_array($config['sites'])) {
            return 'matomo configuration is missing url, token, or sites';
        }

        $site = strtolower($this->matches[1]);
        if (!array_key_exists($site, $config['sites'])) {
            return "{$site} not configured";
        }

        $siteId = urlencode($config['sites'][$site]);

        $method = 'getVisits';
        $unit = 'visits';
        if (!empty($this->matches[2])) {
            switch (strtolower($this->matches[2])) {
                case 'pageviews':
                case 'page views':
                    $method = 'getActions';
                    $unit = 'page views';
                    break;
                case 'visits':
                    $method = 'getVisits';
                    $unit = 'visits';
                    break;
                default:
                    return "Metric not supported";
            }
        }
        $url = "{$config['url']}?module=API&method=VisitsSummary.{$method}&idSite={$siteId}&date=last8&period=day&format=json&token_auth={$config['token']}";

        $stats = (array)$this->request($url, 1800, 'piwik');
        if (isset($stats['result']) && $stats['result'] == 'error') {
            return 'API error. Make sure site is configured correctly and token user has access.';
        }
        $today = array_pop($stats);
        $week = array_sum($stats);
        $yesterday = end($stats);
        return sprintf(
            "%s %s\nToday (so far): %s\nYesterday: %s\nLast week: %s",
            $site,
            $unit,
            number_format($today),
            number_format($yesterday),
            number_format($week)
        );
    }
}
