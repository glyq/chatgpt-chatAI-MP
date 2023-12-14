<?php

namespace app\common\library;

use Exception;

/**
 * 安全过滤类
 *
 * @category       Security
 * @copyright      Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright      Copyright (c) 2014 - 2019, British Columbia Institute of Technology (https://bcit.ca/)
 * @license        https://opensource.org/licenses/MIT MIT License
 * @link           https://codeigniter.com
 * @author         EllisLab Dev Team
 */
class Security
{
    protected static $instance = null;

    /**
     * List of sanitize filename strings
     *
     * @var    array
     */
    public $filename_bad_chars = array(
        '../',
        '<!--',
        '-->',
        '<',
        '>',
        "'",
        '"',
        '&',
        '$',
        '#',
        '{',
        '}',
        '[',
        ']',
        '=',
        ';',
        '?',
        '%20',
        '%22',
        '%3c',        // <
        '%253c',    // <
        '%3e',        // >
        '%0e',        // >
        '%28',        // (
        '%29',        // )
        '%2528',    // (
        '%26',        // &
        '%24',        // $
        '%3f',        // ?
        '%3b',        // ;
        '%3d'        // =
    );

    /**
     * Character set
     *
     * Will be overridden by the constructor.
     *
     * @var    string
     */
    public $charset = 'UTF-8';

    /**
     * XSS Hash
     *
     * Random Hash for protecting URLs.
     *
     * @var    string
     */
    protected $_xss_hash;

    /**
     * List of never allowed strings
     *
     * @var    array
     */
    protected $_never_allowed_str = array(
        'document.cookie'   => '[removed]',
        '(document).cookie' => '[removed]',
        'document.write'    => '[removed]',
        '(document).write'  => '[removed]',
        '.parentNode'       => '[removed]',
        '.innerHTML'        => '[removed]',
        '-moz-binding'      => '[removed]',
        '<!--'              => '&lt;!--',
        '-->'               => '--&gt;',
        '<![CDATA['         => '&lt;![CDATA[',
        '<comment>'         => '&lt;comment&gt;',
        '<%'                => '&lt;&#37;'
    );

    /**
     * List of never allowed regex replacements
     *
     * @var    array
     */
    protected $_never_allowed_regex = array(
        'javascript\s*:',
        '(\(?document\)?|\(?window\)?(\.document)?)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?"
    );

    protected $options = [
        'placeholder' => '[removed]'
    ];

    /**
     * Class constructor
     *
     * @return    void
     */
    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
        foreach ($this->_never_allowed_str as $index => &$item) {
            $item = str_replace('[removed]', $this->options['placeholder'], $item);
        }
    }

    /**
     *
     * @param array $options 参数
     * @return Security
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     *     It's not something that should be used for general
     *     runtime processing.
     *
     * @link    http://channel.bitflux.ch/wiki/XSS_Prevention
     *        Based in part on some code and ideas from Bitflux.
     *
     * @link    http://ha.ckers.org/xss.html
     *        To help develop this script I used this great list of
     *        vulnerabilities along with a few other hacks I've
     *        harvested from examining vulnerabilities in other programs.
     *
     * @param string|string[] $str      Input data
     * @param bool            $is_image Whether the input is an image
     * @return    string
     */
    public function xss_clean($str, $is_image = false)
    {
        // Is the string an array?
        if (is_array($str)) {
            foreach ($str as $key => &$value) {
                $str[$key] = $this->xss_clean($value);
            }

            return $str;
        }

        // Remove Invisible Characters
        $str = $this->remove_invisible_characters($str);

        /*
         * URL Decode
         *
         * Just in case stuff like this is submitted:
         *
         * <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         *
         * Note: Use rawurldecode() so it does not remove plus signs
         */
        if (stripos($str, '%') !== false) {
            do {
                $oldstr = $str;
                $str = rawurldecode($str);
                $str = preg_replace_callback('#%(?:\s*[0-9a-f]){2,}#i', array($this, '_urldecodespaces'), $str);
            } while ($oldstr !== $str);
            unset($oldstr);
        }

        /*
         * Convert character entities to ASCII
         *
         * This permits our tests below to work reliably.
         * We only convert entities that are within tags since
         * these are the ones that will pose security problems.
         */
        $str = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", array($this, '_convert_attribute'), $str);
        $str = preg_replace_callback('/<\w+.*/si', array($this, '_decode_entity'), $str);

        // Remove Invisible Characters Again!
        $str = $this->remove_invisible_characters($str);

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja	vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $str = str_replace("\t", ' ', $str);

        // Capture converted string for later comparison
        $converted_string = $str;

        // Remove Strings that are never allowed
        $str = $this->_do_never_allowed($str);

        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($is_image === true) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $str = preg_replace('/<\?(php)/i', '&lt;?\\1', $str);
        } else {
            $str = str_replace(array('<?', '?' . '>'), array('&lt;?', '?&gt;'), $str);
        }

        /*
         * Compact any exploded words
         *
         * This corrects words like:  j a v a s c r i p t
         * These words are compacted back to their correct state.
         */
        $words = array(
            'javascript',
            'expression',
            'vbscript',
            'jscript',
            'wscript',
            'vbs',
            'script',
            'base64',
            'applet',
            'alert',
            'document',
            'write',
            'cookie',
            'window',
            'confirm',
            'prompt',
            'eval'
        );

        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)) . '\s*';

            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $str = preg_replace_callback('#(' . substr($word, 0, -3) . ')(\W)#is', array($this, '_compact_exploded_words'), $str);
        }

        /*
         * Remove disallowed Javascript in links or img tags
         * We used to do some version comparisons and use of stripos(),
         * but it is dog slow compared to these simplified non-capturing
         * preg_match(), especially if the pattern exists in the string
         *
         * Note: It was reported that not only space characters, but all in
         * the following pattern can be parsed as separators between a tag name
         * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
         * ... however, $this->remove_invisible_characters() above already strips the
         * hex-encoded ones, so we'll skip them below.
         */
        do {
            $original = $str;

            if (preg_match('/<a/i', $str)) {
                $str = preg_replace_callback('#<a(?:rea)?[^a-z0-9>]+([^>]*?)(?:>|$)#si', array($this, '_js_link_removal'), $str);
            }

            if (preg_match('/<img/i', $str)) {
                $str = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', array($this, '_js_img_removal'), $str);
            }

            if (preg_match('/script|xss/i', $str)) {
                $str = preg_replace('#</*(?:script|xss).*?>#si', $this->options['placeholder'], $str);
            }
        } while ($original !== $str);
        unset($original);

        /*
         * Sanitize naughty HTML elements
         *
         * If a tag containing any of the words in the list
         * below is found, the tag gets converted to entities.
         *
         * So this: <blink>
         * Becomes: &lt;blink&gt;
         */
        $pattern = '#'
            . '<((?<slash>/*\s*)((?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)|.+)' // tag start and name, followed by a non-tag character
            . '[^\s\042\047a-z0-9>/=]*' // a valid attribute character immediately after the tag would count as a separator
            // optional attributes
            . '(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters, excluding > (tag close) for obvious reasons
            . '[^\s\042\047>/=]+' // attribute characters
            // optional attribute-value
            . '(?:\s*=' // attribute-value separator
            . '(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value
            . ')?' // end optional attribute-value group
            . ')*)' // end optional attributes group
            . '[^>]*)(?<closeTag>\>)?#isS';

        // Note: It would be nice to optimize this for speed, BUT
        //       only matching the naughty elements here results in
        //       false positives and in turn - vulnerabilities!
        do {
            $old_str = $str;
            $str = preg_replace_callback($pattern, array($this, '_sanitize_naughty_html'), $str);
        } while ($old_str !== $str);
        unset($old_str);

        /*
         * Sanitize naughty scripting elements
         *
         * Similar to above, only instead of looking for
         * tags it looks for PHP and JavaScript commands
         * that are disallowed. Rather than removing the
         * code, it simply converts the parenthesis to entities
         * rendering the code un-executable.
         *
         * For example:	eval('some code')
         * Becomes:	eval&#40;'some code'&#41;
         */
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $str
        );

        // Same thing, but for "tag functions" (e.g. eval`some code`)
        $str = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)`(.*?)`#si',
            '\\1\\2&#96;\\3&#96;',
            $str
        );

        // Final clean up
        // This adds a bit of extra precaution in case
        // something got through the above filters
        $str = $this->_do_never_allowed($str);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($is_image === true) {
            return ($str === $converted_string);
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * XSS Hash
     *
     * Generates the XSS hash if needed and returns it.
     *
     * @return    string    XSS hash
     */
    public function xss_hash()
    {
        if ($this->_xss_hash === null) {
            $rand = $this->get_random_bytes(16);
            $this->_xss_hash = ($rand === false)
                ? md5(uniqid(mt_rand(), true))
                : bin2hex($rand);
        }

        return $this->_xss_hash;
    }

    // --------------------------------------------------------------------

    /**
     * Get random bytes
     *
     * @param int $length Output length
     * @return    string
     */
    public function get_random_bytes($length)
    {
        if (empty($length) or !ctype_digit((string)$length)) {
            return false;
        }

        if (function_exists('random_bytes')) {
            try {
                // The cast is required to avoid TypeError
                return random_bytes((int)$length);
            } catch (Exception $e) {
                // If random_bytes() can't do the job, we can't either ...
                // There's no point in using fallbacks.
                return false;
            }
        }

        // Unfortunately, none of the following PRNGs is guaranteed to exist ...
        if (defined('MCRYPT_DEV_URANDOM') && ($output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM)) !== false) {
            return $output;
        }


        if (is_readable('/dev/urandom') && ($fp = fopen('/dev/urandom', 'rb')) !== false) {
            // Try not to waste entropy ...
            stream_set_chunk_size($fp, $length);
            $output = fread($fp, $length);
            fclose($fp);
            if ($output !== false) {
                return $output;
            }
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }

        return false;
    }

    // --------------------------------------------------------------------

    /**
     * HTML Entities Decode
     *
     * A replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly. html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @link    https://secure.php.net/html-entity-decode
     *
     * @param string $str     Input
     * @param string $charset Character set
     * @return    string
     */
    public function entity_decode($str, $charset = null)
    {
        if (strpos($str, '&') === false) {
            return $str;
        }

        static $_entities;

        isset($charset) or $charset = $this->charset;
        isset($_entities) or $_entities = array_map('strtolower', get_html_translation_table(HTML_ENTITIES, ENT_COMPAT | ENT_HTML5, $charset));

        do {
            $str_compare = $str;

            // Decode standard entities, avoiding false positives
            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $str, $matches)) {
                $replace = array();
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match . ';', $_entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }

                $str = str_replace(array_keys($replace), array_values($replace), $str);
            }

            // Decode numeric & UTF16 two byte entities
            $str = html_entity_decode(
                preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $str),
                ENT_COMPAT | ENT_HTML5,
                $charset
            );
        } while ($str_compare !== $str);
        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Sanitize Filename
     *
     * @param string $str           Input file name
     * @param bool   $relative_path Whether to preserve paths
     * @return    string
     */
    public function sanitize_filename($str, $relative_path = false)
    {
        $bad = $this->filename_bad_chars;

        if (!$relative_path) {
            $bad[] = './';
            $bad[] = '/';
        }

        $str = $this->remove_invisible_characters($str, false);

        do {
            $old = $str;
            $str = str_replace($bad, '', $str);
        } while ($old !== $str);

        return stripslashes($str);
    }

    // ----------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @param string $str
     * @return    string
     */
    public function strip_image_tags($str)
    {
        return preg_replace(
            array(
                '#<img[\s/]+.*?src\s*=\s*(["\'])([^\\1]+?)\\1.*?\>#i',
                '#<img[\s/]+.*?src\s*=\s*?(([^\s"\'=<>`]+)).*?\>#i'
            ),
            '\\2',
            $str
        );
    }

    // ----------------------------------------------------------------

    /**
     * URL-decode taking spaces into account
     *
     * @param array $matches
     * @return    string
     */
    protected function _urldecodespaces($matches)
    {
        $input = $matches[0];
        $nospaces = preg_replace('#\s+#', '', $input);
        return ($nospaces === $input)
            ? $input
            : rawurldecode($nospaces);
    }

    // ----------------------------------------------------------------

    /**
     * Compact Exploded Words
     *
     * Callback method for xss_clean() to remove whitespace from
     * things like 'j a v a s c r i p t'.
     *
     * @param array $matches
     * @return    string
     */
    protected function _compact_exploded_words($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
    }

    // --------------------------------------------------------------------

    /**
     * Sanitize Naughty HTML
     *
     * Callback method for xss_clean() to remove naughty HTML elements.
     *
     * @param array $matches
     * @return    string
     */
    protected function _sanitize_naughty_html($matches)
    {
        static $naughty_tags = array(
            'alert',
            'area',
            'prompt',
            'confirm',
            'applet',
            'audio',
            'basefont',
            'base',
            'behavior',
            'bgsound',
            'blink',
            'body',
            'embed',
            'expression',
            'form',
            'frameset',
            'frame',
            'head',
            'html',
            'ilayer',
            'iframe',
            'input',
            'button',
            'select',
            'isindex',
            'layer',
            'link',
            'meta',
            'keygen',
            'object',
            'plaintext',
            'style',
            'script',
            'textarea',
            'title',
            'math',
            'video',
            'svg',
            'xml',
            'xss'
        );

        static $evil_attributes = array(
            'on\w+',
            'style',
            'xmlns',
            'formaction',
            'form',
            'xlink:href',
            'FSCommand',
            'seekSegmentTime'
        );

        // First, escape unclosed tags
        if (empty($matches['closeTag'])) {
            return '&lt;' . $matches[1];
        } // Is the element that we caught naughty? If so, escape it
        elseif (in_array(strtolower($matches['tagName']), $naughty_tags, true)) {
            return '&lt;' . $matches[1] . '&gt;';
        } // For other tags, see if their attributes are "evil" and strip those
        elseif (isset($matches['attributes'])) {
            // We'll store the already filtered attributes here
            $attributes = array();

            // Attribute-catching pattern
            $attributes_pattern = '#'
                . '(?<name>[^\s\042\047>/=]+)' // attribute characters
                // optional attribute-value
                . '(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))' // attribute-value separator
                . '#i';

            // Blacklist pattern for evil attribute names
            $is_evil_pattern = '#^(' . implode('|', $evil_attributes) . ')$#i';

            // Each iteration filters a single attribute
            do {
                // Strip any non-alpha characters that may precede an attribute.
                // Browsers often parse these incorrectly and that has been a
                // of numerous XSS issues we've had.
                $matches['attributes'] = preg_replace('#^[^a-z]+#i', '', $matches['attributes']);

                if (!preg_match($attributes_pattern, $matches['attributes'], $attribute, PREG_OFFSET_CAPTURE)) {
                    // No (valid) attribute found? Discard everything else inside the tag
                    break;
                }

                if (
                    // Is it indeed an "evil" attribute?
                    preg_match($is_evil_pattern, $attribute['name'][0])
                    // Or does it have an equals sign, but no value and not quoted? Strip that too!
                    or (trim($attribute['value'][0]) === '')
                ) {
                    $attributes[] = 'xss=removed';
                } else {
                    $attributes[] = $attribute[0][0];
                }

                $matches['attributes'] = substr($matches['attributes'], $attribute[0][1] + strlen($attribute[0][0]));
            } while ($matches['attributes'] !== '');

            $attributes = empty($attributes)
                ? ''
                : ' ' . implode(' ', $attributes);
            return '<' . $matches['slash'] . $matches['tagName'] . $attributes . '>';
        }

        return $matches[0];
    }

    // --------------------------------------------------------------------

    /**
     * JS Link Removal
     *
     * Callback method for xss_clean() to sanitize links.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings.
     *
     * @param array $match
     * @return    string
     */
    protected function _js_link_removal($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;|`|&\#96;)|javascript:|livescript:|mocha:|charset=|window\.|\(?document\)?\.|\.cookie|<script|<xss|d\s*a\s*t\s*a\s*:)#si',
                '',
                $this->_filter_attributes($match[1])
            ),
            $match[0]
        );
    }

    // --------------------------------------------------------------------

    /**
     * JS Image Removal
     *
     * Callback method for xss_clean() to sanitize image tags.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings.
     *
     * @param array $match
     * @return    string
     */
    protected function _js_img_removal($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;|`|&\#96;)|javascript:|livescript:|mocha:|charset=|window\.|\(?document\)?\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->_filter_attributes($match[1])
            ),
            $match[0]
        );
    }

    // --------------------------------------------------------------------

    /**
     * Attribute Conversion
     *
     * @param array $match
     * @return    string
     */
    protected function _convert_attribute($match)
    {
        return str_replace(array('>', '<', '\\'), array('&gt;', '&lt;', '\\\\'), $match[0]);
    }

    // --------------------------------------------------------------------

    /**
     * Filter Attributes
     *
     * Filters tag attributes for consistency and safety.
     *
     * @param string $str
     * @return    string
     */
    protected function _filter_attributes($str)
    {
        $out = '';
        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $str, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace('#/\*.*?\*/#s', '', $match);
            }
        }

        return $out;
    }

    // --------------------------------------------------------------------

    /**
     * HTML Entity Decode Callback
     *
     * @param array $match
     * @return    string
     */
    protected function _decode_entity($match)
    {
        // Protect GET variables in URLs
        // 901119URL5918AMP18930PROTECT8198
        $match = preg_replace('|\&([a-z\_0-9\-]+)\=([a-z\_0-9\-/]+)|i', $this->xss_hash() . '\\1=\\2', $match[0]);

        // Decode, then un-protect URL GET vars
        return str_replace(
            $this->xss_hash(),
            '&',
            $this->entity_decode($match, $this->charset)
        );
    }

    // --------------------------------------------------------------------

    /**
     * Do Never Allowed
     *
     * @param string
     * @return    string
     */
    protected function _do_never_allowed($str)
    {
        $str = str_replace(array_keys($this->_never_allowed_str), $this->_never_allowed_str, $str);

        foreach ($this->_never_allowed_regex as $regex) {
            $str = preg_replace('#' . $regex . '#is', $this->options['placeholder'], $str);
        }

        return $str;
    }

    /**
     * Remove Invisible Characters
     */
    public function remove_invisible_characters($str, $url_encoded = true)
    {
        $non_displayables = array();

        // every control character except newline (dec 10),
        // carriage return (dec 13) and horizontal tab (dec 09)
        if ($url_encoded) {
            $non_displayables[] = '/%0[0-8bcef]/i';    // url encoded 00-08, 11, 12, 14, 15
            $non_displayables[] = '/%1[0-9a-f]/i';    // url encoded 16-31
            $non_displayables[] = '/%7f/i';    // url encoded 127
        }

        $non_displayables[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';    // 00-08, 11, 12, 14-31, 127

        do {
            $str = preg_replace($non_displayables, '', $str, -1, $count);
        } while ($count);

        return $str;
    }
}
