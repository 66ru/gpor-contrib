<?php
/**
 * File containing the ezcWebdavMakeCollectionResponse class.
 *
 * @package Webdav
 * @version 1.1.3
 * @copyright Copyright (C) 2005-2009 eZ Systems AS. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 */
/**
 * Class generated by the backend to respond to MKCOL requests.
 *
 * If a {@link ezcWebdavBackend} receives an instance of {@link
 * ezcWebdavMakeCollectionRequest} it might react with an instance of {@link
 * ezcWebdavMakeCollectionResponse} or with producing an error.
 *
 * @version 1.1.3
 * @package Webdav
 */
class ezcWebdavMakeCollectionResponse extends ezcWebdavResponse
{
    /**
     * Creates a new response object.
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct( ezcWebdavResponse::STATUS_201 );
    }
}

?>
