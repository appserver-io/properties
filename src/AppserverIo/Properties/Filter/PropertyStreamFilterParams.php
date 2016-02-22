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

use AppserverIo\Properties\PropertiesInterface;

/**
 * The params to initialize the property stream filter.
 *
 * @author    Tim Wagner <tw@appserver.io>
 * @author    Bernhard Wick <bw@appserver.io>
 * @copyright 2015 TechDivision GmbH <info@appserver.io>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class PropertyStreamFilterParams extends \php_user_filter
{

    /**
     * The default pattern the variables are declared with.
     *
     * @var string
     */
    const PATTERN = '${%s}';

    /**
     * The pattern that declares the variables to be replaced.
     *
     * @var string
     */
    protected $pattern;

    /**
     * The properties used for replacing the variables.
     *
     * @var \AppserverIo\Properties\PropertiesInterface
     */
    protected $properties;

    /**
     * Initializes the params with the passed values.
     *
     * @param \AppserverIo\Properties\PropertiesInterface $properties The properties used for replacement
     * @param string                                      $pattern    The pattern that declares the variables to be replaced
     */
    public function __construct(PropertiesInterface $properties, $pattern = PropertyStreamFilterParams::PATTERN)
    {
        $this->properties = $properties;
        $this->pattern = $pattern;
    }

    /**
     * Return's the properties use for replacement.
     *
     * @return \AppserverIo\Properties\PropertiesInterface The properties
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * Return's the pattern that declares the variables to be replaced.
     *
     * @return string The pattern
     */
    public function getPattern()
    {
        return $this->pattern;
    }
}
