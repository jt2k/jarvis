<?php

namespace jt2k\Jarvis;

class ManagerResponder extends Responder
{
    /**
     * Examples:
     * manage Alice re the security audit
     * followup with Bob about the training program
     */
    public static $pattern = '(manage|mgr|follow\s*up)\s+(with\s+)?(.*?)\s+(regarding|re|about)\s+(.*?)$';

    /** Percent chance that the stresses of management result in a psychotic episode. */
    const PYSCHOTIC_TENDENCY = 0.4;

    /** The calm, sane communication used to ensure good will and organizational efficiency. */
    private static $replies = [
        "Just following up on {{SUBJECT}}. Please let me know if you need any more info!",
        "Looping you in on this. Any thoughts on {{SUBJECT}}?",
        "We have some eager project managers who are hoping to get {{SUBJECT}} out the door by the end of " .
            "the month. Any updates I can relay to them?",
        "Given the complexities involved with {{SUBJECT}}, I think we should discuss in realtime. ".
            "Can we do a quick Goto?",
        "May I please have a center number for {{SUBJECT}}?"
    ];

    /** The friendly, professional face presented to coworkers and stakeholders. */
    private static $replyEmoji = ':man_in_business_suit_levitating:';

    /** The sudden bursts of anger that erupt unbidden onto the keyboard. */
    private static $psychoReplies = [
        "If I had access to {{SUBJECT}}, then I could do it myself. But unfortunately I have to go through you.",
        "/remind me to harass you every 3 business days about {{SUBJECT}} until you finally reply.",
        "You keep using that word '{{KEYWORD}}'... I do not think it means what you think it means."
    ];

    /** The twisted face of rage lurking beneath the facade of decent human discourse. */
    private static $psychoEmoji = ':japanese_goblin:';

    /**
     * Tries to find an "interesting" keyword within the given subject.
     * @param string $subject
     * @return string/false
     */
    private function findKeyword($subject)
    {
        $tokens = preg_split('/\s+/', $subject);
        foreach ($tokens as $token) {
            // dumb heuristic to detect an interesting keyword
            if (strlen($token) > 5) {
                return $token;
            }
        }

        return false;
    }

    public function respond($redirect = false)
    {
        $employee = $this->matches[3];
        $subject = rtrim($this->matches[5], ".!? \n\r");
        $keyword = $this->findKeyword($subject);
        $replies = self::$replies;
        $emoji = self::$replyEmoji;
        $greeting = 'Hi';
        $sig = 'Thanks';

        if (rand(1, 10) <= 10 * self::PYSCHOTIC_TENDENCY) {
            $replies = self::$psychoReplies;
            $emoji = self::$psychoEmoji;
            $greeting = 'Yo';
            $sig = 'Deal with it';
        }

        // only choose a KEYWORD reply if we actually have a keyword
        do {
            $reply = $replies[rand(0, count($replies) - 1)];
        } while (strpos($reply, '{{KEYWORD}}') !== false && $keyword === false);

        $body = str_replace(['{{SUBJECT}}', '{{KEYWORD}}'], [$subject, $keyword], $reply);

        return <<<EOT
>$greeting $employee,
>
>$body
>
>$sig,
>$emoji
EOT;
    }
}
