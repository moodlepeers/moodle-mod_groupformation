<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die ('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/user_manager.php');
require_once($CFG->dirroot . '/mod/groupformation/classes/util/util.php');
require_once($CFG->dirroot . '/mod/groupformation/lib/classes/criteria/topic_criterion.php');

class mod_groupformation_criterion_calculator {
    private $zlookuptable = array('-3.0' => 0.0013, '-3' => 0.0013, '-2.99' => 0.0014, '-2.98' => 0.0014, '-2.97' => 0.0015, '-2.96' => 0.0015, '-2.95' => 0.0016, '-2.94' => 0.0016, '-2.93' => 0.0017, '-2.92' => 0.0018, '-2.91' => 0.0018, '-2.9' => 0.0019, '-2.89' => 0.0019, '-2.88' => 0.0020, '-2.87' => 0.0021, '-2.86' => 0.0021, '-2.85' => 0.0022, '-2.84' => 0.0023, '-2.83' => 0.0023, '-2.82' => 0.0024, '-2.81' => 0.0025, '-2.8' => 0.0026, '-2.79' => 0.0026, '-2.78' => 0.0027, '-2.77' => 0.0028, '-2.76' => 0.0029, '-2.75' => 0.0030, '-2.74' => 0.0031, '-2.73' => 0.0032, '-2.72' => 0.0033, '-2.71' => 0.0034, '-2.7' => 0.0035, '-2.69' => 0.0036, '-2.68' => 0.0037, '-2.67' => 0.0038, '-2.66' => 0.0039, '-2.65' => 0.0040, '-2.64' => 0.0041, '-2.63' => 0.0043, '-2.62' => 0.0044, '-2.61' => 0.0045, '-2.6' => 0.0047, '-2.59' => 0.0048, '-2.58' => 0.0049, '-2.57' => 0.0051, '-2.56' => 0.0052, '-2.55' => 0.0054, '-2.54' => 0.0055, '-2.53' => 0.0057, '-2.52' => 0.0059, '-2.51' => 0.0060, '-2.5' => 0.0062, '-2.49' => 0.0064, '-2.48' => 0.0066, '-2.47' => 0.0068, '-2.46' => 0.0069, '-2.45' => 0.0071, '-2.44' => 0.0073, '-2.43' => 0.0075, '-2.42' => 0.0078, '-2.41' => 0.0080, '-2.4' => 0.0082, '-2.39' => 0.0084, '-2.38' => 0.0087, '-2.37' => 0.0089, '-2.36' => 0.0091, '-2.35' => 0.0094, '-2.34' => 0.0096, '-2.33' => 0.0099, '-2.32' => 0.0102, '-2.31' => 0.0104, '-2.3' => 0.0107, '-2.29' => 0.0110, '-2.28' => 0.0113, '-2.27' => 0.0116, '-2.26' => 0.0119, '-2.25' => 0.0122, '-2.24' => 0.0125, '-2.23' => 0.0129, '-2.22' => 0.0132, '-2.21' => 0.0136, '-2.2' => 0.0139, '-2.19' => 0.0143, '-2.18' => 0.0146, '-2.17' => 0.0150, '-2.16' => 0.0154, '-2.15' => 0.0158, '-2.14' => 0.0162, '-2.13' => 0.0166, '-2.12' => 0.0170, '-2.11' => 0.0174, '-2.1' => 0.0179, '-2.09' => 0.0183, '-2.08' => 0.0188, '-2.07' => 0.0192, '-2.06' => 0.0197, '-2.05' => 0.0202, '-2.04' => 0.0207, '-2.03' => 0.0212, '-2.02' => 0.0217, '-2.01' => 0.0222, '-2.0' => 0.0228, '-2' => 0.0228, '-1.99' => 0.0233, '-1.98' => 0.0239, '-1.97' => 0.0244, '-1.96' => 0.0250, '-1.95' => 0.0256, '-1.94' => 0.0262, '-1.93' => 0.0268, '-1.92' => 0.0275, '-1.91' => 0.0281, '-1.9' => 0.0287, '-1.89' => 0.0294, '-1.88' => 0.0301, '-1.87' => 0.0307, '-1.86' => 0.0314, '-1.85' => 0.0322, '-1.84' => 0.0329, '-1.83' => 0.0336, '-1.82' => 0.0344, '-1.81' => 0.0351, '-1.8' => 0.0359, '-1.79' => 0.0367, '-1.78' => 0.0375, '-1.77' => 0.0384, '-1.76' => 0.0392, '-1.75' => 0.0401, '-1.74' => 0.0409, '-1.73' => 0.0418, '-1.72' => 0.0427, '-1.71' => 0.0436, '-1.7' => 0.0446, '-1.69' => 0.0455, '-1.68' => 0.0465, '-1.67' => 0.0475, '-1.66' => 0.0485, '-1.65' => 0.0495, '-1.64' => 0.0505, '-1.63' => 0.0516, '-1.62' => 0.0526, '-1.61' => 0.0537, '-1.6' => 0.0548, '-1.59' => 0.0559, '-1.58' => 0.0571, '-1.57' => 0.0582, '-1.56' => 0.0594, '-1.55' => 0.0606, '-1.54' => 0.0618, '-1.53' => 0.0630, '-1.52' => 0.0643, '-1.51' => 0.0655, '-1.5' => 0.0668, '-1.49' => 0.0681, '-1.48' => 0.0694, '-1.47' => 0.0708, '-1.46' => 0.0721, '-1.45' => 0.0735, '-1.44' => 0.0749, '-1.43' => 0.0764, '-1.42' => 0.0778, '-1.41' => 0.0793, '-1.4' => 0.0808, '-1.39' => 0.0823, '-1.38' => 0.0838, '-1.37' => 0.0853, '-1.36' => 0.0869, '-1.35' => 0.0885, '-1.34' => 0.0901, '-1.33' => 0.0918, '-1.32' => 0.0934, '-1.31' => 0.0951, '-1.3' => 0.0968, '-1.29' => 0.0985, '-1.28' => 0.1003, '-1.27' => 0.1020, '-1.26' => 0.1038, '-1.25' => 0.1056, '-1.24' => 0.1075, '-1.23' => 0.1093, '-1.22' => 0.1112, '-1.21' => 0.1131, '-1.2' => 0.1151, '-1.19' => 0.1170, '-1.18' => 0.1190, '-1.17' => 0.1210, '-1.16' => 0.1230, '-1.15' => 0.1251, '-1.14' => 0.1271, '-1.13' => 0.1292, '-1.12' => 0.1314, '-1.11' => 0.1335, '-1.1' => 0.1357, '-1.09' => 0.1379, '-1.08' => 0.1401, '-1.07' => 0.1423, '-1.06' => 0.1446, '-1.05' => 0.1469, '-1.04' => 0.1492, '-1.03' => 0.1515, '-1.02' => 0.1539, '-1.01' => 0.1562, '-1.0' => 0.1587, '-1' => 0.1587, '-0.99' => 0.1611, '-0.98' => 0.1635, '-0.97' => 0.1660, '-0.96' => 0.1685, '-0.95' => 0.1711, '-0.94' => 0.1736, '-0.93' => 0.1762, '-0.92' => 0.1788, '-0.91' => 0.1814, '-0.9' => 0.1841, '-0.89' => 0.1867, '-0.88' => 0.1894, '-0.87' => 0.1922, '-0.86' => 0.1949, '-0.85' => 0.1977, '-0.84' => 0.2005, '-0.83' => 0.2033, '-0.82' => 0.2061, '-0.81' => 0.2090, '-0.8' => 0.2119, '-0.79' => 0.2148, '-0.78' => 0.2177, '-0.77' => 0.2206, '-0.76' => 0.2236, '-0.75' => 0.2266, '-0.74' => 0.2296, '-0.73' => 0.2327, '-0.72' => 0.2358, '-0.71' => 0.2389, '-0.7' => 0.2420, '-0.69' => 0.2451, '-0.68' => 0.2483, '-0.67' => 0.2514, '-0.66' => 0.2546, '-0.65' => 0.2578, '-0.64' => 0.2611, '-0.63' => 0.2643, '-0.62' => 0.2676, '-0.61' => 0.2709, '-0.6' => 0.2749, '-0.59' => 0.2776, '-0.58' => 0.2810, '-0.57' => 0.2843, '-0.56' => 0.2877, '-0.55' => 0.2912, '-0.54' => 0.2946, '-0.53' => 0.2981, '-0.52' => 0.3015, '-0.51' => 0.3050, '-0.5' => 0.3085, '-0.49' => 0.3121, '-0.48' => 0.3156, '-0.47' => 0.3192, '-0.46' => 0.3228, '-0.45' => 0.3264, '-0.44' => 0.33, '-0.43' => 0.3336, '-0.42' => 0.3372, '-0.41' => 0.3409, '-0.4' => 0.3446, '-0.39' => 0.3483, '-0.38' => 0.3520, '-0.37' => 0.3557, '-0.36' => 0.3594, '-0.35' => 0.3632, '-0.34' => 0.3669, '-0.33' => 0.3707, '-0.32' => 0.3745, '-0.31' => 0.3783, '-0.3' => 0.3821, '-0.29' => 0.3859, '-0.28' => 0.3897, '-0.27' => 0.3936, '-0.26' => 0.3974, '-0.25' => 0.4013, '-0.24' => 0.4052, '-0.23' => 0.4090, '-0.22' => 0.4129, '-0.21' => 0.4168, '-0.2' => 0.4207, '-0.19' => 0.4247, '-0.18' => 0.4286, '-0.17' => 0.4325, '-0.16' => 0.4364, '-0.15' => 0.4404, '-0.14' => 0.4443, '-0.13' => 0.4483, '-0.12' => 0.4522, '-0.11' => 0.4562, '-0.1' => 0.4602, '-0.09' => 0.4641, '-0.08' => 0.4681, '-0.07' => 0.4721, '-0.06' => 0.4761, '-0.05' => 0.4801, '-0.04' => 0.4840, '-0.03' => 0.4880, '-0.02' => 0.4920, '-0.01' => 0.4960, '-0' => 0.50, '0' => 0.50, '0.0' => 0.50, '0.01' => 0.5040, '0.02' => 0.5080, '0.03' => 0.5120, '0.04' => 0.5160, '0.05' => 0.5199, '0.06' => 0.5239, '0.07' => 0.5279, '0.08' => 0.5319, '0.09' => 0.5359, '0.1' => 0.5398, '0.11' => 0.5438, '0.12' => 0.5478, '0.13' => 0.5517, '0.14' => 0.5557, '0.15' => 0.5596, '0.16' => 0.5636, '0.17' => 0.5675, '0.18' => 0.5714, '0.19' => 0.5753, '0.2' => 0.5793, '0.21' => 0.5832, '0.22' => 0.5871, '0.23' => 0.5910, '0.24' => 0.5948, '0.25' => 0.5987, '0.26' => 0.6026, '0.27' => 0.6064, '0.28' => 0.6103, '0.29' => 0.6141, '0.3' => 0.6179, '0.31' => 0.6217, '0.32' => 0.6255, '0.33' => 0.6293, '0.34' => 0.6331, '0.35' => 0.6368, '0.36' => 0.6406, '0.37' => 0.6443, '0.38' => 0.6480, '0.39' => 0.6517, '0.4' => 0.6554, '0.41' => 0.6591, '0.42' => 0.6628, '0.43' => 0.6664, '0.44' => 0.67, '0.45' => 0.6736, '0.46' => 0.6772, '0.47' => 0.6808, '0.48' => 0.6844, '0.49' => 0.6879, '0.5' => 0.6915, '0.51' => 0.6950, '0.52' => 0.6985, '0.53' => 0.7019, '0.54' => 0.7054, '0.55' => 0.7088, '0.56' => 0.7123, '0.57' => 0.7157, '0.58' => 0.7190, '0.59' => 0.7224, '0.6' => 0.7257, '0.61' => 0.7291, '0.62' => 0.7324, '0.63' => 0.7357, '0.64' => 0.7389, '0.65' => 0.7422, '0.66' => 0.7454, '0.67' => 0.7486, '0.68' => 0.7517, '0.69' => 0.7549, '0.7' => 0.7580, '0.71' => 0.7611, '0.72' => 0.7642, '0.73' => 0.7673, '0.74' => 0.7704, '0.75' => 0.7734, '0.76' => 0.7764, '0.77' => 0.7794, '0.78' => 0.7823, '0.79' => 0.7852, '0.8' => 0.7881, '0.81' => 0.7910, '0.82' => 0.7939, '0.83' => 0.7967, '0.84' => 0.7995, '0.85' => 0.8023, '0.86' => 0.8051, '0.87' => 0.8078, '0.88' => 0.8106, '0.89' => 0.8133, '0.9' => 0.8159, '0.91' => 0.8186, '0.92' => 0.8212, '0.93' => 0.8238, '0.94' => 0.8264, '0.95' => 0.8289, '0.96' => 0.8315, '0.97' => 0.8340, '0.98' => 0.8365, '0.99' => 0.8389, '1.0' => 0.8413, '1' => 0.8413, '1.01' => 0.8438, '1.02' => 0.8461, '1.03' => 0.8485, '1.04' => 0.8508, '1.05' => 0.8531, '1.06' => 0.8554, '1.07' => 0.8577, '1.08' => 0.8599, '1.09' => 0.8621, '1.1' => 0.8643, '1.11' => 0.8665, '1.12' => 0.8686, '1.13' => 0.8708, '1.14' => 0.8729, '1.15' => 0.8749, '1.16' => 0.8770, '1.17' => 0.8790, '1.18' => 0.8810, '1.19' => 0.8830, '1.2' => 0.8849, '1.21' => 0.8869, '1.22' => 0.8888, '1.23' => 0.8907, '1.24' => 0.8925, '1.25' => 0.8944, '1.26' => 0.8962, '1.27' => 0.8980, '1.28' => 0.8997, '1.29' => 0.9015, '1.3' => 0.9032, '1.31' => 0.9049, '1.32' => 0.9066, '1.33' => 0.9082, '1.34' => 0.9099, '1.35' => 0.9115, '1.36' => 0.9131, '1.37' => 0.9147, '1.38' => 0.9162, '1.39' => 0.9177, '1.4' => 0.9192, '1.41' => 0.9207, '1.42' => 0.9222, '1.43' => 0.9236, '1.44' => 0.9251, '1.45' => 0.9265, '1.46' => 0.9279, '1.47' => 0.9292, '1.48' => 0.9306, '1.49' => 0.9319, '1.5' => 0.9332, '1.51' => 0.9345, '1.52' => 0.9357, '1.53' => 0.9370, '1.54' => 0.9382, '1.55' => 0.9394, '1.56' => 0.9406, '1.57' => 0.9418, '1.58' => 0.9429, '1.59' => 0.9441, '1.6' => 0.9452, '1.61' => 0.9463, '1.62' => 0.9474, '1.63' => 0.9484, '1.64' => 0.9495, '1.65' => 0.9505, '1.66' => 0.9515, '1.67' => 0.9525, '1.68' => 0.9535, '1.69' => 0.9545, '1.7' => 0.9554, '1.71' => 0.9564, '1.72' => 0.9573, '1.73' => 0.9582, '1.74' => 0.9591, '1.75' => 0.9599, '1.76' => 0.9608, '1.77' => 0.9616, '1.78' => 0.9625, '1.79' => 0.9633, '1.8' => 0.9641, '1.81' => 0.9649, '1.82' => 0.9656, '1.83' => 0.9664, '1.84' => 0.9671, '1.85' => 0.9678, '1.86' => 0.9686, '1.87' => 0.9693, '1.88' => 0.9699, '1.89' => 0.9706, '1.9' => 0.9713, '1.91' => 0.9719, '1.92' => 0.9726, '1.93' => 0.9732, '1.94' => 0.9738, '1.95' => 0.9744, '1.96' => 0.9750, '1.97' => 0.9756, '1.98' => 0.9761, '1.99' => 0.9767, '2.0' => 0.9772, '2' => 0.9772, '2.01' => 0.9778, '2.02' => 0.9783, '2.03' => 0.9788, '2.04' => 0.9793, '2.05' => 0.9798, '2.06' => 0.9803, '2.07' => 0.9808, '2.08' => 0.9812, '2.09' => 0.9817, '2.1' => 0.9821, '2.11' => 0.9826, '2.12' => 0.9830, '2.13' => 0.9834, '2.14' => 0.9838, '2.15' => 0.9842, '2.16' => 0.9846, '2.17' => 0.9850, '2.18' => 0.9854, '2.19' => 0.9857, '2.2' => 0.9861, '2.21' => 0.9864, '2.22' => 0.9868, '2.23' => 0.9871, '2.24' => 0.9875, '2.25' => 0.9878, '2.26' => 0.9881, '2.27' => 0.9884, '2.28' => 0.9887, '2.29' => 0.9890, '2.3' => 0.9893, '2.31' => 0.9896, '2.32' => 0.9898, '2.33' => 0.9901, '2.34' => 0.9904, '2.35' => 0.9906, '2.36' => 0.9909, '2.37' => 0.9911, '2.38' => 0.9913, '2.39' => 0.9916, '2.4' => 0.9918, '2.41' => 0.9920, '2.42' => 0.9922, '2.43' => 0.9925, '2.44' => 0.9927, '2.45' => 0.9929, '2.46' => 0.9931, '2.47' => 0.9932, '2.48' => 0.9934, '2.49' => 0.9936, '2.5' => 0.9938, '2.51' => 0.9940, '2.52' => 0.9941, '2.53' => 0.9943, '2.54' => 0.9945, '2.55' => 0.9946, '2.56' => 0.9948, '2.57' => 0.9949, '2.58' => 0.9951, '2.59' => 0.9952, '2.6' => 0.9953, '2.61' => 0.9955, '2.62' => 0.9956, '2.63' => 0.9957, '2.64' => 0.9959, '2.65' => 0.9960, '2.66' => 0.9961, '2.67' => 0.9962, '2.68' => 0.9963, '2.69' => 0.9964, '2.7' => 0.9965, '2.71' => 0.9966, '2.72' => 0.9967, '2.73' => 0.9968, '2.74' => 0.9969, '2.75' => 0.9970, '2.76' => 0.9971, '2.77' => 0.9972, '2.78' => 0.9973, '2.79' => 0.9974, '2.8' => 0.9974, '2.81' => 0.9975, '2.82' => 0.9976, '2.83' => 0.9977, '2.84' => 0.9977, '2.85' => 0.9978, '2.86' => 0.9979, '2.87' => 0.9979, '2.88' => 0.9980, '2.89' => 0.9981, '2.9' => 0.9981, '2.91' => 0.9982, '2.92' => 0.9982, '2.93' => 0.9983, '2.94' => 0.9984, '2.95' => 0.9984, '2.96' => 0.9985, '2.97' => 0.9985, '2.98' => 0.9986, '2.99' => 0.9986, '3.0' => 0.9987, '3' => 0.9987);
    private $store;
    private $usermanager;
    private $data;
    private $groupformationid;
    private $scenario;

    /**
     * mod_groupformation_criterion_calculator constructor.
     * @param $groupformationid
     */
    public function __construct($groupformationid) {
        $this->groupformationid = $groupformationid;
        $this->store = new mod_groupformation_storage_manager ($groupformationid);
        $this->usermanager = new mod_groupformation_user_manager ($groupformationid);
        $this->data = new mod_groupformation_data();

        $this->scenario = $this->store->get_scenario();
    }

    /**
     * Inverts given answer by considering maximum
     *
     * @param number $questionid
     * @param string $category
     * @param number $answer
     * @return number
     */
    private function invert_answer($questionid, $category, $answer) {
        $max = $this->store->get_max_option_of_catalog_question($questionid, $category);

        return $max + 1 - $answer;
    }

    /**
     * Filter criteria specs by erasing useless question ids if not significant enough
     *
     * @param $criteriaspecs
     * @param $users
     * @param bool|false $eval
     * @return array
     */
    public function filter_criteria_specs($criteriaspecs, $users, $eval = false) {
        $filteredspecs = array();
        foreach ($criteriaspecs as $criterion => $spec) {
            $category = $spec['category'];
            $labels = $spec['labels'];
            if (in_array($this->scenario, $spec['scenarios']) && (!$eval || (array_key_exists('evaluation', $spec) && $spec['evaluation']))) {

                $positions = array();

                foreach ($labels as $label => $specs) {
                    if (array_key_exists($this->scenario, $specs['scenarios']) && (!$eval || (array_key_exists('evaluation', $spec) && $spec['evaluation']))) {
                        if (array_key_exists('significant_id_only', $specs) && $specs['significant_id_only']) {
                            $variance = 0;
                            $position = 1;
                            $total = 0;
                            $initialid = null;
                            foreach ($specs['questionids'] as $id) {
                                if (is_null($initialid)) {
                                    $initialid = $id;
                                }
                                // Answers for catalog question in category $criterion.
                                $answers = $this->store->get_answers_to_special_question($category, $id);

                                // Number of options for catalog question.
                                $totaloptions = $this->store->get_max_option_of_catalog_question($id, $category);

                                $dist = array_fill(0, $totaloptions, 0);

                                // Iterates over answers for grade questions.
                                foreach ($answers as $answer) {
                                    // Checks if answer is relevant for this group of users.
                                    if (is_null($users) || in_array($answer->userid, $users)) {

                                        // Increments count for answer option.
                                        $dist [($answer->answer) - 1]++;

                                        // Increments count for total.
                                        if ($id == $initialid) {
                                            $total++;
                                        }
                                    }
                                }

                                // Computes tempexp for later use.
                                $tempexp = 0;
                                $p = 1;
                                foreach ($dist as $d) {
                                    $tempexp = $tempexp + ($p * ($d / $total));
                                    $p++;
                                }

                                // Computes tempvariance to find maximal variance.
                                $tempvariance = 0;
                                $p = 1;
                                foreach ($dist as $d) {
                                    $tempvariance = $tempvariance + ((pow(($p - $tempexp), 2)) * ($d / $total));
                                    $p++;
                                }

                                // Sets position by maximal variance.
                                if ($variance < $tempvariance) {
                                    $variance = $tempvariance;
                                    $position = $id;
                                }

                            }
                            $specs['questionids'] = array($position);
                        }

                        $positions[$label] = $specs;
                    }

                }

                if (count($positions) > 0) {
                    $spec['labels'] = $positions;
                    $filteredspecs[$criterion] = $spec;
                }
            }

        }

        return $filteredspecs;
    }

    /**
     * Filters criterion specs by eval
     *
     * @param $criterion
     * @param $criterionspecs
     * @param null $users
     * @return array
     */
    public function filter_criterion_specs_for_eval($criterion, $criterionspecs, $users = null) {
        $array = array($criterion => $criterionspecs);
        $result = $this->filter_criteria_specs($array, $users, true);
        if (count($result) > 0) {
            return $result[$criterion];
        } else {
            return array();
        }
    }

    /**
     * Computes values for given criterion
     *
     * @param $criterion
     * @param $userid
     * @param null $specs
     * @return array|null
     */
    public function get_values($criterion, $userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification($criterion);
        }

        $labels = $specs['labels'];
        $category = $specs['category'];

        $array = array();

        if (!$this->usermanager->has_answered_everything($userid)) {
            return null;
        }

        foreach ($labels as $key => $spec) {
            $temp = 0;
            $minvalue = 0;
            $maxvalue = 0;

            $questionids = $spec['questionids'];

            if (array_key_exists($this->scenario, $spec['scenarios'])) {
                foreach (array_values($questionids) as $tempquestionid) {
                    $questionid = $tempquestionid;
                    if ($tempquestionid < 0) {
                        $questionid = abs($tempquestionid);
                        if ($this->usermanager->has_answer($userid, $category, $questionid)) {
                            $temp = $temp + $this->invert_answer($questionid, $category,
                                    $this->usermanager->get_single_answer($userid, $category, $questionid));
                        }
                    } else {
                        if ($this->usermanager->has_answer($userid, $category, $questionid)) {
                            $temp = $temp + $this->usermanager->get_single_answer($userid, $category, $questionid);
                        }
                    }
                    $minvalue = $minvalue + 1;
                    $maxvalue = $maxvalue + $this->store->get_max_option_of_catalog_question($questionid, $category);
                }
                $array [$key] = array("values" => array(floatval($temp - $minvalue) / ($maxvalue - $minvalue)));
            }
        }
        return $array;
    }

    /**
     * Returns big5 criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     */
    public function get_big5($userid, $specs = null) {
        return $this->get_values('big5', $userid, $specs);
    }

    /**
     * Returns fam criterion values
     *
     * @param $userid
     * @param $specs
     * @return array
     */
    public function get_fam($userid, $specs = null) {
        return $this->get_values('fam', $userid, $specs);
    }

    /**
     * Returns learning criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return array
     */
    public function get_learning($userid, $specs = null) {
        return $this->get_values('learning', $userid, $specs);
    }

    /**
     * Returns general criterion values
     *
     * @param number $userid
     * @param array $specs
     * @return string
     */
    public function get_general($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification("general");
        }

        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];
        if (!$this->usermanager->has_answered_everything($userid)) {
            return null;
        }
        foreach ($labels as $key => $spec) {

            $qids = $spec['questionids'];

            $value = 0;
            foreach ($qids as $qid) {
                $value += $this->usermanager->get_single_answer($userid, $category, $qid);
            }

            // An array(x,y) with x = ENGLISH and y = GERMAN.
            $values = array(1.0, 0.0);
            if ($value == 1) {
                $values = array(
                    1.0, 0.0);
            } else if ($value == 2) {
                $values = array(
                    0.0, 1.0);
            } else if ($value == 3) {
                $values = array(
                    1.0, 0.5);
            } else if ($value == 4) {
                $values = array(
                    0.5, 1.0);
            }

            $tmp = array();
            $tmp["values"] = $values;
            $array[$key] = $tmp;
        }

        return $array;
    }

    /**
     * Returns knowledge criterion values
     *
     * @param $userid
     * @param null $specs
     * @return array
     */
    public function get_knowledge($userid, $specs = null) {

        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('knowledge');
        }
        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $array = array();
        $category = $specs['category'];


        $answers = $this->usermanager->get_answers($userid, $category);
        $optionscount = $this->store->get_number($category);
        if (count($answers) != $optionscount) {
            return $array;
        }

        // iterate over labels of criterion
        foreach ($labels as $key => $spec) {
            $knowledgevalues = array();

            // max value for answer to knowledge question
            $maxvalue = 100;

            // check whether this label is for this scenario
            if (array_key_exists($scenario, $spec['scenarios'])) {

                // checks whether the values should be one dimension or separate dimensions
                if (array_key_exists('separate_dimensions', $spec) && $spec['separate_dimensions']) {

                    // computes each quotient of answer/maxvalue and adds it as dimension
                    for ($qid = 1; $qid <= $optionscount; $qid++) {
                        $value = floatval($this->usermanager->get_single_answer($userid, $category, $qid));
                        $knowledgevalues [] = $value / $maxvalue;
                    }

                } else {
                    $total = 0;
                    $answers = $this->usermanager->get_answers($userid, $category);

                    // computes sum of all answers
                    foreach ($answers as $answer) {
                        $total = $total + $answer->answer;
                    }

                    // computes average over all answers and quotient of average/maxvalue
                    $temp = floatval($total) / ($optionscount);
                    $knowledgevalues = array(floatval($temp) / $maxvalue);
                }
            }

            $array[$key] = array('values' => $knowledgevalues);

        }
        return $array;
    }

    /**
     * Returns points criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return float
     */
    public function get_points($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('points');
        }

        $scenario = $this->scenario;
        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $maxvalue = $this->store->get_max_points();

        foreach ($labels as $key => $spec) {
            $answer = 0;
            $maxanswer = 0;

            // check whether this label is for this scenario
            if (array_key_exists($scenario, $spec['scenarios'])) {

                // sums up all answers with respect to given questionids
                foreach (array_values($spec['questionids']) as $questionid) {
                    $answer += $this->usermanager->get_single_answer($userid, $category, $questionid);
                    $maxanswer += $maxvalue;
                }

                // computes average
                $answer = floatval($answer / $maxanswer);
                $answers[$key] = array("values" => array($answer));
            }
        }

        return $answers;
    }

    /**
     * Returns grade criterion values
     *
     * @param int $userid
     * @param array $specs
     * @return float
     */
    public function get_grade($userid, $specs = null) {
        if (is_null($specs)) {
            $specs = $this->data->get_criterion_specification('grade');
        }

        $labels = $specs['labels'];
        $answers = array();
        $category = $specs['category'];

        $max = $this->store->get_max_points();
        foreach ($labels as $key => $positions) {
            $answer = 0;
            $maxanswer = 0;
            foreach (array_values($positions['questionids']) as $p) {
                $answer += $this->usermanager->get_single_answer($userid, $category, $p);
                $maxanswer += $max;
            }
            $answer = floatval($answer / $maxanswer);
            $answers[$key] = array("values" => array($answer));
        }

        return $answers;
    }

    /**
     * Returns team criterion values
     *
     * @param $userid
     * @param $specs
     * @return array
     */
    public function get_team($userid, $specs = null) {
        return $this->get_values('team', $userid, $specs);
    }

    /**
     * Returns topic answers as a criterion
     *
     * @param number $userid
     * @return TopicCriterion
     */
    public function get_topic($userid) {
        $choices = $this->usermanager->get_answers($userid, 'topic', 'questionid', 'answer');

        return new lib_groupal_topic_criterion(array_keys($choices));
    }

    /**
     * Computes z score
     *
     * @param $usersvalues
     * @return mixed
     */
    public function compute_z_score($usersvalues) {

        $mean = null;

        foreach ($usersvalues as $userid => $labels) {
            if (is_null($mean)) {
                $mean = $labels;
            } else {
                foreach ($labels as $label => $labelvalues) {
                    $values = $labelvalues['values'];
                    foreach ($values as $k => $value) {
                        $mean[$label]['values'][$k] += $value;
                    }
                }
            }
        }

        $size = count($usersvalues);

        foreach ($mean as $label => $labelvalues) {
            $values = $labelvalues['values'];
            foreach ($values as $k => $value) {
                $mean[$label]['values'][$k] /= $size;
            }
        }

        $variance = null;
        $i = 1;
        foreach ($usersvalues as $userid => $labels) {
            if (is_null($variance)) {
                $variance = $labels;
            }

            foreach ($labels as $label => $labelvalues) {
                $values = $labelvalues['values'];
                foreach ($values as $k => $value) {
                    $meanvalue = $mean[$label]['values'][$k];
                    if ($i == 1) {
                        $variance[$label]['values'][$k] = 0;
                    }
                    $variance[$label]['values'][$k] += pow($value - $meanvalue, 2) / $size;
                }
            }
            $i += 1;
        }

        $stddeviation = $variance;

        foreach ($stddeviation as $label => $labelvalues) {
            $values = $labelvalues['values'];
            foreach ($values as $k => $value) {
                $stddeviation[$label]['values'][$k] = sqrt($stddeviation[$label]['values'][$k]);
            }
        }

        foreach ($usersvalues as $userid => $labels) {
            foreach ($labels as $label => $labelvalues) {
                $values = $labelvalues['values'];
                foreach ($values as $k => $value) {
                    $meanvalue = $mean[$label]['values'][$k];
                    $stdvvalue = $stddeviation[$label]['values'][$k];
                    $xvalue = $labels[$label]['values'][$k];
                    $diff = $xvalue - $meanvalue;
                    if ($diff == 0) {
                        $zscore = 0;
                    } else if ($stdvvalue == 0) {
                        $zscore = 0;
                    } else {
                        $zscore = ($xvalue - $meanvalue) / $stdvvalue;
                    }
                    $labels[$label]['values'][$k] = $this->lookup_z($zscore);
                }
            }
        }

        return $usersvalues;
    }

    /**
     * Lookup z-score
     *
     * @param $z
     * @return float|mixed
     */
    function lookup_z($z) {
        $z = strval(round($z, 2));
        $val = 0.0;
        if (-3.00 <= $z && $z <= 3.00) {
            $val = $this->zlookuptable[$z];
        } else if (-3.00 > $z) {
            $val = 0.0;
        } else if (3.00 < $z) {
            $val = 1.0;
        }

        return $val;
    }

    /**
     * Returns eval data for user
     *
     * @param $userid
     * @param $groupusers
     * @param $courseusers
     * @return array
     */
    public function get_eval($userid, $groupusers, $courseusers) {
        $completedusers = array_keys($this->usermanager->get_completed_by_answer_count('userid', 'userid'));
        $groupandcompleted = array_intersect($completedusers, $groupusers);
        $courseandcompleted = array_intersect($completedusers, $courseusers);

        $vals = array('user');
        if (count($groupandcompleted) > 0) {
            $vals[] = 'group';
        }
        if (count($courseandcompleted) > 1) {
            $vals[] = 'course';
        }

        $eval = array(array("name" => "first_page", "mode" => "text", "caption" => get_string("eval_first_page_title", "groupformation"), "text" => get_string("eval_first_page_text", "groupformation")));
        $criteria = $this->store->get_label_set();

        foreach ($criteria as $criterion) {
            $labels = $this->data->get_criterion_specification($criterion);
            if (!is_null($labels)) {
                $labels = $this->filter_criterion_specs_for_eval($criterion, $labels);
            }
            if (!is_null($labels) && count($labels) > 0) {

                $array = $this->get_eval_infos($criterion, $labels, $userid, $groupusers, $courseusers);

                $bars = array();
                $values = array('user' => 1, 'group' => 4, 'course' => 2);

                foreach (array_keys($values) as $key) {
                    $bars[$key] = get_string("eval_caption_" . $key, "groupformation");
                }

                $directions = 1;
                if ($criterion == 'big5') {
                    $directions = 2;
                }

                $eval[] = array("name" => $criterion, "directions" => $directions, "mode" => "chart", "caption" => get_string('eval_name_' . $criterion, 'groupformation'), "values" => $vals, "bars" => $bars, "criteria" => $array);

            }
        }

        return $eval;
    }

    /**
     * Returns eval values for user, group and course
     *
     * @param $criterion
     * @param $labels
     * @param $userid
     * @param $groupusers
     * @param $courseusers
     * @return array
     */
    public function get_eval_infos($criterion, $labels, $userid, $groupusers = array(), $courseusers = array()) {
        $completedusers = array_keys($this->usermanager->get_completed_by_answer_count('userid', 'userid'));
        $groupandcompleted = array_intersect($completedusers, $groupusers);
        $courseandcompleted = array_intersect($completedusers, $courseusers);
        $completed = count($courseandcompleted);
        $coursesize = count($courseusers);
        $setfinaltext = $coursesize > 2;

        $evalinfos = array();

        $users = array_merge(array(intval($userid)), $groupandcompleted, $courseandcompleted);
        $users = array_unique($users);

        $uservalues = $this->read_values_for_user($criterion, $userid, $labels);

        $usersvalues = $this->get_values_for_users($criterion, $users);

        $usersvalues = $this->compute_z_score($usersvalues);


        $groupvalues = $this->get_avg_values_for_users($groupandcompleted, $usersvalues);
        $coursevalues = $this->get_avg_values_for_users($courseandcompleted, $usersvalues);

        foreach ($labels['labels'] as $label => $spec) {
            $user = $uservalues[$label]['values'][0];
            $group = null;
            $course = null;

            if (count($groupandcompleted) >= 2 && !is_null($groupvalues)) {
                $group = $groupvalues[$label]['values'][0];
            }

            if (count($courseandcompleted) >= 2 && !is_null($coursevalues)) {
                $course = $coursevalues[$label]['values'][0];
            }

            $mode = 1;
            if ($criterion == "big5")
                $mode = 2;
            $array = array();
            $array["name"] = $label;//get_string('eval_'.$label,'groupformation');
            $array["values"] = array("user" => $user, "group" => $group, "course" => $course);
            $array["range"] = array("min" => 0, "max" => 1);
            $array["mode"] = $mode;
            $array["captions"] = $this->get_captions($label, $mode, $setfinaltext, $completed, $coursesize);
            $array["cutoff"] = $this->get_eval_text($criterion, $label, $spec["cutoffs"], $user);
            $evalinfos[] = $array;
        }

        return $evalinfos;
    }

    /**
     * Returns evaluation text
     *
     * @param $criterion
     * @param $label
     * @param $cutoffs
     * @param $uservalue
     * @return string
     */
    private function get_eval_text($criterion, $label, $cutoffs, $uservalue) {
        if (is_null($cutoffs)) {
            return "eval_text_" . $criterion . "_" . $label;
        } else {

            $i = 1;

            foreach ($cutoffs as $cutoff) {
                if ($uservalue >= $cutoff) {
                    $i += 1;
                }
            }
            return get_string("eval_text_" . $criterion . "_" . $label . "_" . $i, "groupformation");
        }

    }

    /**
     * Returns captions for evaluation data
     *
     * @param $mode
     * @param $setfinaltext
     * @param $completed
     * @param $coursesize
     * @return array
     * @throws coding_exception
     */
    private function get_captions($label, $mode, $setfinaltext, $completed, $coursesize) {
        $percent = round($completed / ($coursesize + 1) * 100, 2);
        $a = new stdClass();
        $a->percent = $percent;
        $a->completed = $completed;
        $a->coursesize = $coursesize;
        $captions = array(
            "cutoffCaption" => get_string("eval_cutoff_caption_" . $label, "groupformation"),
            "maxCaption" => get_string("eval_max_caption_" . $label, "groupformation"),
            "maxText" => get_string("eval_max_text_" . $label, "groupformation"),
            "finalText" => (($setfinaltext) ? get_string("eval_final_text", "groupformation", $a) : null)
        );
        if ($mode == 2) {
            $captions["mean"] = 0.5;
            $captions["minCaption"] = get_string("eval_min_caption_" . $label, "groupformation");
            $captions["minText"] = get_string("eval_min_text_" . $label, "groupformation");
        }

        return $captions;
    }

    /**
     * Returns values for user
     *
     * @param string $criterion
     * @param int $userid
     * @param array $specs
     * @return mixed
     */
    public function get_values_for_user($criterion, $userid, $specs = null) {
        $function = 'get_' . $criterion;

        return $this->$function($userid, $specs);
    }

    /**
     * Reads values from DB
     *
     * @param $criterion
     * @param $userid
     * @return array
     */
    public function read_values_for_user($criterion, $userid) {
        global $DB;

        $recs = $DB->get_records('groupformation_user_values', array('groupformationid' => $this->groupformationid, 'userid' => $userid, 'criterion' => $criterion));

        $array = array();
        foreach (array_values($recs) as $rec) {
            if (!array_key_exists($rec->label, $array)) {
                $array[$rec->label] = array();
            }
            if (!array_key_exists('values', $array[$rec->label])) {
                $array[$rec->label]['values'] = array();
            }
            $array[$rec->label]['values'][$rec->dimension] = floatval($rec->value);
        }

        return $array;
    }

    /**
     * Returns values for users
     *
     * @param $criterion
     * @param $userid
     * @param null $specs
     * @return mixed
     */
    public function get_values_for_users($criterion, $users) {
        $usersvalues = array();

        foreach (array_values($users) as $userid) {
            $usersvalues[$userid] = $this->read_values_for_user($criterion, $userid);
        }

        return $usersvalues;
    }

    /**
     * Returns average values for the users
     *
     * @param $criterion
     * @param $groupusers
     * @return null
     */
    public function get_avg_values_for_users($groupusers, $usersvalues) {
        $avgvalues = null;
        $groupsize = count($groupusers);
        if ($groupsize > 0) {
            foreach ($groupusers as $groupuser) {
                $uservalues = $usersvalues[$groupuser];

                if (is_null($avgvalues)) {
                    $avgvalues = $uservalues;
                } else {
                    if (!is_null($uservalues)) {
                        foreach ($uservalues as $key => $uservalue) {
                            // $avgvalues[$key]['value'] += $uservalue['value'];
                            foreach ($avgvalues[$key]['values'] as $k => $v) {
                                $avgvalues[$key]['values'][$k] += $uservalue['values'][$k];
                            }
                        }
                    } else {
                        $groupsize = max(1, $groupsize - 1);
                    }
                }
            }
            foreach (array_keys($avgvalues) as $key) {
                foreach ($avgvalues[$key]['values'] as $k => $v) {
                    $avgvalues[$key]['values'][$k] /= $groupsize;
                }
            }
        }

        return $avgvalues;
    }
}
