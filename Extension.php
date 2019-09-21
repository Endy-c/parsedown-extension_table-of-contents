<?php
/**
 * ToC Extension/Plugin for Parsedown.
 * ============================================================================
 * It creates a list of contents table from headings.
 *
 * @author  KEINOS (https://github.com/KEINOS/)
 * @package Parsedown ^1.7 (https://github.com/erusev/parsedown)
 * @see Howto: https://github.com/KEINOS/parsedown-extension_table-of-contents/
 * @license https://github.com/KEINOS/parsedown-extension_table-of-contents/LICENSE
*/

class ParsedownToc extends \Parsedown
{
    /* Methods/Functions (in ABC order) */

    #
    # Block
    #
    protected function blockHeader($Line)
    {
        if (isset($Line['text'][1])) {
            $Block = \Parsedown::blockHeader($Line);

            // Compatibility with old Parsedown Version
            if (isset($Block['element']['handler']['argument'])) {
                $text  = $Block['element']['handler']['argument'];
            }

            if (isset($Block['element']['text'])) {
                $text  = $Block['element']['text'];
            }

            $level = $Block['element']['name'];    //levels are h1, h2, ..., h6
            $id    = $this->createAnchorID($text);

            //Set attributes to head tags
            $Block['element']['attributes'] = array(
                'id'   => $id,
                'name' => $id,
            );

            $this->setContentsList(array(
                'text'  => $text,
                'id'    => $id,
                'level' => $level
            ));

            return $Block;
        }
    }

    public function contentsList($Return_as = 'string')
    {
        if ('string' === strtolower($Return_as)) {
            $result = '';
            if (! empty($this->contentsListString)) {
                $result = $this->text($this->contentsListString);
            }
            return $result;
        }
        if ('json' === strtolower($Return_as)) {
            return json_encode($this->contentsListArray);
        }

        error_log('Unknown return type given while parsing ToC. At: ' . __FUNCTION__ . '() in Line:' . __LINE__ . ' (Using default type)');
        return $this->contentsList('string');
    }

    protected function createAnchorID($Text)
    {
        return  urlencode($this->fetchText($Text));
    }

    protected function fetchText($Text)
    {
        return trim(strip_tags($this->line($Text)));
    }

    #
    # Setters
    #
    protected function setContentsList($Content)
    {
        $this->setContentsListAsArray($Content);
        $this->setContentsListAsString($Content);
    }

    protected function setContentsListAsArray($Content)
    {
        $this->contentsListArray[] = $Content;
    }
    protected $contentsListArray = array();

    protected function setContentsListAsString($Content)
    {
        $text  = $this->fetchText($Content['text']);
        $id    = $Content['id'];
        $level = (integer) trim($Content['level'], 'h');
        $link  = "[${text}](#${id})";

        if ($this->firstHeadLevel === 0) {
            $this->firstHeadLevel = $level;
        }
        $cutIndent = $this->firstHeadLevel - 1;
        if ($cutIndent > $level) {
            $level = 1;
        } else {
            $level = $level - $cutIndent;
        }

        $indent = str_repeat('  ', $level);

        $this->contentsListString .= "${indent}- ${link}\n";
    }
    protected $contentsListString = '';
    protected $firstHeadLevel = 0;

}
