<?php

/*
 * This file is part of the Mink package.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Mink\Selector\Xpath;

/**
 * XPath manipulation utility.
 *
 * @author Graham Bates
 * @author Christophe Coevoet <stof@notk.org>
 */
class Manipulator
{
    /**
     * Regex to find union operators not inside brackets.
     */
    const UNION_PATTERN = '/\|(?![^\[]*\])/';
    /**
     * Regex to find strings in the xpath. If there is
     * a | in a string, we do not want to prepend it.
     */
    const STRING_LITERALS = "/'(.*?)'/s";

    /**
     * Cache variable for string literals we patched out of
     * the xpath
     *
     * @var array $stringLiterals
     */
    private $stringLiterals;

    /**
     * Prepends the XPath prefix to the given XPath.
     *
     * The returned XPath will match elements matching the XPath inside an element
     * matching the prefix.
     *
     * @param string $xpath
     * @param string $prefix
     *
     * @return string
     */
    public function prepend($xpath, $prefix)
    {
        $expressions = array();

        $xpath = $this->replaceAllLiterals($xpath);

        // If the xpath prefix contains a union we need to wrap it in parentheses.
        if (preg_match(self::UNION_PATTERN, $prefix)) {
            $prefix = '('.$prefix.')';
        }

        // Split any unions into individual expressions.
        foreach (preg_split(self::UNION_PATTERN, $xpath) as $expression) {
            $expression = trim($expression);
            $parenthesis = '';

            // If the union is inside some braces, we need to preserve the opening braces and apply
            // the prefix only inside it.
            if (preg_match('/^[\(\s*]+/', $expression, $matches)) {
                $parenthesis = $matches[0];
                $expression = substr($expression, strlen($parenthesis));
            }

            // add prefix before element selector
            if (0 === strpos($expression, '/')) {
                $expression = $prefix.$expression;
            } else {
                $expression = $prefix.'/'.$expression;
            }
            $expressions[] = $parenthesis.$expression;
        }

        return $this->restoreAllLiterals(implode(' | ', $expressions));
    }

    private function replaceAllLiterals($xpath)
    {
        $this->stringLiterals = [];
        if (preg_match(self::STRING_LITERALS, $xpath, $matches)) {
            for ($i = 0; $i < sizeof($matches); $i++) {
                $this->stringLiterals[$i] = $matches[$i];
                $xpath = str_replace($matches[$i], "LITERAL$i", $xpath);
            }
        }
        return $xpath;
    }

    private function restoreAllLiterals($xpath) {

        for ($i = 0; $i < sizeof($this->stringLiterals); $i++) {
            $xpath = str_replace("LITERAL$i", $this->stringLiterals[$i], $xpath);
        }
        return $xpath;
    }
}
