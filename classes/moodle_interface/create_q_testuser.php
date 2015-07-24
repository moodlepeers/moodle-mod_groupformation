<?php
/**
 * Created by PhpStorm.
 * User: zukic07
 * Date: 18/07/15
 * Time: 08:56
 */
require_once (dirname (__FILE__) . '/storage_manager.php');

class mod_groupformation_create_q_testuser {


    /**
     * create users
     *
     * @param $number number of users to create
     * @param
     * @return array of created users
     */
    public function createTestusers($number, $nTopics, $nKnowledges, $groupformation) {
        /* container for created users */
        global $DB;
        /* storage manager */
        $stm = new mod_groupformation_storage_manager($groupformation);

        $categories = $stm->getCategories();

        $users = array();

        for ($j = 1; $j <= $number; $j++) {
            $user = "user$j";
            try {

                $userStd = create_user_record($user, "Moodle_1234");
                $users[] = $user;
            } catch (Exception $e) {
                //echo "<div class='alert'>$user not created, already da</div>";
                // schlägt user creation fehl, dann überspringe auch Datenbankoperationen mit "continue"
                continue;
            }


            /* ----------  Fragebogen fuer jeden user ausfuellen  ---------- */

            try {
                $allInserts = array();
                $userid = $userStd->id;

                // groupformation: #, category: topic, questionid: <iteration>, userid: #, answer: <iteration>

                for ($i = 1; $i<= $nTopics; $i++) {
                    $sql = new stdClass();
                    $sql->groupformation = $groupformation;
                    $sql->category = "topic";
                    $sql->questionid = $i; // $i, weil anzahl topics = anzahl id's
                    $sql->userid = $userid;
                    $sql->answer = $i; // $i, damit topics nur einmal, in "erstellter" Reihenfolge, sortiert sind
                    $allInserts[] = $sql;
                }

                // groupformation: #, category: knowledge, questionid: <iteration>, userid: #, answer: 1 - 6
                for ($i = 1; $i <= $nKnowledges; $i++) {
                    $sql = new stdClass();
                    $sql->groupformation = $groupformation;
                    $sql->category = "knowledge";
                    $sql->questionid = $i; // gleiche Sache wie bei topics (oben drüber)
                    $sql->userid = $userid;
                    $sql->answer = ($j % 2 == 0) ? 1 : 6;
                    $allInserts[] = $sql;
                }
                // groupformation: #, category: grade, questionid: 1, userid: #, answer: 1
                // groupformation: #, category: grade, questionid: 2, userid: #, answer: 4
                // groupformation: #, category: grade, questionid: 3, userid: #, answer: 1
                $sql = new stdClass();
                $sql->groupformation = $groupformation;
                $sql->category = "grade";
                $sql->questionid = 1;
                $sql->userid = $userid;
                $sql->answer = 1;
                $allInserts[] = $sql;
                $sql = new stdClass();
                $sql->groupformation = $groupformation;
                $sql->category = "grade";
                $sql->questionid = 2;
                $sql->userid = $userid;
                $sql->answer = 4;
                $allInserts[] = $sql;
                $sql = new stdClass();
                $sql->groupformation = $groupformation;
                $sql->category = "grade";
                $sql->questionid = 3;
                $sql->userid = $userid;
                $sql->answer = 1;
                $allInserts[] = $sql;

                // groupformation: #, category: team, questionid: 1 - 27, userid: #, answer: 1 - 6
                if (in_array("team", $categories)) {
                    for ($i = 1, $n = $stm->getNumber("team"); $i <= $n; $i++) {
                        $sql = new stdClass();
                        $sql->groupformation = $groupformation;
                        $sql->category = "team";
                        $sql->questionid = $i;
                        $sql->userid = $userid;
                        $sql->answer = ($j % 2 == 0) ? 1 : 6;
                        $allInserts[] = $sql;
                    }
                }
                // groupformation: #, category: character, questionid: 1 - 11, userid: #, answer: 1 - 6
                if (in_array("character", $categories)) {
                    for ($i = 1, $n = $stm->getNumber("character"); $i <= $n; $i++) {
                        $sql = new stdClass();
                        $sql->groupformation = $groupformation;
                        $sql->category = "character";
                        $sql->questionid = $i;
                        $sql->userid = $userid;
                        $sql->answer = ($j % 2 == 0) ? 1 : 6;
                        $allInserts[] = $sql;
                    }
                }
                // groupformation: #, category: motivation, questionid: 1 - 18, userid: #, answer: 1 - 6
                if (in_array("motivation", $categories)) {
                    for ($i = 1, $n = $stm->getNumber("motivation"); $i <= $n; $i++) {
                        $sql = new stdClass();
                        $sql->groupformation = $groupformation;
                        $sql->category = "motivation";
                        $sql->questionid = $i;
                        $sql->userid = $userid;
                        $sql->answer = ($j % 2 == 0) ? 1 : 6;
                        $allInserts[] = $sql;
                    }
                }
                // groupformation: #, category: sellmo, questionid: 1 - 31, userid: #, answer: 1 - 5
                for ($i = 1, $n = $stm->getNumber("sellmo"); $i <= 31; $i++) {
                    $sql = new stdClass();
                    $sql->groupformation = $groupformation;
                    $sql->category = "sellmo";
                    $sql->questionid = $i;
                    $sql->userid = $userid;
                    $sql->answer = ($j % 2 == 0) ? 1 : 5;
                    $allInserts[] = $sql;
                }
                // groupformation: #, category: self, questionid: 1 - 10, userid: #, answer: 1 - 6
                if (in_array("self", $categories)) {
                    for ($i = 1, $n = $stm->getNumber("self"); $i <= 10; $i++) {
                        $sql = new stdClass();
                        $sql->groupformation = $groupformation;
                        $sql->category = "self";
                        $sql->questionid = $i;
                        $sql->userid = $userid;
                        $sql->answer = ($j % 2 == 0) ? 1 : 6;
                        $allInserts[] = $sql;
                    }
                }
                // groupformation: #, category: srl, questionid: 1 - 62, userid: #, answer: 1 - 6
                if (in_array("srl", $categories)) {
                    for ($i = 1, $n = $stm->getNumber("srl"); $i <= 62; $i++) {
                        $sql = new stdClass();
                        $sql->groupformation = $groupformation;
                        $sql->category = "srl";
                        $sql->questionid = $i;
                        $sql->userid = $userid;
                        $sql->answer = ($j % 2 == 0) ? 1 : 6;
                        $allInserts[] = $sql;
                    }
                }

                // alles eintragen
                $sql = new stdClass();
                $sql->groupformation = $groupformation;
                $sql->userid = $userid;
                $sql->completed = 1;
                $sql->timecompleted = NULL;
                $sql->groupid = NULL;
                $DB->insert_record("groupformation_started", $sql);
                $DB->insert_records("groupformation_answer", $allInserts);

                // enroll user
                global $COURSE;
                enrol_try_internal_enrol($COURSE->id, $userid);

            } catch (Exception $e) {
                echo "<div class='alert'>$user DB-Eintrag fehlgeschlagen</div>";
            }
        }


        /* ---------- / Fragebogen fuer jeden user ausfuellen  ---------- */

        return "user erstellt!!";
    }

    public function deleteTestusers() {
        global $DB;

        try {

            $userids = $DB->get_records_sql("SELECT * FROM {user} WHERE username LIKE 'user%'");

            foreach ($userids as $u) {
                $DB->delete_records("user", array('id' => $u->id));
                $DB->delete_records("groupformation_answer", array('userid' => $u->id));
                $DB->delete_records("groupformation_started", array('userid' => $u->id));

            }

            return "user und ihre antworten gelöscht!";
        } catch (Exception $e) {
            return "fehler beim delete-Befehl";
        }


    }
}