<?php

/**
 * \AppserverIo\Properties\Properties
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

use AppserverIo\Lang\Strng;
use AppserverIo\Lang\NullPointerException;
use AppserverIo\Collections\HashMap;
use Metadata\MergeableClassMetadata;

/**
 * The Properties class represents a persistent set of properties.
 * The Properties can be saved to a stream or loaded from a stream.
 * Each key and its corresponding value in the property list is a string.
 *
 * A property list can contain another property list as its "defaults";
 * this second property list is searched if the property key is not
 * found in the original property list.
 *
 * Because Properties inherits from HashMap, the put method can be
 * applied to a Properties object. Their use is strongly discouraged
 * as they allow the caller to insert entries whose keys or values are
 * not Strings. The setProperty method should be used instead. If the
 * store or save method is called on a "compromised" Properties object
 * that contains a non-String key or value, the call will fail.
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class Properties extends HashMap implements PropertiesInterface
{

    /**
     * This member is TRUE if the sections should be parsed, else FALSE
     *
     * @var boolean
     */
    protected $sections = false;

    /**
     * The default constructor.
     *
     * @param \AppserverIo\Properties\Properties $defaults The properties we want want to use for initialization
     */
    public function __construct(Properties $defaults = null)
    {
        // check if properties are passed
        if ($defaults != null) {
            // if yes set them
            parent::__construct($defaults->toArray());
        } else {
            parent::__construct();
        }
    }

    /**
     * Factory method.
     *
     * @param \AppserverIo\Properties\Properties $defaults Default properties to initialize the new ones with
     *
     * @return \AppserverIo\Properties\Properties The initialized properties
     */
    public static function create(Properties $defaults = null)
    {
        return new Properties($defaults);
    }

    /**
     * Reads a property list (key and element pairs) from the passed file.
     *
     * @param string  $file        The path and the name of the file to load the properties from
     * @param boolean $sections    Has to be TRUE to parse the sections
     * @param integer $scannerMode Can either be INI_SCANNER_NORMAL (default) or INI_SCANNER_RAW, if INI_SCANNER_RAW is supplied, then option values will not be parsed.
     *
     * @return \AppserverIo\Properties\Properties The initialized properties
     * @throws \AppserverIo\Properties\PropertyFileParseException Is thrown if an error occurs while parsing the property file
     * @throws \AppserverIo\Properties\PropertyFileNotFoundException Is thrown if the property file passed as parameter does not exist in the include path
     * @link http://php.net/parse_ini_string
     */
    public function load($file, $sections = false, $scannerMode = INI_SCANNER_RAW)
    {
        // try to load the file content
        $content = @file_get_contents($file, FILE_USE_INCLUDE_PATH);
        // check if file has successfully been loaded
        if (! $content) {
            // throw an exception if the file can not be found in the include path
            throw new PropertyFileNotFoundException(sprintf('File %s not found in include path', $file));
        }
        // parse the file content
        $properties = parse_ini_string($content, $this->sections = $sections, $scannerMode);
        // check if property file was parsed successfully
        if ($properties == false) {
            // throw an exception if an error occurs
            throw new PropertyFileParseException(sprintf('File %s can not be parsed as property file', $file));
        }
        // set the found values
        $this->items = $properties;
        // return the initialized properties
        return $this;
    }

    /**
     * Stores the properties in the property file. This method is NOT using the include path for storing the file.
     *
     * @param string $file The path and the name of the file to store the properties to
     *
     * @return void
     *
     * @throws \AppserverIo\Properties\PropertyFileStoreException Is thrown if the file could not be written
     * @todo Actually only properties without sections will be stored, if a section is specified, then it will be ignored
     */
    public function store($file)
    {
        // create a new file or replace the old one if it exists
        if (($handle = @fopen($file, "w+")) === false) {
            throw new PropertyFileStoreException(sprintf('Can\'t open property file %s for writing', $file));
        }
        // store the property in the file
        foreach ($this->items as $name => $value) {
            $written = @fwrite($handle, $name . " = " . addslashes($value) . PHP_EOL);
            if ($written === false) {
                throw new PropertyFileStoreException(sprintf('Can\'t attach property with name %s to property file %s', $name, $file));
            }
        }
        // saves and closes the file and returns TRUE if the file was written successfully
        if (!@fclose($handle)) {
            throw new PropertyFileStoreException(sprintf('Error while closing and writing property file %s', $file));
        }
    }

    /**
     * Searches for the property with the specified key in this property list.
     *
     * @param string $key     Holds the key of the value to return
     * @param string $section Holds a string with the section name to return the key for (only matters if sections is set to TRUE)
     *
     * @return string Holds the value of the passed key
     * @throws \AppserverIo\Lang\NullPointerException Is thrown if the passed key, or, if sections are TRUE, the passed section is NULL
     */
    public function getProperty($key, $section = null)
    {
        // initialize the property value
        $property = null;
        // check if the sections are included
        if ($this->sections) {
            // if the passed section OR the passed key is NULL throw an exception
            if ($section == null) {
                throw new NullPointerException('Passed section is null');
            }
            if ($key == null) {
                throw new NullPointerException('Passed key is null');
            }
            // if the section exists ...
            if ($this->exists($section)) {
                // get all entries of the section
                $entries = new HashMap($this->get($section));
                if ($entries->exists($key)) {
                    // if yes set it
                    $property = $entries->get($key);
                }
            }
        } else {
            // if the passed key is NULL throw an exception
            if ($key == null) {
                throw new NullPointerException('Passed key is null');
            }
            // check if the property exists in the internal list
            if ($this->exists($key)) {
                // if yes set it
                $property = $this->get($key);
            }
        }
        // return the property or null
        return $property;
    }

    /**
     * Calls the HashMap method add.
     *
     * @param string $key     Holds the key of the value to return
     * @param mixed  $value   Holds the value to add to the properties
     * @param string $section Holds a string with the section name to return the key for (only matters if sections is set to TRUE)
     *
     * @return void
     * @throws \AppserverIo\Lang\NullPointerException Is thrown if the passed key, or, if sections are TRUE, the passed section is NULL
     */
    public function setProperty($key, $value, $section = null)
    {
        // check if the sections are included
        if ($this->sections) {
            // if the passed section OR the passed key is NULL throw an exception
            if ($section == null) {
                throw new NullPointerException('Passed section is null');
            }
            if ($key == null) {
                throw new NullPointerException('Passed key is null');
            }
            // if the section exists ...
            if ($this->exists($section)) {
                // get all entries of the section
                $entries = new HashMap($this->get($section));
                $entries->add($key, $value);
            }
        } else {
            // if the passed key is NULL throw an exception
            if ($key == null) {
                throw new NullPointerException('Passed key is null');
            }
            // add the value with the passed
            $this->add($key, $value);
        }
    }

    /**
     * Returns all properties with their keys as a string.
     *
     * @return string String with all key -> properties pairs
     */
    public function __toString()
    {
        // initialize the return value
        $return = "";
        // iterate over all items and concatenate the values to
        // the return string
        foreach ($this->items as $key => $value) {
            // if sections are set to true there can be subarrays
            // with key/value pairs
            if (is_array($value)) {
                // set the section and add the key/value pairs to the section
                $return .= "[" . $key . "]";
                foreach ($value as $sectionKey => $sectionValue) {
                    $return .= $sectionKey . "=" . $sectionValue . PHP_EOL;
                }
            }
            // add the key/value pair
            $return .= $key . "=" . $value . PHP_EOL;
        }
        // return the string
        return $return;
    }

    /**
     * Returns all properties with their keys as a String.
     *
     * @return \AppserverIo\Lang\String String with all key -> properties pairs
     */
    public function toString()
    {
        return new Strng($this->__toString());
    }

    /**
     * Merges the passed properties into the actual instance. If override
     * flag is set to TRUE, existing properties will be overwritten.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties to merge
     * @param boolean                                     $override   TRUE if existing properties have to be overwritten, else FALSE
     *
     * @return void
     */
    public function mergeProperties(PropertiesInterface $properties, $override = false)
    {
        // iterate over the keys of the passed properties and add thm, or replace existing ones
        foreach ($properties as $key => $value) {
            if ($this->exists($key) === false || ($this->exists($key) === true && $override === true)) {
                $this->setProperty($key, $value);
            }
        }
    }

    /**
     * Returns all key values as an array.
     *
     * @return array The keys as array values
     */
    public function getKeys()
    {
        // check if the property file is sectioned
        if ($this->sections) {
            // initialize the array for the keys
            $keys = array();
            // iterate over the sections and merge all sectioned keys
            foreach ($this->items as $item) {
                $keys = array_merge($keys, array_keys($item));
            }
            // return the keys
            return $keys;
        } else {
            return array_keys($this->items);
        }
    }
}
