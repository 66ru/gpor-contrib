<?php
/**
 * File containing the ezcDocumentWikiTextNode struct
 *
 * @package Document
 * @version 1.3
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */

/**
 * Struct for Wiki document abstract syntax tree text nodes
 *
 * @package Document
 * @version 1.3
 */
class ezcDocumentWikiTextNode extends ezcDocumentWikiInlineNode
{
    /**
     * Set state after var_export
     *
     * @param array $properties
     * @return void
     * @ignore
     */
    public static function __set_state( $properties )
    {
        $nodeClass = __CLASS__;
        $node = new $nodeClass( $properties['token'] );
        $node->nodes = $properties['nodes'];
        return $node;
    }
}

?>
