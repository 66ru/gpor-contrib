<?php
/**
 * File containing the ezcDocumentDocbookToRstRecurseHandler class.
 *
 * @package Document
 * @version 1.3
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Handler for elements, which are safe to ignore, but into which contents
 * should be recursed, so the childs are still converted.
 *
 * @package Document
 * @version 1.3
 */
class ezcDocumentDocbookToRstRecurseHandler extends ezcDocumentElementVisitorHandler
{
    /**
     * Handle a node
     *
     * Handle / transform a given node, and return the result of the
     * conversion.
     *
     * @param ezcDocumentElementVisitorConverter $converter
     * @param DOMElement $node
     * @param mixed $root
     * @return mixed
     */
    public function handle( ezcDocumentElementVisitorConverter $converter, DOMElement $node, $root )
    {
        return $converter->visitChildren( $node, $root );
    }
}

?>
