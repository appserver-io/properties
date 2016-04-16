<?php

/**
 * \AppserverIo\Properties\PropertiesUtil
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */

namespace AppserverIo\Properties;

use AppserverIo\Properties\Filter\PropertyStreamFilter;
use AppserverIo\Properties\Filter\PropertyStreamFilterParams;

/**
 * Utility class providing some helper functionality to handle properties files.
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class PropertiesUtil
{

    /**
     * The singleton instance.
     *
     * @var \AppserverIo\Properties\PropertiesUtil
     */
    protected static $instance;

    /**
     * This is a utility class, so protect it against direct
     * instantiation.
     */
    private function __construct()
    {
    }

    /**
     * This is a utility class, so protect it against cloning.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Return's the singleton instance and creates a new one if necessary.
     *
     * @return \AppserverIo\Properties\PropertiesUtil The singleton instance
     */
    public static function singleton()
    {

        // query whether or not we've already an instance created
        if (PropertiesUtil::$instance == null) {
            PropertiesUtil::$instance = new PropertiesUtil();
        }

        // return the singleton instance
        return PropertiesUtil::$instance;
    }

    /**
     * Replaces the variables declared in the properties with the
     * properties value itself.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties with the variables/values to replace
     * @param string                                      $pattern    The pattern that declares the variables (in valid sprintf format)
     *
     * @return void
     */
    public function replaceProperties(PropertiesInterface $properties, $pattern = PropertyStreamFilterParams::PATTERN)
    {
        foreach ($properties as $key => $value) {
            $properties->setProperty($key, $this->replacePropertiesInString($properties, $value));
        }
    }

    /**
     * Replaces the variables declared by the passed token with the
     * properties and returns the content.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties with the values to replace
     * @param string                                      $string     The string to replace the variables with the properties
     * @param string                                      $pattern    The pattern that declares the variables (in valid sprintf format)
     *
     * @return string The content of the file with the replaced variables
     */
    public function replacePropertiesInString(PropertiesInterface $properties, $string, $pattern = PropertyStreamFilterParams::PATTERN)
    {

        // open the stream
        $fp = fopen('php://temp', 'r+');

        // write/rewind the content into the string to allow using stream filters
        fputs($fp, $string);
        rewind($fp);

        // replace the properties and close the stream
        $replaced = $this->replacePropertiesInStream($properties, $fp, $pattern);
        fclose($fp);

        // return the string with the properties replaced
        return $replaced;
    }

    /**
     * Replaces the variables declared by the passed token with the
     * properties and returns the content.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties with the values to replace
     * @param resource                                    $fp         The file pointer to replace the variables with the properties
     * @param string                                      $pattern    The pattern that declares the variables (in valid sprintf format)
     *
     * @return string The content of the file with the replaced variables
     */
    public function replacePropertiesInStream(PropertiesInterface $properties, $fp, $pattern = PropertyStreamFilterParams::PATTERN)
    {

        // initialize the params for the stream filter
        $params = new PropertyStreamFilterParams($properties, $pattern);

        // register the filter
        stream_filter_register(PropertyStreamFilter::NAME, 'AppserverIo\Properties\Filter\PropertyStreamFilter');
        stream_filter_append($fp, PropertyStreamFilter::NAME, STREAM_FILTER_READ, $params);

        // replace the properties and close the stream
        return stream_get_contents($fp);
    }
}
