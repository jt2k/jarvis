<?php
namespace jt2k\Jarvis;

class BibleResponder extends Responder
{
    public static $pattern = '^(?:bible|esv) (.+)$';

    public function respond()
    {
        $passage = urlencode(trim($this->matches[1]));
        $url = "http://www.esvapi.org/v2/rest/passageQuery?passage={$passage}&key=IP&output-format=plain-text&include-passage-references=false&include-verse-numbers=false&include-first-verse-numbers=false&include-footnotes=false&include-short-copyright=true&include-passage-horizontal-lines=false&include-heading-horizontal-lines=false&include-headings=false&include-subheadings=false&include-selahs=false&line-length=0";

        return $this->requestRaw($url);
    }
}
