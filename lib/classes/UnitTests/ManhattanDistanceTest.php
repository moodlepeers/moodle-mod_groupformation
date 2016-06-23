<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 25/03/15
 * Time: 08:12
 */

<<<<<<< HEAD
require_once(__DIR__ . "/../Criteria/specific_criterion.php");
require_once(__DIR__ . "/../Evaluator/manhattan_distance.php");

require_once(__DIR__."/../Criteria/criterion_weight.php");
=======
require_once(__DIR__ . "/../criteria/specific_criterion.php");
require_once(__DIR__ . "/../evaluators/manhattan_distance.php");

require_once(__DIR__ . "/../criteria/criterion_weight.php");
>>>>>>> 5b285c7dfbe7497951911c49e83a9821e0b9b8dc


class ManhattanDistanceTest extends PHPUnit_Framework_TestCase {

    public $md;
    public $c1, $c2;

    /**
     * @before
     */
    public function testSettingUp() {
        /**
         * wichtig! erstellen neuer Criterions führt auch Eintrag in statische lib_groupal_criterion_weight-Liste
         */
        lib_groupal_criterion_weight::init(new lib_groupal_hash_map());

        $this->md = new lib_groupal_manhattan_distance();
        $this->c1 = new lib_groupal_specific_criterion("c1", array(3, 6), 0.2, 0.5, true, 3);
        $this->c2 = new lib_groupal_specific_criterion("c2", array(1.5, 3), 0.2, 0.5, true, 3);

    }

    public function testGetDistance() {
        $this->assertEquals(9, $this->md->getDistance($this->c1, $this->c2));
    }

    public function testNormalizeDistance() {
        $this->assertEquals(4.5, $this->md->normalizeDistance($this->c1, $this->c2));
    }
    // TODO need better tests

}
