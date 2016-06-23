<?php
// This file is part of PHP implementation of GroupAL
// http://sourceforge.net/projects/groupal/
//
// GroupAL is free software: you can redistribute it and/or modify
// it under the terms of the GNU Lesser General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// GroupAL implementations are distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Lesser General Public License for more details.
//
// You should have received a copy of the GNU Lesser General Public License
// along with GroupAL. If not, see <http://www.gnu.org/licenses/>.
//
//  This code CAN be used as a code-base in Moodle 
// (e.g. for moodle-mod_groupformation). Then put this code in a folder
// <moodle>\lib\groupal
/**
 * implementation of a user (based on the interface)
 *
 *
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/lgpl.html GNU LGPL v3 or later
 */
require_once 'iuser.php';

class lib_groupal_user implements lib_groupal_iuser {

    private $userID;

    private $userRole;

    // Array with criterions.
    private $crArray = array();

    // A $criterionsArray stores Array of type $criterionArray as declared in criterion.php .

    public function __construct($id, $role, $criterionsArray) {
        $this->userID = $id;
        $this->userRole = $role;
        foreach ($criterionsArray as $key => $value) {
            // TODO fix error: how to instantiate criterion for test cases
            // $this->crArray[$key] = new lib_groupal_specific_criterion($value);
            // array_push( static::$crArray, new Criterion($value) );
        }
    }


    public function getID() {
        return $this->userID;
    }

    public function getRole() {
        return $this->userRole;
    }

    public function getCriterionsList() {
        return $this->crArray;
    }

    public function getCriteriaValues() {
        $outputArray = array();
        foreach ($this->crArray as $value) {
            $outputArray[$value->getID()] = $value->getValueList();
        }
        return $outputArray;
    }
}


