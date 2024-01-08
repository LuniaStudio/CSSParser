<?php

/**
 * Class CSSParser
 *
 * A class for parsing and transforming CSS within HTML markup.
 */
class CSSParser
{
    const CONFIG_FILE = 'configs/config.ini';
    const ROOT_FILE = 'configs/root.ini';
    const ELEMENTS_FILE = 'configs/elements.ini';
    const CUSTOM_FILE = 'configs/custom.ini';
    const UTILITIES_FILE = 'configs/utilities.ini';
    const RESOLUTIONS_FILE = 'configs/resolutions.ini';

    /**
     * @var array File paths for different CSS configuration files.
     */
    private $files = [
        'config' => self::CONFIG_FILE,
        'root' => self::ROOT_FILE,
        'elements' => self::ELEMENTS_FILE,
        'custom' => self::CUSTOM_FILE,
        'utilities' => self::UTILITIES_FILE,
        'resolutions' => self::RESOLUTIONS_FILE
    ];

    /**
     * @var array Parsed content from CSS configuration files.
     */
    private $config;
    private $root;
    private $elements;
    private $custom;
    private $utilities;
    private $resolutions;

    /**
     * CSSParser constructor.
     *
     * Loads configuration files.
     *
     * @throws \RuntimeException If parsing of any configuration file fails.
     */
    public function __construct()
    {
        foreach ($this->files as $name => $file) {

            $parsedFile = parse_ini_file($file, true);

            if ($parsedFile === false) {

                throw new \RuntimeException("Failed to parse $file");
            }

            $this->$name = $parsedFile;
        }

        libxml_use_internal_errors(true);
    }

    /**
     * Parses the provided HTML markup and adds CSS styles based on configurations.
     *
     * @param string $markup HTML markup to be parsed and modified.
     * @return string Modified HTML markup with added CSS styles.
     */
    public function parse($markup)
    {
        $domDocument = new DOMDocument();
        $domDocument->preserveWhiteSpace = true;
        $domDocument->loadHTML($markup, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $headTag = $this->makeHeadTag($domDocument);
        $styleTag = $this->makeStyleTag($headTag, $domDocument);
        $this->appendStyles($styleTag, $markup);

        return $domDocument->saveHTML();
    }

    /**
     * Creates or retrieves the <head> tag in the DOM document.
     *
     * @param DOMDocument $domDocument The DOM document.
     * @return \DOMElement The <head> tag.
     */
    private function makeHeadTag($domDocument)
    {
        $headTag = $domDocument->getElementsByTagName('head')->item(0);

        if (!$headTag) {

            $headTag = $domDocument->createElement('head');
            $domDocument->getElementsByTagName('html')->item(0)->appendChild($headTag);
        }

        return $headTag;
    }

    /**
     * Creates or retrieves the <style> tag in the <head> tag of the DOM document.
     *
     * @param \DOMElement $headTag The <head> tag.
     * @param DOMDocument $domDocument The DOM document.
     * @return \DOMElement The <style> tag.
     */
    private function makeStyleTag($headTag, $domDocument)
    {
        $styleTag = $domDocument->getElementsByTagName('style')->item(0);

        if (!$styleTag) {

            $styleTag = $domDocument->createElement('style');
            $headTag->appendChild($styleTag);
        }

        return $styleTag;
    }

    /**
     * Appends styles to the <style> tag based on configurations and markup.
     *
     * @param \DOMElement $styleTag The <style> tag.
     * @param string $markup HTML markup.
     */
    private function appendStyles($styleTag, $markup)
    {
        $this->appendRootStyles($styleTag);
        $markupClasses = $this->getClasses($markup);
        $this->appendClassesStyles($styleTag, $markupClasses);
        $utilities = $this->getUtilities($markup);
        $splitUtilities = $this->splitUtilities($utilities);
        $replacedUtilities = $this->replaceUtilities($utilities, $splitUtilities);
        $replacedStyles = $this->replaceStyles($replacedUtilities);
        $implodedStyles = $this->implodeStyles($replacedStyles);
        $generatedMediaQueries = $this->generateMediaQueries($implodedStyles);

        $styleTag->nodeValue .= $generatedMediaQueries;
    }

    /**
     * Appends :root styles to the <style> tag.
     *
     * @param \DOMElement $styleTag The <style> tag.
     */
    private function appendRootStyles($styleTag)
    {
        $styleTag->nodeValue .= ":root{";

        foreach ($this->root as $property => $value) {

            $styleTag->nodeValue .= "{$property}:{$value};";
        }

        $styleTag->nodeValue .= "}";
    }

    /**
     * Appends element and custom styles to the <style> tag.
     *
     * @param \DOMElement $styleTag The <style> tag.
     * @param array $markupClasses Array of markup classes.
     */
    private function appendClassesStyles($styleTag, $markupClasses)
    {
        foreach ($this->elements as $element => $elementStyles) {

            $elementUtility = $element . '{';

            foreach ($elementStyles as $styleName => $styleValue) {

                $elementUtility .= $styleName . ':' . $styleValue . ';';
            }

            $elementUtility = rtrim($elementUtility, ';');
            $styleTag->nodeValue .= $elementUtility . '}';
        }

        foreach ($markupClasses as $class) {

            if (isset($this->custom[$class])) {

                $classStyles = $this->custom[$class];
                $classUtility = '.' . $class . '{';

                foreach ($classStyles as $styleName => $styleValue) {

                    $classUtility .= $styleName . ':' . $styleValue . ';';
                }

                $classUtility = rtrim($classUtility, ';');
                $styleTag->nodeValue .= $classUtility . '}';
            }
        }
    }

    /**
     * Extracts classes from HTML markup.
     *
     * @param string $markup HTML markup.
     * @return array Array of class names.
     */
    private function getClasses($markup)
    {
        $pattern = '/(?:class|element)\s*=\s*"(.*?)"/';

        if (preg_match_all($pattern, $markup, $results)) {

            $custom = explode(' ', implode(' ', $results[1]));
        }

        return $custom ?? [];
    }

    /**
     * Extracts utilities from HTML markup.
     *
     * @param string $markup HTML markup.
     * @return array Array of utility names.
     */
    private function getUtilities($markup)
    {
        $pattern = '/' . $this->config['attributeName'] . '\s*=\s*"(.*?)"/';

        if (preg_match_all($pattern, $markup, $results)) {

            $utilities = explode(' ', implode(' ', $results[1]));
        }

        return $utilities ?? [];
    }

    /**
     * Splits utilities into media query groups.
     *
     * @param array $utilities Array of utilities.
     * @return array Split utilities.
     */
    private function splitUtilities($utilities)
    {
        $splitUtilities = [];

        foreach ($utilities as $utility) {

            $utility = trim($utility);
            $lastCharacter = substr($utility, -1, 1);
            $count = 0;
            $mediaQuery = '';

            while ($lastCharacter !== ')' && $count < strlen($utility)) {

                $mediaQuery = $lastCharacter . $mediaQuery;
                $utility = substr($utility, 0, -1);
                $lastCharacter = substr($utility, -1, 1);
                $count++;
            }

            $splitUtilities[$mediaQuery][] = trim($utility);
        }

        $splitUtilities = array_map('array_unique', $splitUtilities);

        return $splitUtilities;
    }

    /**
     * Replaces utilities with their corresponding styles.
     *
     * @param array $utilities Array of utilities.
     * @param array $splitUtilities Split utilities.
     * @return array Replaced utilities.
     */
    private function replaceUtilities($utilities, $splitUtilities)
    {
        $replacedUtilities = [];

        foreach ($splitUtilities as $viewport => $classNames) {

            $replacedUtilities[$viewport] = array_intersect_key($this->utilities, array_flip($classNames));
        }

        return $replacedUtilities;
    }

    /**
     * Replaces utility styles with their corresponding selectors.
     *
     * @param array $replacedUtilities Replaced utilities.
     * @return array Replaced styles.
     */
    private function replaceStyles($replacedUtilities)
    {
        $replacedStyles = [];

        foreach ($replacedUtilities as $viewport => $class) {

            foreach ($class as $className => $styles) {

                $utility = '[' . $this->config['attributeName'] . '~="' . $className . $viewport . '"]{';

                foreach ($styles as $styleName => $styleValue) {

                    $utility .= $styleName . ':' . $styleValue . ';';
                }

                $utility = rtrim($utility, ';');
                $replacedStyles[$viewport][] = $utility .= '}';
            }
        }

        return $replacedStyles;
    }

    /**
     * Implodes replaced styles.
     *
     * @param array $replacedStyles Replaced styles.
     * @return array Imploded styles.
     */
    private function implodeStyles($replacedStyles)
    {
        $implodedStyles = [];

        foreach ($replacedStyles as $viewport => $replacedStyle) {

            $implodedStyles[$viewport] = implode($replacedStyle);
        }

        return $implodedStyles;
    }

    /**
     * Generates media queries for different resolutions.
     *
     * @param array $implodedStyles Imploded styles.
     * @return string Generated media queries.
     */
    private function generateMediaQueries($implodedStyles)
    {
        $generatedMediaQueries = '';

        foreach ($this->resolutions as $viewport => $ranges) {

            if (isset($implodedStyles[$viewport])) {

                $mediaQuery = '@media screen and ';
                $mediaQuery .= "(min-width: {$ranges['min']}px)";

                if (isset($ranges['max'])) {

                    $mediaQuery .= " and (max-width: {$ranges['max']}px)";
                }

                $mediaQuery .= '{' . $implodedStyles[$viewport];
                $generatedMediaQueries .= $mediaQuery . '}';
            }
        }

        return $generatedMediaQueries;
    }

    public function __destruct()
    {
        libxml_clear_errors();
    }
}
