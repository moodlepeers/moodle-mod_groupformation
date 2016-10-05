<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 05/05/15
 * Time: 10:39
 */

require_once(__DIR__ . "/../cohort.php");
require_once(__DIR__ . "/../group.php");

class CohortTest extends PHPUnit_Framework_TestCase {

    public $cohortInstance;
    public $group1, $group2, $group3;

    /**
     * @before
     */
    public function testSettingUpEnvironment() {
<<<<<<< HEAD
        // Obergrenze/Anzahl der Gruppen pro Cohort = 2
        // init: Cohort mit $group1, eine weitere leere Gruppe wird automatisch erstellt
=======
        // Obergrenze/Anzahl der Gruppen pro Cohort = 2.
        // init: Cohort mit $group1, eine weitere leere Gruppe wird automatisch erstellt.
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc
        $this->group1 = new lib_groupal_group();
        $groupList = array($this->group1);
        $this->cohortInstance = new lib_groupal_cohort(5, $groupList);
    }

    /**
     *
     */
    public function testInitState() {
        $this->assertEquals(5, count($this->cohortInstance->groups));
    }

}
