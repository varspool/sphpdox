<?php

namespace Sphpdox;

class CommentParser
{
    protected $comment;
    protected $shortDescription;
    protected $longDescription = null;
    protected $annotations = array();

    /**
     * @param string $docblock
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
        $this->parse();
    }

    protected function parse()
    {
        $content = $this->comment;

        // Rewrite newlines
        $content = preg_replace('/\r\n/', "\n", $content);

        // Rewrite tabs
        $content = preg_replace('/\t/', '    ', $content);

        // Remove initial whitespace
        $content = preg_replace('/^\s*/m', '', $content);

        // Remove start and end comment markers
        $content = preg_replace('/\s*\/\*\*#?@?\+?\s*/m', '', $content);
        $content = preg_replace('/\s*\*\/\s*/m', '', $content);

        // Remove start of line comment markers
        $content = preg_replace('/^\* ?/m', '', $content);

        // Split the comment into parts
        $this->split($content);
    }

    protected function split($content)
    {
        // Pull off all annotation lines
        $continuation = false;
        $annotation = '';

        $lines = explode("\n", $content);
        $remaining = $lines;

        foreach ($lines as $i => $line) {
            if (!$line) {
                $continuation = false;
                continue;
            }

            if ($line[0] == '@') {
                if ($annotation) {
                    $this->annotations[] = $annotation;
                }
                $annotation = '';
                $continuation = true;
            } elseif ($continuation) {
                $annotation .= ' ';
            } else {
                continue;
            }

            $annotation .= trim($line);
            unset($remaining[$i]);
        }

        if ($annotation) {
            $this->annotations[] = $annotation;
        }

        // Split remaining lines by paragrah
        $remaining = implode("\n", $remaining);
        $parts = preg_split("/(\n\n|\r\n\r\n)/", $remaining, -1, PREG_SPLIT_NO_EMPTY);

        // Into two parts
        if ($parts) {
            $first = $parts[0];
            $this->shortDescription = trim($first);

            $rest = array_slice($parts, 1);
            if ($rest) {
                $long = implode("\n\n", $rest);
                $long = preg_replace('/(\w) *\n *(\w)/', '\1 \2', $long);
                $long = trim($long);
                $this->longDescription = $long;
            }
        }
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function getDescription()
    {
        $description = $this->getShortDescription();
        if ($this->hasLongDescription()) {
            $description .= "\n\n" . $this->getLongDescription();
        }
        return $description;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function getLongDescription()
    {
        return $this->longDescription;
    }

    public function hasDescription()
    {
        return (boolean)$this->shortDescription;
    }

    public function hasLongDescription()
    {
        return $this->longDescription != null;
    }
}