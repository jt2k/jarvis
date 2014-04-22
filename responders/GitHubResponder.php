<?php
namespace jt2k\Jarvis;

class GitHubResponder extends Responder
{
    public static $pattern = '^(?:github|gh)(.*)$';

    public function renderEvent($event)
    {
        $actor = $event->actor->login;
        $repo = $event->repo->name;

        switch ($event->type) {
            case 'PushEvent':
                $count = count($event->payload->commits);
                if ($count === 1) {
                    $commits = "1 commit";
                } else {
                    $commits = "{$count} commits";
                }
                if ($count > 1) {
                    return "{$actor} pushed {$commits} to {$repo}";
                } else {
                    return "{$actor} pushed to {$repo}: {$event->payload->commits[0]->message} ({$event->payload->commits[0]->author->name})";
                }
                break;

            case 'CreateEvent':
                return "{$actor} created new {$event->payload->ref_type} {$event->payload->ref} in {$repo}";
                break;

            default:
                return "Unsupported event: {$event->type}";
        }
    }

    public function renderSearchResult($result)
    {
        return "{$result->full_name} - {$result->description}: {$result->html_url}";
    }

    public function respond()
    {
        if (!$this->requireConfig(array('github_access_token', 'github_username'))) {
            return;
        }

        $args = explode(' ', trim($this->matches[1]));

        $cmd = strtolower(array_shift($args));

        switch ($cmd) {
            case 'org':
            case 'organization':
                if (!($org = array_shift($args))) {
                    return "Please specify organization";
                }
                $org = urlencode($org);
                $url = "https://api.github.com/users/{$this->config['github_username']}/events/orgs/{$org}?access_token={$this->config['github_access_token']}";
                $type = 'event';
                break;

            case 'search':
                $search = trim(join(' ', $args));
                if (empty($search)) {
                    return "Please specify search term";
                }
                $search = urlencode($search);
                $url = "https://api.github.com/search/repositories?q={$search}&access_token={$this->config['github_access_token']}";
                $type = 'search';
                break;

            default:
                $url = "https://api.github.com/users/{$this->config['github_username']}/events?access_token={$this->config['github_access_token']}";
                $type = 'event';
        }

        $result = $this->request($url, 300, 'gh');
        if (is_array($result) && isset($result[0])) {
            return $this->renderEvent($result[0]);
        } elseif (is_object($result) && is_array($result->items) && isset($result->items[0])) {
            return $this->renderSearchResult($result->items[0]);
        } else {
            return 'Nothing found';
        }
    }
}
