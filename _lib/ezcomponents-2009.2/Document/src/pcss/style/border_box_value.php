<?php
/**
 * File containing the ezcDocumentPcssStyleBorderBoxValue class.
 *
 * @package Document
 * @version 1.3
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @access private
 */

/**
 * Style directive border box value representation.
 *
 * @package Document
 * @access private
 * @version 1.3
 */
class ezcDocumentPcssStyleBorderBoxValue extends ezcDocumentPcssStyleBoxValue
{
    /**
     * Get sub value handler.
     * 
     * @return ezcDocumentPcssStyleValue
     */
    protected function getSubValue()
    {
        return 'ezcDocumentPcssStyleBorderValue';
    }
}

?>