<?php
/**
 * Spot.php
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
use \Com\Tecnick\Color\Model\Cmyk;

/**
 * Com\Tecnick\Color\Spot
 *
 * Spot Color class
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Color
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-color
 */
class Spot extends \Com\Tecnick\Color\Web
{
    /**
     * Array of default Spot colors
     * Color keys must be in lowercase and without spaces.
     *
     * @var array
     */
    protected static $default_spot_colors = array (
        'none' => array('name' => 'None',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'all' => array('name' => 'All',
            'color' => array('cyan' => 1, 'magenta' => 1, 'yellow' => 1, 'key' => 1, 'alpha' => 1)),
        'cyan' => array('name' => 'Cyan',
            'color' => array('cyan' => 1, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'magenta' => array('name' => 'Magenta',
            'color' => array('cyan' => 0, 'magenta' => 1, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'yellow' => array('name' => 'Yellow',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'key' => array('name' => 'Key',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 1, 'alpha' => 1)),
        'white' => array('name' => 'White',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
        'black' => array('name' => 'Black',
            'color' => array('cyan' => 0, 'magenta' => 0, 'yellow' => 0, 'key' => 1, 'alpha' => 1)),
        'red' => array('name' => 'Red',
            'color' => array('cyan' => 0, 'magenta' => 1, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'green' => array('name' => 'Green',
            'color' => array('cyan' => 1, 'magenta' => 0, 'yellow' => 1, 'key' => 0, 'alpha' => 1)),
        'blue' => array('name' => 'Blue',
            'color' => array('cyan' => 1, 'magenta' => 1, 'yellow' => 0, 'key' => 0, 'alpha' => 1)),
    );
    
    /**
     * Array of Spot colors
     *
     * @var array
     */
    protected $spot_colors = array();

    /**
     * Returns the array of spot colors.
     *
     * @return array Spot colors array.
     */
    public function getSpotColors()
    {
        return $this->spot_colors;
    }

    /**
     * Return the normalized version of the spot color name
     *
     * @param string $name Full name of the spot color.
     *
     * @return string
     */
    public function normalizeSpotColorName($name)
    {
        return preg_replace('/[^a-z0-9]*/', '', strtolower($name));
    }

    /**
     * Return the requested spot color data array
     *
     * @param string $name Full name of the spot color.
     *
     * @return array
     *
     * @throws ColorException if the color is not found
     */
    public function getSpotColor($name)
    {
        $key = $this->normalizeSpotColorName($name);
        if (empty($this->spot_colors[$key])) {
            // search on default spot colors
            if (empty(self::$default_spot_colors[$key])) {
                throw new ColorException('unable to find the spot color: '.$key);
            }
            $this->addSpotColor($key, new Cmyk(self::$default_spot_colors[$key]['color']));
        }
        return $this->spot_colors[$key];
    }

    /**
     * Return the requested spot color CMYK object
     *
     * @param string $name Full name of the spot color.
     *
     * @return \Com\Tecnick\Color\Web\Model\Cmyk
     *
     * @throws ColorException if the color is not found
     */
    public function getSpotColorObj($name)
    {
        $spot = $this->getSpotColor($name);
        return $spot['color'];
    }

    /**
     * Add a new spot color or overwrite an existing one with the same name.
     *
     * @param string $name  Full name of the spot color.
     * @param Cmyk   $color CMYK color object
     */
    public function addSpotColor($name, Cmyk $color)
    {
        $key = $this->normalizeSpotColorName($name);
        $num = count($this->spot_colors);
        if (isset($this->spot_colors[$key])) {
            $num = $this->spot_colors[$key]['i'];
        } else {
            $num = 1 + count($this->spot_colors);
        }
        $this->spot_colors[$key] = array(
            'i'     => $num,
            'name'  => $name,
            'color' => $color,
        );
    }
}
