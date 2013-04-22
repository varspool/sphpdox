<?php

namespace Sphpdox;

class CommentParser
{
    protected $comment;
    protected $shortDescription;
    protected $longDescription = null;
    protected $annotations = array();

    /**
     * A separately indexed array of annotations by name, for ease of use
     *
     * The @suffix is also removed
     *
     * @var array<string => array<string>>
     */
    protected $annotationsByName = array();

    /**
     * Constructor
     *
     * @param string $docblock
     */
    public function __construct($comment)
    {
        $this->comment = $comment;
        $this->parse();
    }

    /**
     * Parses the docblock
     */
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

    protected function addAnnotation($annotation)
    {
        if ($annotation) {
            if (preg_match('/@(\w+)/', $annotation, $matches)) {
                $this->annotationsByName[$matches[1]][] = $annotation;
            }
            $this->annotations[] = $annotation;
        }
    }

    /**
     * Splits the simplified comment string into parts (annotations plus
     *     descriptions)
     *
     * @param string $content
     */
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
                $this->addAnnotation($annotation);
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

        $this->addAnnotation($annotation);

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

    /**
     * Gets all the annotations on the method
     *
     * @return array
     */
    public function getAnnotations()
    {
        return $this->annotations;
    }

    /**
     * Gets all annotations of the specified name, if there are any
     *
     * @param string $name
     * @return array<string>
     */
    public function getAnnotationsByName($name)
    {
        if (isset($this->annotationsByName[$name])) {
            return $this->annotationsByName[$name];
        }
        return array();
    }

    /**
     * Whether the comment has at least one annotation of the given name
     *
     * @param string $name
     * @return boolean
     */
    public function hasAnnotation($name)
    {
        return !empty($this->annotationsByName[$name]);
    }

    /**
     * Gets the short and long description in the comment
     *
     * The full comment if you like. If this docblock were processed,
     * the former paragraph and this one would be returned.
     *
     * @return string
     */
    public function getDescription()
    {
        $description = $this->getShortDescription();
        if ($this->hasLongDescription()) {
            $description .= "\n\n" . $this->getLongDescription();
        }
        return $description;
    }

    /**
     * Gets the short description in the comment
     *
     * That's just the first paragraph
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Gets the long description
     *
     * Some methods dont have descriptions, in which case this will be null
     *
     * @return string|null
     */
    public function getLongDescription()
    {
        return $this->longDescription;
    }

    /**
     * Whether the comment has any sort of description, long or short
     *
     * @return boolean
     */
    public function hasDescription()
    {
        return (boolean)$this->shortDescription;
    }

    /**
     * Whether the comment has a long description
     *
     * @return boolean
     */
    public function hasLongDescription()
    {
        return $this->longDescription != null;
    }
}