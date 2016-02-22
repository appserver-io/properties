<?php

/**
 * AppserverIo\Properties\Filter\PropertyStreamFilter
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Properties\Filter;

/**
 * A stream filter implementation that replaces the variables defined in a stream with
 * the content of the properties file passed to the constructor.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class PropertyStreamFilter extends \php_user_filter
{

    /**
     * The unique filter name.
     *
     * @var string
     */
    const NAME = 'appserver-io.properties.filter.property-stream-filter';

    /**
     * The filter parameters.
     *
     * @var \AppserverIo\Properties\StreamFilterParams
     */
    public $params;

    /**
     * Returns the params.
     *
     * @return \AppserverIo\Properties\StreamFilterParams The params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * This method is called whenever data is read from or written to the attached
     * stream (such as with fread() or fwrite()).
     *
     * @param resource $in       A resource pointing to a bucket brigade which contains one or more bucket objects containing data to be filtered
     * @param resource $out      A resource pointing to a second bucket brigade into which your modified buckets should be placed
     * @param integer  $consumed Should be incremented by the length of the data which your filter reads in and alters
     * @param boolean  $closing  If the stream is in the process of closing, the closing parameter will be set to TRUE
     *
     * @return integer Whether the filter succeeded or not
     * @see php_user_filter::filter()
     */
    public function filter($in, $out, &$consumed, $closing)
    {

        // load the properties from the params
        $pattern = $this->getParams()->getPattern();
        $properties = $this->getParams()->getProperties();

        // stop processing if we can't find the properties
        if ($properties == null) {
            return PSFS_ERR_FATAL;
        }

        // while we can read from the stream
        while ($bucket = stream_bucket_make_writeable($in)) {
            // try to find and replace the variables
            foreach ($properties->getKeys() as $propertyName) {
                $bucket->data = str_replace(
                    sprintf($pattern, $propertyName),
                    $properties->getProperty($propertyName),
                    $bucket->data
                );
            }

            // update the stream
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        // continue applying filters
        return PSFS_PASS_ON;
    }
}
