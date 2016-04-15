<?php

/**
 * \AppserverIo\Properties\PropertiesUtilTest
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

/**
 * This is the test for the PropertiesUtil class.
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class PropertiesUtilTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test's the replaceProperties() method on a properties object containing a variable.
     *
     * @return void
     */
    public function testReplaceProperties()
    {

        // initialize the properties
        $properties = new Properties();
        $properties->setProperty('foo', '${bar}');
        $properties->setProperty('bar', 'foobar');

        // load the utilities instance and replace the properties
        PropertiesUtil::singleton()->replaceProperties($properties);

        // assert that the variable has been replaced
        $this->assertSame('foobar', $properties->getProperty('foo'));
    }

    /**
     * Test's the merge() method with two properties instances containing the same key.
     *
     * @return void
     */
    public function testMergeWithSameKeyNotOverride()
    {

        // initialize the properties
        $properties = new Properties();
        $properties->setProperty('foo', '${bar}');
        $properties->setProperty('bar', 'foobar');

        // initialize the properties to be merged
        $propertiesToMerge = new Properties();
        $propertiesToMerge->setProperty('foo', 'bar');
        $propertiesToMerge->setProperty('foobar', 'something');

        // merge the properties
        $properties->merge($propertiesToMerge);

        // assert that the results are as expected
        $this->assertSame('${bar}', $properties->getProperty('foo'));
        $this->assertSame('foobar', $properties->getProperty('bar'));
        $this->assertSame('something', $properties->getProperty('foobar'));
    }

    /**
     * Test's the merge() method with two properties instances containing the same key
     * and the override flag has been passed.
     *
     * @return void
     */
    public function testMergeWithSameKeyAndOverride()
    {

        // initialize the properties
        $properties = new Properties();
        $properties->setProperty('foo', '${bar}');


        // initialize the properties to be merged
        $propertiesToMerge = new Properties();
        $propertiesToMerge->setProperty('foo', 'bar');

        // merge the properties
        $properties->merge($propertiesToMerge, true);

        // assert that the results are as expected
        $this->assertSame('bar', $properties->getProperty('foo'));
    }

    /**
     * This test tries to replace the variables in the passed
     * string with the values found in a properties file.
     *
     * @return void
     */
    public function testReplacePropertiesInString()
    {

        // initialize the mock properties
        $mockProperties = $this->getMockBuilder($interface = 'AppserverIo\Properties\PropertiesInterface')
            ->setMethods(get_class_methods($interface))
            ->getMock();
        $mockProperties->expects($this->once())
            ->method('getKeys')
            ->willReturn(array('foo'));
        $mockProperties->expects($this->once())
            ->method('getProperty')
            ->with('foo')
            ->willReturn('bar');

        // load the utilities instance
        $util = PropertiesUtil::singleton();

        // query whether or not the replacement works as expected
        $this->assertEquals(
            'This is a bar test!',
            $util->replacePropertiesInString($mockProperties, 'This is a ${foo} test!')
        );
    }
}