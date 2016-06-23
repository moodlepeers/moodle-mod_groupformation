<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 23/03/15
 * Time: 12:59
 */

<<<<<<< HEAD
require_once(__DIR__ . "/../CustomCollections/hash_map.php");
=======
require_once(__DIR__ . "/../util/hash_map.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc

class HashMapTest extends PHPUnit_Framework_TestCase {

    protected $hashMap;

    /**
     * @before
     */
    public function testSettingUpEnvironment() {
        $this->hashMap = new lib_groupal_hash_map();
    }

    /**
     * testing add
     */
    public function testAdd() {
        $this->hashMap->add("test", 5);
        $this->assertEquals(5, $this->hashMap->getValue("test"));
        return $this->hashMap;
    }

    /**
     *
     * test remove
     */
    public function testRemove() {
        $this->assertFalse($this->hashMap->remove("test"));
    }

    /**
     * remove after testAdd()-Function
     * @depends testAdd
     */
    public function testRemoveAfterTestAdd($hashMap) {
        $this->assertTrue($hashMap->remove("test"));
    }

    /**
     * after 5 adds count assoc. array has to be 5
     */
    public function testSomeMoreAdds() {
        $this->hashMap->add("test1", 1);
        $this->hashMap->add("test2", 2);
        $this->hashMap->add("test3", 3);
        $this->hashMap->add("test4", 4);
        $this->hashMap->add("test5", 5);

        $this->assertTrue(count($this->hashMap->get()) == 5);
    }

    /**
     * changing Value from 5 to 10
     */
    public function testSetValue() {
        $this->hashMap->add("test", 5);
        $this->hashMap->set("test", 10);
        $this->assertEquals(10, $this->hashMap->getValue("test"));
    }

    /**
     * changing value of key that does not exist
     */
    public function testSetValueError() {
        $this->assertFalse($this->hashMap->set("t", 4));
    }


    public function testContainsKey() {
        $this->hashMap->add("test1", 1);
        $this->hashMap->add("test2", 2);
        $this->hashMap->add("test3", 3);
        $this->hashMap->add("test4", 4);
        $this->hashMap->add("test5", 5);

        $this->assertTrue($this->hashMap->containsKey("test4"));
        $this->assertFalse($this->hashMap->containsKey("test6"));
    }
}
