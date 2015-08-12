<?php
/**
 * Pdf.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Color
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-color
 *
 * This file is part of tc-lib-color software library.
 */

namespace Com\Tecnick\Color;

use \Com\Tecnick\Color\Exception as ColorException;
use \Com\Tecnick\Color\Web;
use \Com\Tecnick\Color\Spot;

/**
 * Com\Tecnick\Color\Pdf
 *
 * PDF Color class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Color
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-color
 */
class Pdf extends \Com\Tecnick\Color\Spot
{
    /**
     * Array of valid JavaScript color names to be used in PDF documents
     *
     * @var array
     */
    protected static $jscolor = array(
        'transparent',
        'black',
        'white',
        'red',
        'green',
        'blue',
        'cyan',
        'magenta',
        'yellow',
        'dkGray',
        'gray',
        'ltGray',
    );

    /**
     * Return the Js color array of names
     *
     * @return array
     */
    public function getJsMap()
    {
        return self::$jscolor;
    }

    /**
     * Convert color to javascript string
     *
     * @param string|object $color color name or color object
     *
     * @return string
     */
    public function getJsColorString($color)
    {
        if (in_array($color, self::$jscolor)) {
            return 'color.'.$color;
        }
        $webcolor = new Web();
        try {
            if (($colobj = $webcolor->getColorObj($color)) !== null) {
                return $colobj->getJsPdfColor();
            }
        } catch (ColorException $e) {
        }
        // default transparent color
        return 'color.'.self::$jscolor[0];
    }

    /**
     * Returns a color object from an HTML or CSS color representation, or spor color name
     *
     * In case of errors the default color is returned
     *
     * @param string $color  HTML color to parse
     *
     * @return object or null in case of error or the color is not found
     */
    public function getColorObject($color)
    {
        try {
            return $this->getSpotColorObj($color);
        } catch (ColorException $e) {
        }
        try {
            return $this->getColorObj($color);
        } catch (ColorException $e) {
        }
        return null;
    }
}
