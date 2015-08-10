<?php
/**
 * StandardTwoOfFiveCheck.php
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
 * Com\Tecnick\Barcode\Type\Linear\StandardTwoOfFiveCheck;
 *
 * StandardTwoOfFiveCheck Barcode type class
 * Standard 2 of 5 + CHECKSUM
 *
 * @since       2015-02-21
 * @category    Library
 * @package     Barcode
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnick.com/tc-lib-barcode
 */
class StandardTwoOfFiveCheck extends \Com\Tecnick\Barcode\Type\Linear
{
    /**
     * Barcode format
     *
     * @var string
     */
    protected $format = 'S25+';

    /**
     * Map characters to barcodes
     *
     * @var array
     */
    protected $chbar = array(
        '0' => '10101110111010',
        '1' => '11101010101110',
        '2' => '10111010101110',
        '3' => '11101110101010',
        '4' => '10101110101110',
        '5' => '11101011101010',
        '6' => '10111011101010',
        '7' => '10101011101110',
        '8' => '10101110111010',
        '9' => '10111010111010'
    );

    /**
     * Calculate the checksum
     *
     * @param $code (string) code to represent.
     *
     * @return char checksum.
     */
    protected function getChecksum($code)
    {
        $clen = strlen($code);
        $sum = 0;
        for ($idx = 0; $idx < $clen; $idx+=2) {
            $sum += intval($code[$idx]);
        }
        $sum *= 3;
        for ($idx = 1; $idx < $clen; $idx+=2) {
            $sum += intval($code[$idx]);
        }
        $check = $sum % 10;
        if ($check > 0) {
            $check = (10 - $check);
        }
        return $check;
    }

    /**
     * Get the pre-formatted code
     *
     * @return string
     */
    protected function formatCode()
    {
        return $this->code.$this->getChecksum($this->code);
    }
    
    /**
     * Get the bars array
     *
     * @return array
     *
     * @throws BarcodeException in case of error
     */
    protected function setBars()
    {
        $code = $this->formatCode();
        if ((strlen($code) % 2) != 0) {
            // add leading zero if code-length is odd
            $code = '0'.$code;
        }
        $seq = '11011010';
        $clen = strlen($code);
        for ($idx = 0; $idx < $clen; ++$idx) {
            $digit = $code[$idx];
            if (!isset($this->chbar[$digit])) {
                throw new BarcodeException('Invalid character: chr('.ord($digit).')');
            }
            $seq .= $this->chbar[$digit];
        }
        $seq .= '1101011';
        $this->processBinarySequence($seq);
    }
}
