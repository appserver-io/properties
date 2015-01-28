<?php

/**
 * \AppserverIo\Properties\PropertiesTest
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
 * This is the test for the Properties class.
 *
 * @author    Tim Wagner <tw@techdivision.com>
 * @copyright 2015 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://github.com/appserver-io/properties
 * @link      http://www.appserver.io
 */
class PropertiesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * This test tries to load a not existent property file
     * and expects an exception therefore.
     *
     * @return void
     * @expectedException \AppserverIo\Properties\PropertyFileNotFoundException
     */
    public function testLoadWithPropertyFileNotFoundException()
    {
        // initialize and load a not existent property file
        $properties = Properties::create();
        $properties->load(
            'Invalid/Path/To/File'
        );
    }

    /**
     * This test tries to load an invalid property file
     * and expects an exception therefore.
     *
     * @return void
     * @expectedException \AppserverIo\Properties\PropertyFileParseException
     */
    public function testLoadWithPropertyFileParseException()
    {
        // initialize and load a not existent property file
        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/invalid.properties'
        );
    }

    /**
     * This test tries to load a property file
     * without sections.
     *
     * @return void
     */
    public function testLoadWithoutSections()
    {
        // initialize and load a simple property file
        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/test.no.sections.properties'
        );
        // check the values
        $this->assertEquals(
            'Foo test',
            $properties->getProperty('property.key.01')
        );
        $this->assertEquals(
            'Bar test',
            $properties->getProperty('property.key.02')
        );
    }

    /**
     * This test tries to load a property file
     * without sections.
     *
     * @return void
     */
    public function testLoadWithSections()
    {
        // initialize and load a sectioned property file
        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/test.with.sections.properties',
            true
        );
        // check the values
        $this->assertEquals(
            'Foo test',
            $properties->getProperty('property.key.01', 'foo')
        );
        $this->assertEquals(
            'Bar test',
            $properties->getProperty('property.key.02', 'foo')
        );
        // check the values
        $this->assertEquals(
            'Test foo',
            $properties->getProperty('property.key.03', 'bar')
        );
        $this->assertEquals(
            'Test bar',
            $properties->getProperty('property.key.04', 'bar')
        );
    }

    /**
     * This test tries to store a property file
     * without sections.
     *
     * @return void
     */
    public function testStoreWithoutSections()
    {
        // create a new property file
        $created = Properties::create();
        // set some properties
        $created->setProperty('property.key.01', 'Foo test');
        $created->setProperty('property.key.02', 'Bar test');
        // store the property to a file
        $created->store($toStore = tempnam(sys_get_temp_dir(), 'stored.test.properties'));
        // initialize and load the stored property file
        $properties = Properties::create();
        $properties->load($toStore);
        // check the values
        $this->assertEquals(
            'Foo test',
            $properties->getProperty('property.key.01')
        );
        $this->assertEquals(
            'Bar test',
            $properties->getProperty('property.key.02')
        );
    }

    /**
     * This test tries to store a property file to an
     * invalid path and expects an exception therefore.
     *
     * @return void
     * @expectedException \AppserverIo\Properties\PropertyFileStoreException
     */
    public function testStoreWithPropertyFileStoreException()
    {
        // create a new property file
        $created = Properties::create();
        // set some properties
        $created->setProperty('property.key.01', 'Foo test');
        // try store the property file
        $created->store('/Invalid/Path/In/FileSystem');
    }

    /**
     * This test tries to load the keys of property file
     * without sections.
     *
     * @return void
     */
    public function testGetKeysWithoutSections()
    {
        // initialize and load the stored property file
        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/test.no.sections.properties'
        );
        // load the keys
        $keys = $properties->getKeys();
        // check the expected keys
        $this->assertTrue(in_array('property.key.01', $keys));
        $this->assertTrue(in_array('property.key.02', $keys));
    }

    /**
     * This test tries to load the keys of sectioned
     * property file.
     *
     * @return void
     */
    public function testGetKeysWithSections()
    {
        // initialize and load the stored property file
        $properties = Properties::create();
        $properties->load(
            __DIR__ . '/test.with.sections.properties',
            true
        );
        // load the keys
        $keys = $properties->getKeys();
        // check the expected keys
        $this->assertTrue(in_array('property.key.01', $keys));
        $this->assertTrue(in_array('property.key.02', $keys));
        $this->assertTrue(in_array('property.key.03', $keys));
        $this->assertTrue(in_array('property.key.04', $keys));
    }
}