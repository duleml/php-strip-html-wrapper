<?php
namespace App;

class StripHtml implements StripState {

    public function Strip($html){
        $html = $this->preprocess($html);
        $plain = $this->stripTags($html);
        $plain = mb_eregi_replace(" +", " ", $plain);
        return trim($plain);
    }

    function preprocess($html){
        //remove comments
        $clean = mb_eregi_replace("<!.*?>", " ", $html);
        //remove script
        $clean = mb_eregi_replace("<script[^>]*>.*?(<\\/script[^>]*>|$)", " ", $clean);
        //remove stylesheets
        $clean = mb_eregi_replace("<style[^>]*>.*?(<\\/style[^>]*>|$)", " ", $clean);
        //remove comments with ?
        $clean = mb_eregi_replace("<\\?.*?>", " ", $clean);

        return $clean;
    }

    function stripTags($html)
    {
        $plain = "";
        $state = StripState::AttributeState;

        while ($state == StripState::AttributeState) {
            $plain = "";
            $lastQuotePos = 0;
            $state = StripState::ContentState;

            $prevc = 'x'; //previous non-white char
            for ($i = 0; $i < strlen($html); ++$i) {
                $c = $html[$i];
                if ($c == '<' && $state == StripState::ContentState && (strlen($html) <= $i + 1
                        || (ctype_alnum($html[$i + 1]) || $html[$i + 1] == '/'))
                ) {
                    $state = StripState::TagState;
                } elseif ($state == StripState::ContentState) {
                    $plain .= $c;
                } elseif ($c == '>' && $state == StripState::TagState) {
                    $state = StripState::ContentState;
                    $plain .= ' ';
                } elseif ($c == '"' && $state != StripState::ContentState) {
                    if ($prevc == '=' && $state == StripState::TagState) {
                        $state = StripState::AttributeState;
                    } else if ($state == StripState::AttributeState) {
                        $state = StripState::TagState;
                        $lastQuotePos = $i;
                    }
                }

                if ($state == StripState::TagState && ctype_space($c)) {
                    $prevc = $c;
                }
            }

            if ($state == StripState::AttributeState) {
                //Parsing ended in an attribute. Remove last " and retry.
                $removePos = strpos($html, '\"');
                // Debug.Assert(removePos != -1, "Expected a \" char.");
                unset($html[$removePos]);

            }
        }
            $cleaned = $this->postprocess((string)$plain);
            return $cleaned;
    }

    function postprocess($text) {
        $text = mb_eregi_replace('\\s+', " ", $text);
        return trim($text);
    }
}
