<?php
/**
 * InterleavedTwoOfFive.php
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-barcode
 *
 * This file is part of tc-lib-barcode software library.
 */

namespace Com\Tecnick\Barcode\Type\Linear;

use \Com\Tecnick\Barcode\Exception as BarcodeException;

/**
 * Com\Tecnick\Barcode\Type\Linear\InterleavedTwoOfFive;
 *
 * InterleavedTwoOfFive Barcode type class
 * Interleaved 2 of 5
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-barcode
 */
class InterleavedTwoOfFive extends \Com\Tecnick\Barcode\Type\Linear\InterleavedTwoOfFiveCheck
{
    /**
     * Barcode format
     *
     * @var string
     */
    protected $format = 'I25';

    /**
     * Get the pre-formatted code
     *
     * @return string
     */
    protected function formatCode()
    {
        return $this->code;
    }
}
