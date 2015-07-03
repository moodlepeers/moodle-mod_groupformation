<?php
/**
 *
 *
 *
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once ($CFG->dirroot . '/mod/groupformation/classes/moodle_interface/storage_manager.php');


class mod_groupformation_GroupingViewController
{
    private $store = NULL;
    private $viewState = 0;

    private $groups = array();


    /* survey beendet?
     */
    private $surveyState;

    /* generierte gruppen ins moodle übernommen?
     */
    private $groupsAddopted;


    public function __construct($groupformationID){
        $this->store = new mod_groupformation_storage_manager($groupformationID);
        $this->groups = $this->store->getGeneratedGroups();

        //TODO surveyState & groupsAddopted soll aus db abgefragt werden
        $this->surveyState = 1;
        $this->groupsAddopted = 0;

        /* Survey läuft noch */
        if($this->surveyState == 0){
            $this->viewState = 0;
        }
        /* Survey beendet, aber keine Gruppen generiert*/
        elseif($this->surveyState == 1 && !(isset($this->groups) && !empty($this->groups) )){
            $this->viewState = 1;
        }
        /* Gruppenbildung läuft */
        elseif($this->surveyState == 1 && 0)
        {
            $this->viewState = 2;
        }
        /* Gruppen generiert, aber nicht ins Moodle integriert */
        elseif (isset($this->groups) && !empty($this->groups) && $this->groupsAddopted == 0) {
            $this->viewState = 3;
        }
        /* Gruppen generiert und ins Moodle integriert */
        else
        {
            $this->viewState = 4;
        }
    }

    public function displaySettings(){
        echo'<div class="gf_pad_header">
		Gruppenbildung
		</div>';

        switch($this->viewState){
            case 0:
                echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="1" class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung starten</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
                break;

            case 1:
                echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="1" class="gf_button gf_button_pill gf_button_small">Gruppenbildung starten</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
                break;

            case 2:
                echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="0" class="gf_button gf_button_pill gf_button_small">Gruppenbildung abbrechen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
                break;

            case 3:
                echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="0" class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung starten</button>
                    <button class="gf_button gf_button_pill gf_button_small" >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" >Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
                break;

            case 4:
                echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="0" class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung starten</button>
                    <button class="gf_button gf_button_pill gf_button_small" >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
                break;

            case 'default':
            default:
            echo '<div class="gf_pad_content bp_align_left-middle">
                    <button type="submit" name="starting" value="1" class="gf_button gf_button_pill gf_button_small" disabled>Gruppenbildung starten</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled >Gruppen l&ouml;schen</button>
                    <button class="gf_button gf_button_pill gf_button_small" disabled>Gruppe übernehmen</button>
                    <p>Statusanzeige "Gruppenbildung l&auml;uft..." mit %Zahl oder voraussichtlicher Endzeit</p>
		        </div>';
            break;
        }
    }

    public function displayAnalysis(){
        echo '
        <div class="gf_pad_header_small">
			Auswertung
		</div>';

        if($this->viewState == 3 || $this->viewState == 4){
            // TODO: ist noch statisch
            echo '
            <div class="gf_pad_content">
                <p style="color: green;">die daten sind statisch</p>
                <p>Gleichm&auml;&szlig;igkeit der Gruppen: <b>0.7</b><span class="toolt" tooltip="ein Wert > 0.5 ist gut"></span></p>
                <p>Anzahl gebildeter Gruppen: <b>100</b></p>
                <p>Maximale Gruppengr&ouml;&szlig;e: <b>6</b></p>
		    </div>';
        }
        else
        {
            echo '
            <div class="gf_pad_content">
                <p style="opacity: 0.5;"><i>keine Daten vorhanden</i></p>
		    </div>';
        }
    }

    public function displayUncompleteGroups(){
        echo '<div class="gf_pad_header_small">Maximale Gruppengr&ouml;&szlig;e wurde bei folgenden Gruppen nicht erreicht: </div>';

        if($this->viewState == 3 || $this->viewState == 4){
            // TODO: ist noch statisch
            echo '
		<div class="gf_pad_content">
		    <p style="color: green;">die daten sind statisch</p>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_16 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_18 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_25 - Anzahl Mitglieder: <b>1</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_36 - Anzahl Mitglieder: <b>2</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
			<div class="grid row_highlight">
				<div class="col_m_87-5">Gruppennamen_99 - Anzahl Mitglieder: <b>3</b> </div>
				<div class="col_m_12-5 bp_align_right-middle"><button class="gf_button gf_button_pill gf_button_tiny">scroll to</button></div>
			</div>
		</div>';
        }
        else
        {
            echo '
            <div class="gf_pad_content">
                <p style="opacity: 0.5;"><i>keine Daten vorhanden</i></p>
		    </div>';
        }

    }

    public function displayGroups(){
        echo '<div class="gf_pad_header_small">
			        &Uuml;bersicht gebildeter Gruppen
            </div>';

        if($this->viewState == 3 || $this->viewState == 4){

            echo '<div class="gf_pad_content">
                    <p style="color: green;">Gruppenname und user-name(hier userID) sind dynamisch</p>';

            foreach($this->groups as $key=>$value) {
                //TODO: Gruppenquallität && Link zur MoodleGruppe(wenn integriert) je Gruppe fehlt
                $users = $this->store->getUsersFromGeneratedGroups($key);

                echo '<div class="grid bottom_stripe">
                    <div class="col_s_50">Name: <b>' . $value->groupname . '</b></div>
                    <div class="col_s_25">Gruppenqualit&auml;t: <b>0.74</b><span class="toolt" tooltip="ein Wert > 0.5 ist gut"></span></div>
                    <div class="col_s_25 bp_align_right-middle">'. $this->linkToGoup() .'</div>
                    <div class="col_s_100 gf_group_links">';

                foreach ($users as $user) {
                    //TODO: get link to user profile with userid
                    echo '<a href="#">' . $user->userid . '</a>';
                }

                echo '</div>
			    </div>';
            }
            echo '</div>';
        }else{
            echo '<div class="gf_pad_content">
                <p style="opacity: 0.5; margin-left: 4px;"><i>keine Daten vorhanden</i></p>
		    </div>';
        }
    }

    private function linkToGoup()
    {
        if($this->viewState == 4){
            //TODO: get link to group with the groupID
            return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny">zur Moodle Gruppenansicht</button></a>';
        }
        else
        {
            return '<a href="#"><button class="gf_button gf_button_pill gf_button_tiny" disabled>zur Moodle Gruppenansicht</button></a>';
        }

    }
}


?>