<?php
namespace jt2k\Jarvis;

class SymfonyResponder extends Responder
{
    public static $pattern = '^symfony\s*([0-9\.]+)?$';
    public static $help = array(
        'symfony - returns information about latest and maintained versions',
        'symfony [version] - returns information about a specific version/branch'
    );

    public function respond()
    {
        $response = '';
        if (!empty($this->matches[1])) {
            $release = $this->request("https://symfony.com/releases/{$this->matches[1]}.json", 3600, 'symfony');
            if (is_object($release) && isset($release->version)) {
                $response .= sprintf("Symfony version %s\n", $release->version);
                if ($release->is_eomed) {
                    $response .= sprintf("Status: Unmaintained (since %s)\n", $release->eol);
                } elseif ($release->is_eoled) {
                    $response .= sprintf("Status: Security fixes only (since %s)\n", $release->eom);
                    $response .= sprintf("Security fixes until %s\n", $release->eol);
                } elseif ($release->is_under_development) {
                    $response .= sprintf("Status: In development (release date: %s)\n", $release->release_date);
                    $response .= sprintf("Bug fixes until %s\n", $release->eom);
                    $response .= sprintf("Security fixes until %s\n", $release->eol);
                } else {
                    $response .= "Status: Maintained\n";
                    $response .= sprintf("Bug fixes until %s\n", $release->eom);
                    $response .= sprintf("Security fixes until %s\n", $release->eol);
                }
                $response .= sprintf("Latest release: %s\n", $release->latest_patch_version);
            }
        } else {
            $releases = $this->request("https://symfony.com/releases.json", 3600, 'symfony');
            if (is_object($releases) && isset($releases->symfony_versions)) {
                $response .= sprintf("Latest stable release: %s\n", $releases->symfony_versions->stable);
                $response .= sprintf("Latest LTS release: %s\n", $releases->symfony_versions->lts);
                $response .= sprintf("Maintained branches: %s\n", implode(', ', $releases->maintained_versions));
            }
        }

        return $response;
    }
}
