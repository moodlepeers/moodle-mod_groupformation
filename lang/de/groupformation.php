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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * German strings for module
 *
 * @package mod_groupformation
 * @author Eduard Gallwas, Johannes Konert, Rene Roepke, Nora Wester, Ahmed Zukic
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
defined('MOODLE_INTERNAL') || die ();

$string ['language'] = 'de';
$string ['modulename'] = 'Gruppenformation';
$string ['modulenameplural'] = 'Gruppenformationen';
$string ['modulename_help'] = 'Erlaubt die automatische Erstellung von (Lern)-Gruppen Ihrer Teilnehmenden. Basierend auf Fragebogen-Antworten werden die Gruppen(zusammenstellungen) optimiert.';
$string ['beta_version'] = '';
$string ['password_wrong'] = 'Das eingegebene Passwort ist falsch';
$string ['groupformationname'] = 'Name der Gruppenformation';
$string ['groupformationname_help'] = 'Dieser Titel wird auf der Kursseite angezeigt.';
$string ['groupformation'] = 'Gruppenformation';
$string ['pluginadministration'] = 'Administration: Gruppenformation';
$string ['pluginname'] = 'groupformation';
$string ['nogroupformation'] = 'keine Gruppenformation';
$string ['groupnameexists'] = 'Dieser Gruppenname existiert schon';
$string ['generategroups'] = 'Gruppengenerierung';
$string ['namingschema'] = 'Namensschema';
$string ['userpergroup'] = 'Wieviele Studierende pro Gruppe?';
$string ['notOpen'] = 'Die Abgabe ist nicht mehr möglich';
$string ['continueTheForm'] = 'Weiter ausfüllen';
$string ['completeTheForm'] = 'Ausfüllen';
$string ['alreadySubmitted'] = 'Der Fragebogen wurde schon ausgefüllt';
$string ['overview'] = 'Übersicht';
$string ['generategroups'] = "Gruppen erstellen";
$string ['edit_param'] = 'Bearbeiten';
$string ['editparam'] = 'Paramter bearbeiten';
$string ['nochangespossible'] = 'Der Fragebogen wurde bereits von mindestens einem Studierenden beantwortet. Sie können nur noch Gruppengröße oder Gruppenanzahl ändern. Weitere Änderungen werden nicht gespeichert.';
$string ['availability_nochangespossible'] = 'Der Fragebogen wurde bereits beantwortet. Sie können die Verfügbarkeit nicht mehr ändern.';
$string ['scenario'] = 'Szenario';
$string ['scenarioLabel'] = '';
$string ['scenario_description'] = 'Bitte wählen Sie das für Sie am Besten geeignete Szenario für die Gruppenformation aus.';
$string ['scenarioInfo'] = 'Die drei Szenarien unterscheiden sich darin, wie die Fragebogen-Antworten bei der Gruppenbildung berücksichtigt werden.
			Beim Projekgruppen wird darauf geachtet, dass sich Vorwissen und Merkmale der Gruppenmitglieder ergänzen während die Motivation und angestrebten Ziele in der Gruppe möglichst ähnlich sind.
			Bei den Hausaufgabengruppen wird so optimiert, dass für jede Gruppe die besten Vorausetzungen für gemeinsames Lernen gegeben sind.
		    Beim Referatsgruppen steht das gemeinsame Interesse an den zur Auswahl stehenden Referatsthemen im Vordergrund.';
$string ['groupformationsettings'] = 'Gruppenformation Einstellungen';
$string ['scenario_projectteams'] = 'Projektteams';
$string ['scenario_projectteams_description'] = 'Projektteams arbeiten über einen längeren Zeitraum gemeinsam intensiv an einem Projekt (z.B. Durchführung einer Studie, Abgabe eines Berichtes, etc.). Oft können die Aufgaben innerhalb des Teams aufgeteilt werden, sodass es von Vorteil ist, wenn sich die Kompetenzen der Teammitglieder gegenseitig ergänzen. Typischerweise wird die gemeinsame Arbeit mit einer Gruppennote für alle Teammitglieder bewertet. Daher geht es bei der Zusammensetzung von Projektteams primär um ähnliche Motivation und Zielsetzung der Teammitglieder.';
$string ['scenario_homeworkgroups'] = 'Hausaufgabengruppen';
$string ['scenario_homeworkgroups_description'] = 'Hausaufgabengruppen arbeiten in regelmäßigen Abständen (meist wöchentlich) an Aufgaben, die zur Vorbereitung auf eine Klausur dienen. Auch wenn die Aufgaben (oft auch "Übungen" oder "Übungszettel" genannt) grundsätzlich auf unterschiedliche Gruppenmitglieder aufgeteilt werden könnten, ist dies meist nicht sinnvoll, weil bei der abschließenden Klausur jedes Gruppenmitglied individuell benotet wird und daher alle Aufgaben selbst lösen können muss. Daher geht es bei der Zusammensetzung von Hausaufgabengruppen primär um unterschiedliches Vorwissen und unterschiedliche Lernstile, die sich ergänzen.';
$string ['scenario_presentationgroups'] = 'Referatgruppen';
$string ['scenario_presentationgroups_description'] = 'Referatsgruppen arbeiten nur über einen relativ kurzen Zeitraum zusammen an einer gemeinsamen Präsentation. Oft wird dabei die Aufgabenstellung schon zu Beginn auf die Gruppenmitglieder aufgeteilt (oder gewählt), dann individuell bearbeitet und erst am Ende wieder zusammengesetzt. Bewertet wird meist die gemeinsame Gruppenleistung. Daher geht es bei der Zusammensetzung von Referatsgruppen primär um gemeinsame Interessen an Themen.';
$string ['time'] = 'Zeit';
$string ['topics'] = 'Themen';
$string ['topics_dummy'] = 'Thema ';
$string ['knowledge_dummy'] = 'Beispiel ';
$string ['topics_description'] = 'Ich möchte (Gruppen-)Themen zur Auswahl anbieten';
$string ['topics_description_extended'] = 'Geben Sie hier die Themen an, die die Gruppen bearbeiten sollen. Die Gruppenzuordnung erfolgt nach dem Priorisierungen der Studierenden.';
$string ['topics_question'] = 'Bitte sortieren Sie die zur Wahl stehenden Themen entsprechend Ihrer Präferenz, beginnend mit Ihrem bevorzugten Thema.';
$string ['topicchoice'] = 'Themenauswahl';
$string ['useOneLineForEachTopic'] = 'Pro Thema jeweils eine Zeile benutzen';
$string ['knowledge'] = 'Vorwissen';
$string ['knowledge_description'] = 'Das Vorwissen in bestimmten Gebieten soll in die Gruppenbildung einfließen.';
$string ['knowledge_description_extended'] = 'Geben Sie hier die Wissensgebiete ein, in welchen sich die Studierenden einschätzen sollen.
		Eine Vorschau des Fragebogens-Abschnittes für die Studierenden ist rechts zu sehen.';
$string ['knowledgeChoice'] = 'Vorwissen';
$string ['knowledge_info_presentation'] = 'Geben Sie hier die Wissensgebiete ein, in welchen sich die Studierenden einschätzen sollen.
		Eine Vorschau des Fragebogens-Abschnittes für die Studierenden ist rechts zu sehen.
		Für Ihre Auswahl "Referatsgruppen" wird das Vorwissen nur berücksichtigt, falls nach Optimierung der Themenwahl noch weiter optimiert werden kann.';
$string ['knowledge_info_homework'] = 'Geben Sie hier die Wissensgebiete ein, in welchen sich die Studierenden einschätzen sollen.
		Eine Vorschau des Fragebogens-Abschnittes für die Studierenden ist rechts zu sehen.
		Für Ihre Auswahl "Hausaufgabengruppen" wird so optimiert, dass das Vorwissen sich in jeder Gruppe möglichst ergänzt.';
$string ['knowledge_info_project'] = 'Geben Sie hier die Wissensgebiete ein, in welchen sich die Studierenden einschätzen sollen.
		Eine Vorschau des Studierenden-Fragebogens sehen Sie rechts.
		Für Ihre Auswahl "Projektgruppen" wird so optimiert, dass das Vorwissen sich in jeder Gruppe möglichst ergänzt, das Wissensniveau der Studierenden aber vergleichbar ist.';
$string ['add_line'] = 'Zeile hinzufügen';
$string ['remove_line'] = 'Zeile entfernen';
$string ['preview'] = 'Vorschau:';
$string ['input'] = 'Eingabe:';
$string ['knowledge_question'] = 'Wie schätzen Sie Ihr persönliches Vorwissen in folgenden Gebieten ein?';
$string ['knowledge_scale'] = '0&nbsp;=&nbsp;kein&nbsp;Vorwissen, 100&nbsp;=&nbsp;sehr&nbsp;viel Vorwissen';
$string ['groupoptions'] = 'Gruppen-Einstellungen';
$string ['groupoption_description'] = 'Gruppen-Einstellungen';
$string ['groupoption_help'] = 'Diese Einstellungen können bis zum Starten der Gruppenbildung geändert werden, selbst wenn schon Fragebögen von Studierenden ausgefüllt wurden.';
$string ['groupoption_onlyactivestudents'] = 'Studierende ohne einzige Antwort werden nicht in Gruppen eingeteilt.';
$string ['maxmembers'] = 'Max. Gruppengröße';
$string ['maxgroups'] = 'Max. Gruppenanzahl';
$string ['maxpoints'] = 'Max. Punktzahl';
$string ['groupname'] = 'Gruppenname';
$string ['groupname_help'] = 'Der Gruppen-Name wird als Präfix für die generierten Moodle-Gruppen genutzt. Das Schema ist <Gruppen-Name>_XXX, wobei XXX für die Nummer der Gruppe steht.';
$string ['evaluationmethod_description'] = 'Nach welchem Bewertungsschema wird die Leistung der Gruppen am Ende bewertet?';
$string ['grades'] = 'Noten';
$string ['points'] = 'Punkte';
$string ['justpass'] = 'Nur Bestehen';
$string ['noevaluation'] = 'Keine Bewertung';
$string ['useOneLineForEachKnowledge'] = 'Für jedes Thema eine eigene Zeile benutzen';
$string ['cannotloadxml'] = 'XML Datei konnte nicht geladen werden.';
$string ['scenario_error'] = 'Bitte wäen Sie ein Szenario aus.';
$string ['maxmembers_error'] = 'Bitte wählen Sie die maximale Gruppengröße.';
$string ['maxgroups_error'] = 'Bitte wählen Sie die maximale Gruppenanzahl.';
$string ['maxpoints_error'] = 'Sie müssen eine Punktzahl zwischen 1 und 100 angeben.';
$string ['groupname_error'] = 'Der Gruppenname kann maximal 100 Zeichen lang sein.';
$string ['evaluationmethod_error'] = 'Bitte wählen Sie die Methode zur Bewertung aus.';
$string ['choose_scenario'] = 'Szenario auswählen';
$string ['choose_number'] = 'Anzahl auswählen';
$string ['choose_evaluationmethod'] = 'Bewertungsmethode auswählen';
$string ['evaluation_point_info'] = 'Bitte maximale Punktzahl eingeben';
$string ['students_enrolled_info'] = 'Studierenden sind im Kurs eingeschrieben';
$string ['groupSettingsInfo'] = 'Sie haben Themen zur Auswahl angegeben. Daraus bestimmt sich die Anzahl der Gruppen und deren Größe.';
$string ['analyse'] = 'Analyse';
$string ['questionnaire_not_started'] = 'Der Fragebogen wartet noch auf Ihre Bearbeitung.';
$string ['questionnaire_press_to_begin'] = 'Geben Sie ihre Zustimmung und klicken Sie auf "Weiter", um zu beginnen.';
$string ['questionnaire_not_submitted'] = 'Sie haben den Fragebogen noch nicht abgegeben.';
$string ['questionnaire_press_continue_submit'] = 'Klicken Sie auf "Bearbeiten", um den Fragebogen weiter auszufüllen oder auf "Abgeben", um ihn abzugeben. Durch "Zustimmung wiederrufen" können sie ihre Zustimmung wiederrufen und alle ihre Antworten werden dadurch gelöscht.

Wenn Sie den Fragebogen abgegeben haben, können Sie ihre Zustimmung nicht mehr wiederrufen.';
$string ['questionnaire_answer_stats'] = 'Aktueller Zustand des Fragebogens: ';
$string ['questionnaire_submitted'] = 'Sie haben den Fragebogen bereits abgegeben und können Ihre Antworten nicht mehr ändern.';
$string ['questionnaire_press_preview'] = 'Klicken Sie auf "Vorschau", um den Fragebogen anzusehen.';
$string ['questionnaire_no_more_questions'] = 'Es gibt keine weiteren Fragen zu beantworten.';
$string ['questionnaire_press_beginning_submit'] = 'Klicken Sie auf "Zur Anfangsseite", um zum Anfang zurückzukehren. Dort können Sie ihren Fragebogen entgültig abgeben.';
$string ['questionnaire_go_to_start'] = 'Zur Anfangsseite';
$string ['questionnaire_submit'] = 'Abgeben';
$string ['questionnaire_submit_disabled_teacher'] = 'Abgeben ist deaktiviert, da dies nur eine Vorschau ist.';
$string ['questionnaire_preview'] = 'Das ist eine Vorschau des Fragebogens.';
$string ['category_general'] = 'Allgemeines';
$string ['category_grade'] = 'Ziele';
$string ['category_points'] = 'Ziele';
$string ['category_team'] = 'Gruppenaspekte';
$string ['category_character'] = 'Persönlichkeitsmerkmale';
$string ['category_motivation'] = 'Kurs-Motivation';
$string ['category_learning'] = 'Lernstile';
$string ['category_knowledge'] = 'Vorwissen';
$string ['category_topic'] = 'Themenauswahl';
$string ['category_sellmo'] = 'Lern- und Leistungsmotivation';
$string ['category_self'] = 'Selbsteinschätzung';
$string ['category_srl'] = 'Selbstreguliertes Lernen';
$string ['stats_partly'] = 'In der Kategorie "{$a->category}" haben Sie {$a->answered} von {$a->questions} Fragen beantwortet.';
$string ['stats_all'] = 'In der Kategorie "{$a->category}" haben Sie alle Fragen beantwortet.';
$string ['stats_none'] = 'In der Kategorie "{$a->category}" haben Sie noch keine Frage beantwortet.';
$string ['tab_questionnaire'] = 'Fragebogen';
$string ['tab_overview'] = 'Überblick';
$string ['tab_grouping'] = 'Gruppenbildung';
$string ['tab_preview'] = 'Fragebogen-Vorschau';
$string ['tab_evaluation'] = 'Auswertung';
$string ['tab_group'] = 'Gruppenzuordnung';
$string ['questionnaire_availability_info_future'] = 'Im Zeitraum vom {$a->start} Uhr bis {$a->end} Uhr wird der Fragebogen zur Verfügung stehen.';
$string ['questionnaire_availability_info_now'] = 'Der Fragebogen ist offen und kann bis {$a->end} ausgefüllt werden.';
$string ['questionnaire_availability_info_until'] = 'Der Fragebogen ist noch bis {$a->end} Uhr freigeschaltet.';
$string ['questionnaire_availability_info_from'] = 'Der Fragebogen ist ab {$a->start} Uhr freigeschaltet.';
$string ['questionnaire_available'] = 'Der Fragebogen steht zur Bearbeitung bereit.';
$string ['questionnaire_not_available_begin'] = 'Der Fragebogen ist verfügbar ab {$a->start}.';
$string ['questionnaire_available_end'] = 'Der Fragebogen ist verfügbar bis {$a->end}.';
$string ['questionnaire_not_available'] = 'Der Fragebogen ist derzeit nicht verfügbar.';
$string ['questionnaire_not_available_begin_end'] = 'Der Fragebogen ist verfügbar von {$a->start} bis {$a->end}.';
$string ['questionnaire_not_available_end'] = 'Der Fragebogen ist nicht mehr verfügbar.';
$string ['info_header_student'] = 'Was bedeutet Gruppenformation?';
$string ['info_text_student'] = 'In diesem Moodle-Kurs wird die Gruppenformation dazu genutzt {$a->scenario_name} für eine erfolgreiche Zusammenarbeit zu bilden.
		Wenn Sie den Fragebogen ausgefüllt und abgeschickt haben, werden für Sie geeignete Lernpartner/innen ermittelt.
		Alle Angaben werden vertraulich behandelt.
		<br>Ist die Befragungszeit um, werden Gruppen unter Berücksichtigung Ihrer Angaben und Präferenzen gebildet. Sie können anschließend hier Ihre Gruppenmitglieder einsehen (und auch über Moodle kontaktieren).
		<br><br>
		Fragen? Probleme? Lob? Anregungen?<br>
		Die Plugin-Entwickler/innen und Wissenschaftler/innen dazu, finden Sie unter<br>
		http://sourceforge.net/projects/moodlepeers/ <br>
		(erstellt und weiterentwickelt mit Mitteln zur Qualitätsverbesserung der Lehre\' der TU Darmstadt)
';
$string ['info_header_teacher_analysis'] = 'Wie funktioniert die Gruppenformation?';
$string ['info_header_teacher_settings'] = 'Wie funktioniert die Gruppenformation?';
$string ['info_text_teacher_settings'] = 'Mit diesem Plugin haben Sie die Möglichkeit die Bildung von Gruppen Ihrer Studierenden zu optimieren. Die drei erforderlichen Schritte sind:<br>
		1.)	Sie fügen die Aktivität „Gruppenformation“ Ihrem Kurs hinzu.
		Auf dieser Seite können Sie auswählen, welches Szenario am besten zu Ihren Vorstellungen der späteren Gruppenarbeit passt
		(bei Unsicherheit wählen Sie Projektteams). Die weiteren Einstellungen erlauben Ihnen die gewünschte Gruppengröße und evtl. eine Themenauswahl für
		Studierende anzugeben. Wenn Sie möchten, dass die Studierenden sich im Vorwissen gut ergänzen in den später gebildeten Gruppen, können Sie die Vorwissensthemen
		zur Abfrage angeben.<br>
		2.)	Studierende sehen den Fragebogen, welcher basierend auf Ihren Einstellungen erstellt wurde. Die Dauer der Verfügbarkeit der Fragen können Sie einstellen (siehe 1.).
		Eine Vorschau des Studierenden-Fragebogens erhalten Sie beim späteren Öffnen der Aktivität als Lehrende/r.<br>
		3.)	Sie können unter dem Menüpunkt ‚Gruppenformation‘, wenn Sie die Aktivität später selbst aufrufen, sehen, wie viele Antworten bereits
		vorliegen. Sind Sie mit dem Rücklauf zufrieden, starten Sie manuell die Bildung der Gruppen (dies geschieht niemals automatisch).<br>
		Es dauert eine Weile, bis alle Gruppen fertig erstellt sind. Das Ergebnis können Sie sich noch einmal ansehen, bevor Sie die Gruppen so in Moodle übernehmen.<br>
		<br>
		Fertig. Jetzt können auch die Studierenden Ihre Gruppenmitglieder sehen. Es ist Ihnen als Lehrende/r jederzeit möglich, die Gruppen manuell in Moodle nachzubearbeiten (bspw. bei Nachzüglern).<br>
';
$string ['info_text_teacher_analysis'] = 'Mit diesem Plugin haben Sie die Möglichkeit die Bildung von Gruppen Ihrer Studierenden zu optimieren. Die drei erforderlichen Schritte sind:<br>
		<br>
		1.)	Sie fügen die Aktivität „Gruppenformation“ Ihrem Kurs hinzu.
		(Das ist bereits geschehen, wenn Sie diese Seite hier sehen).
		In den Einstellungen können Sie auswählen, welches Szenario am besten zu Ihren Vorstellungen der späteren Gruppenarbeit passt
		(bei Unsicherheit wählen Sie Projektteams). Die weiteren Einstellungen erlauben Ihnen die gewünschte Gruppengröße und evtl. eine Themenauswahl für
		Studierende anzugeben. Wenn Sie möchten, dass die Studierenden sich im Vorwissen gut ergänzen in den später gebildeten Gruppen, können Sie die Vorwissensthemen
		zur Abfrage angeben.<br>
		<br>
		2.)	Studierende sehen den Fragebogen, welcher basierend auf Ihren Einstellungen erstellt wurde. Die Dauer der Verfügbarkeit der Fragen können Sie einstellen (siehe 1.).
		Eine Vorschau des Studierenden-Fragebogens erhalten Sie im Menü oben.<br>
		<br>
		3.)	Sie können unter dem Menüpunkt ‚Gruppenformation‘ hier auf dieser Seite sehen, wie viele Antworten bereits
		vorliegen. Sind Sie mit dem Rücklauf zufrieden, starten Sie manuell die Bildung der Gruppen (dies geschieht niemals automatisch).<br>
		Es dauert eine Weile, bis alle Gruppen fertig erstellt sind. Das Ergebnis können Sie sich noch einmal ansehen, bevor Sie die Gruppen so in Moodle übernehmen.<br>
		<br>
		Fertig. Jetzt können auch die Studierenden Ihre Gruppenmitglieder sehen. Es ist Ihnen als Lehrende/r jederzeit möglich, die Gruppen manuell in Moodle nachzubearbeiten (bspw. bei Nachzüglern).<br>
';
$string['statusGrupping0'] = 'Um die Gruppenbildung starten zu können, müssen Sie die Aktivität im Tab Überblick beenden. ';
$string['statusGrupping1'] = 'Sie können die Gruppenbildung jetzt starten.';
$string['statusGrupping2'] = 'Die Gruppenbildung läuft.';
$string['statusGrupping3'] = 'Die Gruppenbildung wird abgebrochen.';
$string['statusGrupping4'] = 'Gruppenbildung ist abgeschlossen. Sie können den Gruppenvorschlag übernehmen, oder verwerfen. ';
$string['statusGrupping5'] = 'Die realen Gruppen sind gebildet. Sie können jetzt manuelle Veränderung an den Gruppen vornehmen. ';
$string['grouping_start'] = 'Gruppenbildung starten';
$string['grouping_delete'] = 'Gruppenvorschlag verwerfen';
$string['grouping_adopt'] = 'Gruppenvorschlag übernehmen';
$string['grouping_abort'] = 'Gruppenbildung abbrechen';
$string['grouping_edit'] = 'Gruppen bearbeiten';
$string['moodlegrouping_delete'] = 'Moodle-Gruppen l&ouml;schen';
$string['questionnaire_commited'] = 'Ihre Antworten sind abgegeben. Somit können Sie sie nicht mehr verändern.';
$string['no_data_to_display'] = 'Keine Daten vorhanden.';

$string['onlyactivestudents'] = 'Zur Gruppenbildung sollen ausschließlich Studierende betrachtet werden, die mind. eine Frage beantwortet haben.';
$string['emailnotifications'] = 'Nach Abschluss der Gruppenbildung möchte ich via Nachricht in Moodle benachrichtigt werden.';
$string['onlyactivestudents_description'] = 'Zur Gruppenbildung sollen ausschließlich Studierende betrachtet werden, die mind. eine Frage beantwortet haben.';
$string['emailnotifications_description'] = 'Nach Abschluss der Gruppenbildung möchte ich via Nachricht in Moodle benachrichtigt werden.';
$string['sampleGroupName'] = 'Der Name deiner Gruppe ist ';
$string['oneManGroup'] = 'Du bist allein in dieser Gruppe.';
$string['noUser'] = 'Der Nutzer existiert nicht!';
$string['membersAre'] = 'Deine Arbeitskollegen sind: ';
$string['groupingNotReady'] = 'Die Gruppenbildung ist noch nicht abgeschlossen.';
$string['jobget_name'] = 'Gruppenbildung und Aufräumen';
$string['no_time'] = 'Kein Zeitpunkt festgelegt';
$string['activity_end'] = 'Aktivität beenden';
$string['activity_start'] = 'Aktivität starten';
$string['analysis_status_info0'] = 'Sie müssen die Aktivität beenden, bevor sie Gruppen bilden können.';
$string['analysis_status_info1'] = 'Sie müssen die Aktivität starten, damit Studierende den Fragebogen beantworten können.';
$string['analysis_status_info2'] = 'Die Gruppenbildung wurde bereits angestoßen bzw. durchgeführt. Die Aktivität kann nicht mehr gestartet werden.';
$string['analysis_status_info3'] = 'Sie können die Aktivität starten oder beenden.';
$string['analysis_status_info4'] = 'Sie können die Gruppenbildung jetzt starten.';
$string['contact_members'] = 'Um deine Gruppenmitglieder zu kontaktieren, klicke auf deren Profilnamen.';
$string['invalid'] = 'Ungültiger Zustand';
$string['groups_build'] = 'Gruppen sind gebildet.';
$string['activity_visible'] = 'Die Aktivität ist für dich nicht einsehbar.';

$string['are'] = 'Es gibt ';
$string['are_now'] = 'Es gibt derzeit ';
$string['students_available_single'] = 'eingeschriebenen Studierenden, der den Fragebogen ausfüllen kann.';
$string['students_available_multiple'] = 'eingeschriebene Studierende, die den Fragebogen ausfüllen können.';
$string['students_answered_single'] = 'Studierender hat den Fragebogen bearbeitet.';
$string['students_answered_multiple'] = 'Studierende haben den Fragebogen bearbeitet.';
$string['name_by_group'] = 'Name: ';
$string['quality'] = 'Gruppenqualität: ';
$string['quality_info'] = 'Der Gruppen-Performanz-Index (GPI) gibt die Qualität der gebildeten Gruppe wieder und ist ein Wert zwischen 0 und 1. Je größer der Wert, deste besser ist die gebildete Gruppe. Ist kein Wert angegeben, so sind die Gruppen nicht algorithmisch gebildet, sondern randomisiert.';
$string['to_groupview'] = 'zur Moodle Gruppenansicht';
$string['number_member'] = 'Anzahl Mitglieder: ';
$string['kohort_index'] = 'Kohorten-Performanz-Index: ';
$string['kohort_index_info'] = 'Der Kohorten-Performanz-Index (KPI) gibt die Qualität der gebildeten Gruppen wieder und ist ein Wert zwischen 0 und 1. Je größer der Wert, desto besser sind die gebildeten Gruppen.';
$string['max_group_size'] = 'Maximale Gruppengröße: ';
$string['number_of_groups'] = 'Anzahl gebildeter Gruppen: ';
$string['options'] = 'Optionen';
$string['activity'] = 'Aktivität ';
$string['statistic'] = 'Fragebogenstatistik';
$string['group_building'] = 'Gruppenbildung ';
$string['evaluation'] = 'Auswertung';
$string['group_overview'] = 'Übersicht gebildeter Gruppen';
$string['max_group_size_not_reached'] = 'Maximale Gruppengröße wurde bei folgenden Gruppen nicht erreicht:';
$string['your_group'] = 'Deine Gruppe ';
$string['students_grouping_single'] = 'Studierende zur Gruppenbildung.';
$string['students_grouping_multiple'] = 'Studierende zur Gruppenbildung.';
$string['students_commited_single'] = 'Studierende davon haben ihre Antworten schon endgültig abgegeben.';
$string['students_commited_multiple'] = 'Studierende davon hat seine Antworten schon endgültig abgegeben.';
$string['commited_not_completed'] = 'von den fest abgegebenen Fragebögen sind nicht vollständig.';
$string['completed_questionnaire'] = 'vollständig beantwortete Fragebögen.';

$string['emailnotifications_info'] = 'Sie werden via Moodle benachrichtigt, wenn sie abgeschlossen ist.';
$string['onlyactivestudents_info'] = 'Zur Gruppenbildung werden ausschließlich Studierende betrachtet werden, die mind. eine Frage beantwortet haben. Sie können das in den {$a->url} anpassen.';
$string ['starttime'] = 'Startzeit';
$string ['endtime'] = 'Endzeit';

$string['excellent'] = 'sehr gut';
$string['none'] = 'gar nicht';
$string['bad'] = 'schlecht';

$string['cron_job_not_running'] = 'Um die gestarteten Anfragen zur Gruppenbildung zu bearbeiten läuft im Hintergrund ein Cron-Daemon. Leider reagiert dieser in der letzten Zeit nicht oder ist gar außer Betrieb. Sollten Sie diese Meldung nach mehr als 24 Stunden noch immer sehen, kontaktieren Sie bitte den Systemadministrator.';

$string['groupformation_message'] = 'Die Gruppenformation ist abgeschlossen. Sie können sich nun das Ergebnis anschauen';
$string['groupformation_message_subject'] = 'Gruppenformation abgeschlossen';
$string['groupformation_message_contexturlname'] = 'Resultate';

$string['import'] = 'Import';
$string['export'] = 'Export';
$string['export_answers'] = 'Sie können die Antworten mit anonymisierten Teilnehmerkennungen hier herunterladen: ';
$string['export_users'] = 'Sie können die Teilnehmer-bezogenen Daten mit anonymisierten Teilnehmerkennungen hier herunterladen: ';
$string['export_groups'] = 'Sie können die Gruppen mit anonymisierten Teilnehmerkennungen hier herunterladen: ';
$string['export_group_users'] = 'Sie können die Gruppen-Nutzer-Zuordnungen mit anonymisierten Teilnehmerkennungen hier herunterladen: ';
$string['export_logging'] = 'Sie können die Loggingdaten mit anonymisierten Teilnehmerkennungen hier herunterladen: ';
$string['export_description_no'] = 'Erst wenn du Antworten in exportfähigen Kategorien gegeben hast, kannst du sie hier exportieren.';
$string['export_description_yes'] = 'Klicke auf den folgenden Button, um deine Antworten für diesen Fragebogen zu exportieren.';
$string['import_description_yes'] = 'Klicke auf den folgenden Button, um Antworten von früheren Fragebögen zu importieren.';
$string['import_description_no'] = 'Es ist nicht möglich Antworten zu importieren, da der Fragebogen nicht mehr verfügbar ist oder bereits abgegeben wurde.';
$string['import_form_description'] = 'Du kannst Antworten von früheren Fragebögen importieren, indem du hier deine Antworten im passenden Format hochlädst (z.B. answers.xml).';

$string['file_error'] = 'Du musst eine *.xml-Datei hochladen.';
$string['failed_import'] = 'Der Import ist fehlgeschlagen. Das Format der Datei war nicht korrekt. Bitte lade eine Datei mit exportierten Antworten hoch.';
$string['successful_import'] = 'Der Import war erfolgreich. Du kannst deine Antworten im Tab "Fragebogen" einsehen.';

$string['archive_activity_task'] = 'Archivierung alter Aktivitäten';
$string['archived_activity_answers'] = 'Die Aktitivät ist archiviert worden. Ihre Antworten sind nicht mehr gespeichert und es ist keine Interaktion mehr möglich.';
$string['archived_activity_admin'] = 'Die Aktitivät ist archiviert worden. Es ist keine Interaktion mehr möglich.';

$string['students_selected']='Teilnehmer ausgewählt';
$string['drop_selection']='Selektion aufheben';

$string['no_evaluation_text'] = 'Es gibt für diese Aktivität keine Auswertung.';
$string['no_evaluation_ready'] = 'Es gibt keine Auswertung, da nicht alle Fragen beantwortet wurden. Erst wenn alle Fragen beantwortet wurden, gibt eine Auswertung ihrer Antworten';
$string['eval_final_text'] = 'Die Vergleichswerte basieren auf derzeit {$a->percent}% gegebener Antworten ({$a->completed} von {$a->coursesize} Personen haben bisher geantwortet).';

$string['eval_first_page_title']="Allgemeine Informationen";
$string['eval_first_page_text'] = "Sie erhalten nun eine individuelle Rückmeldung auf die Antworten, die Sie im Fragebogen gegeben haben. Dabei werden jeweils mehrere Antworten, die sich auf dasselbe Thema beziehen, zu einem Mittelwert zusammengefasst und mit einer Vergleichsstichprobe anderer Studierender verglichen. Daraus wird ein sogenannter Prozentrang berechnet; dieser bewertet nicht, ob eine Eigenschaft gut oder schlecht ist, sondern nur, wie häufig solche Werte unter Studierenden vorkommen. Ein Prozentrang von 10 bedeutet, dass 10% der Vergleichsstichprobe ein gleiches oder kleineres Ergebnis hatten; ein Prozentrang von 90 hingegen bedeutet, dass 90% der Vergleichsstichprobe ein gleiches oder kleineres Ergebnis hatten.
\\n\\n
Zunächst erhalten Sie Rückmeldung zu fünf Persönlichkeitseigenschaften, die als relativ stabil, das heißt unabhängig von bestimmten Situationen, betrachtet werden können: Extraversion, Neurotizismus, Gewissenhaftigkeit, soziale Verträglichkeit und Offenheit für Erfahrungen.
\\n\\n
Anschließend erhalten Sie Rückmeldung zu vier Dimensionen von Motivation, die sich auf die aktuelle Lehrveranstaltung oder Aufgabe beziehen und insofern von Situation zu Situation variieren können: Interesse, Herausforderung, Erfolgswahrscheinlichkeit und Misserfolgsbefürchtung.";

$string['eval_name_big5'] = 'Persönlichkeit';
$string['eval_name_fam'] = 'Motivation';

$string['eval_max_caption_neurotizismus']='emotional stabil';
$string['eval_min_caption_neurotizismus']='emotional labil';
$string['eval_max_caption_extraversion']='extravertiert';
$string['eval_min_caption_extraversion']='introvertiert';
$string['eval_max_caption_gewissenhaftigkeit']='gewissenhaft';
$string['eval_min_caption_gewissenhaftigkeit']='nachlässig';
$string['eval_max_caption_vertraeglichkeit']='verträglich';
$string['eval_min_caption_vertraeglichkeit']='kompetitiv';
$string['eval_max_caption_offenheit']='offen';
$string['eval_min_caption_offenheit']='konservativ';
$string['eval_cutoff_caption_extraversion']='Extraversion';
$string['eval_cutoff_caption_neurotizismus']='Neurotizismus';
$string['eval_cutoff_caption_gewissenhaftigkeit']='Gewissenhaftigkeit';
$string['eval_cutoff_caption_vertraeglichkeit']='Soziale Verträglichkeit';
$string['eval_cutoff_caption_offenheit']='Offenheit für Erfahrung';

$string['eval_max_text_extraversion']='Introversion und Extraversion sind zwei Pole einer Persönlichkeitseigenschaft, die durch die Interaktion mit der Umwelt charakterisiert wird. Introversion bezeichnet dabei eine nach innen, Extraversion eine nach außen gewandte Haltung.';
$string['eval_min_text_extraversion']='Introversion und Extraversion sind zwei Pole einer Persönlichkeitseigenschaft, die durch die Interaktion mit der Umwelt charakterisiert wird. Introversion bezeichnet dabei eine nach innen, Extraversion eine nach außen gewandte Haltung.';
$string['eval_max_text_neurotizismus']='Der Persönlichkeitsfaktor „Neurotizismus“ spiegelt individuelle Unterschiede im Erleben von negativen Emotionen wider und wird auch als emotionale Labilität bezeichnet. Der Gegenpol wird auch als emotionale Stabilität bezeichnet.';
$string['eval_min_text_neurotizismus']='Der Persönlichkeitsfaktor „Neurotizismus“ spiegelt individuelle Unterschiede im Erleben von negativen Emotionen wider und wird auch als emotionale Labilität bezeichnet. Der Gegenpol wird auch als emotionale Stabilität bezeichnet.';
$string['eval_max_text_gewissenhaftigkeit']='Der Faktor „Gewissenhaftigkeit“ beschreibt in erster Linie den Grad an Selbstkontrolle, Genauigkeit und Zielstrebigkeit.';
$string['eval_min_text_gewissenhaftigkeit']='Der Faktor „Gewissenhaftigkeit“ beschreibt in erster Linie den Grad an Selbstkontrolle, Genauigkeit und Zielstrebigkeit.';
$string['eval_max_text_vertraeglichkeit']='Soziale Verträglichkeit bezieht sich auf das Verhalten gegenüber anderen Menschen.';
$string['eval_min_text_vertraeglichkeit']='Soziale Verträglichkeit bezieht sich auf das Verhalten gegenüber anderen Menschen.';
$string['eval_max_text_offenheit']='Mit dem Faktor „Offenheit für Erfahrungen“ wird das Interesse und das Ausmaß der Beschäftigung mit neuen Erfahrungen, Erlebnissen und Eindrücken beschrieben.';
$string['eval_min_text_offenheit']='Mit dem Faktor „Offenheit für Erfahrungen“ wird das Interesse und das Ausmaß der Beschäftigung mit neuen Erfahrungen, Erlebnissen und Eindrücken beschrieben.';

$string['eval_max_caption_herausforderung']='Herausforderung';
$string['eval_min_caption_herausforderung']='Herausforderung';
$string['eval_max_caption_interesse']='Interesse';
$string['eval_min_caption_interesse']='Interesse';
$string['eval_max_caption_erfolgswahrscheinlichkeit']='Erfolgswahrscheinlichkeit';
$string['eval_min_caption_erfolgswahrscheinlichkeit']='Erfolgswahrscheinlichkeit';
$string['eval_max_caption_misserfolgsbefuerchtung']='Misserfolgsbefürchtung';
$string['eval_min_caption_misserfolgsbefuerchtung']='Misserfolgsbefürchtung';

$string['eval_max_text_herausforderung']='Die Herausforderung ist eine Dimension der Motivation, die ausdrückt, wie sehr die aktuelle Lehrveranstaltung oder Aufgabe überhaupt leistungsthematisch interpretiert wird, also ob Sie Ihre eigenen Fähigkeiten und Ihre Tüchtigkeit erproben oder unter Beweis stellen wollen.';
$string['eval_min_text_herausforderung']='Die Herausforderung ist eine Dimension der Motivation, die ausdrückt, wie sehr die aktuelle Lehrveranstaltung oder Aufgabe überhaupt leistungsthematisch interpretiert wird, also ob Sie Ihre eigenen Fähigkeiten und Ihre Tüchtigkeit erproben oder unter Beweis stellen wollen.';
$string['eval_max_text_interesse']='Mit dem Faktor „Offenheit für Erfahrungen“ wird das Interesse und das Ausmaß der Beschäftigung mit neuen Erfahrungen, Erlebnissen und Eindrücken beschrieben.';
$string['eval_min_text_interesse']='Mit dem Faktor „Offenheit für Erfahrungen“ wird das Interesse und das Ausmaß der Beschäftigung mit neuen Erfahrungen, Erlebnissen und Eindrücken beschrieben.';
$string['eval_max_text_erfolgswahrscheinlichkeit']='Erfolgswahrscheinlichkeit enthält Annahmen darüber, wie sicher man sich ist, in der aktuellen Lehrveranstaltung oder Aufgabe gut abzuschneiden. Hohe Erfolgswahrscheinlichkeit kann daraus erwachsen, dass man sich als hinreichend fähig einschätzt oder die Aufgabe generell für leicht hält.';
$string['eval_min_text_erfolgswahrscheinlichkeit']='Erfolgswahrscheinlichkeit enthält Annahmen darüber, wie sicher man sich ist, in der aktuellen Lehrveranstaltung oder Aufgabe gut abzuschneiden. Hohe Erfolgswahrscheinlichkeit kann daraus erwachsen, dass man sich als hinreichend fähig einschätzt oder die Aufgabe generell für leicht hält.';
$string['eval_max_text_misserfolgsbefuerchtung']='Bei der Misserfolgsbefürchtung handelt es sich um eine Form von Motivation, die sich aus der Angst vor Misserfolg ergibt, verbunden mit der Annahme, durch den Druck der Situation nicht optimal lernen zu können.';
$string['eval_min_text_misserfolgsbefuerchtung']='Bei der Misserfolgsbefürchtung handelt es sich um eine Form von Motivation, die sich aus der Angst vor Misserfolg ergibt, verbunden mit der Annahme, durch den Druck der Situation nicht optimal lernen zu können.';

$string['eval_text_big5_extraversion_3']='Ihre Antworten deuten auf eine hohe Ausprägung in Extraversion hin. Entsprechend tendieren Sie vermutlich eher zu Geselligkeit, aktivem, gesprächigen Verhalten, Optimismus und Herzlichkeit, sowie einer höheren Empfänglichkeit für Anregungen und Aufregungen.';
$string['eval_text_big5_extraversion_2']='Ihre Antworten deuten auf eine mittlere Ausprägung in Extraversion hin. Entsprechend sind Sie vermutlich in eher moderatem Umfang gesprächig, nicht besonders dominant und enthusiastisch.';
$string['eval_text_big5_extraversion_1']='Ihre Antworten deuten auf eine niedrige Ausprägung in Extraversion hin. Entsprechend tendieren Sie vermutlich eher zu introvertiertem, zurückhaltenden Verhalten bei sozialen Interaktionen und sind gerne allein und unabhängig. Introvertierte Personen werden oft als ruhig, still und zurückhaltend beschrieben.';
$string['eval_text_big5_neurotizismus_3']='Sie scheinen eine eher hohe Ausprägung in Neurotizismus aufzuweisen. Demnach erleben Sie häufiger Angst, Nervosität, Anspannung, Trauer, Unsicherheit und Verlegenheit. Zudem bleiben diese Empfindungen bei Ihnen länger bestehen und werden leichter ausgelöst. Sie tendieren zu mehr Sorgen um Ihre Gesundheit, neigen zu unrealistischen Ideen und haben Schwierigkeiten, in Stresssituationen angemessen zu reagieren.';
$string['eval_text_big5_neurotizismus_2']='Sie scheinen eine mittelmäßige Ausprägung in Neurotizismus aufzuweisen. Demnach erleben Sie weder besonders häufig noch besonders selten Angst, Nervosität, Anspannung, Trauer, Unsicherheit und Verlegenheit. In Stresssituationen sind Sie weder besonders anfällig für Probleme noch in besonderem Maße robust dagegen.';
$string['eval_text_big5_neurotizismus_1']='Sie scheinen eine niedrige Ausprägung in Neurotizismus aufzuweisen. Demnach sind Sie eher ruhig, zufrieden, stabil, entspannt und sicher und können mit Stresssituationen oft besser umgehen.';
$string['eval_text_big5_gewissenhaftigkeit_3']='Sie scheinen hohe Gewissenhaftigkeitswerte aufzuweisen. Entsprechend kann angenommen werden, dass sie organisiert, sorgfältig, planend, effektiv, verantwortlich, zuverlässig und überlegt handeln.';
$string['eval_text_big5_gewissenhaftigkeit_2']='Sie scheinen mittelmäßige Gewissenhaftigkeitswerte aufzuweisen. Entsprechend kann angenommen werden, dass Sie ein ausgeglichenes Verhältnis zwischen Gewissenhaftigkeit und Lockerheit aufweisen und weder besonders streng organisiert noch unorganisiert sind.';
$string['eval_text_big5_gewissenhaftigkeit_1']='Sie scheinen eher niedrige Gewissenhaftigkeitswerte aufzuweisen. Entsprechend kann man darauf schließen, dass sie tendenziell spontan sind und eher unsorgfältig und ungenau handeln.';
$string['eval_text_big5_vertraeglichkeit_3']='Ihre hohen Werte in Verträglichkeit weisen darauf hin, dass Sie grundsätzlich eher altruistisch sind. Sie begegnen anderen mit Verständnis, Wohlwollen und Mitgefühl, sind bemüht, anderen zu helfen und gehen meist davon aus, dass diese sich ebenso hilfsbereit verhalten werden. Sie neigen zu zwischenmenschlichem Vertrauen, zu Kooperation und Nachgiebigkeit.';
$string['eval_text_big5_vertraeglichkeit_2']='Ihre mittleren Werte in Verträglichkeit weisen darauf hin, dass Sie weder stark egozentrisch noch altruistisch veranlagt sind. Man würde Sie weder als besonders misstrauisch noch als besonders schnell vertrauensvoll, weder als extrem nachgiebig noch als extrem stur beschreiben.';
$string['eval_text_big5_vertraeglichkeit_1']='Ihre niedrigen Werte in Verträglichkeit weisen darauf hin, dass Sie grundsätzlich eher streitlustig, egozentrisch und misstrauisch gegenüber den Absichten anderer Menschen sind. Sie verhalten sich eher kompetitiv als kooperativ, besitzen die Fähigkeit, für eigene Interessen zu kämpfen und sind weniger nachgiebig.';
$string['eval_text_big5_offenheit_3']='Sie haben eher hohe Offenheitswerten erzielt. Dies spricht dafür, dass Sie ein reges Fantasieleben haben sowie an vielen persönlichen und öffentlichen Vorgängen interessiert sind. Man beschreibt solche Personen oft als intellektuell, experimentierfreudig und künstlerisch interessiert, sie verhalten sich häufig unkonventionell und bevorzugen Abwechslung.';
$string['eval_text_big5_offenheit_2']='Sie haben mittlere Offenheitswerten erzielt. Dies spricht dafür, dass Sie weder zu besonders konventionellen noch besonders unkonventionellen Einstellungen und Verhalten neigen. Visionärer, fantasievoller Veranlagung und pragmatischer Hier-jetzt-Bezug halten sich offenbar bei Ihnen die Waage.';
$string['eval_text_big5_offenheit_1']='Sie haben eher niedrige Offenheitswerte erzielt. Dies spricht dafür, dass Sie eher zu konventionellem Verhalten und zu konservativen Einstellungen neigen. Sie ziehen Bekanntes und Bewährtes dem Neuen vor und nehmen ihre emotionalen Reaktionen eher gedämpft wahr, handeln pragmatisch mit Hier-jetzt-Bezug.';

$string['eval_text_fam_herausforderung_3']='Die aktuelle Lehrveranstaltung oder Aufgabe wurde von Ihnen als besonders herausfordernd eingeschätzt. Bei fremdgesteuerten Aufgaben kann dies negative, bei selbstgesteuerten Aufgaben hingegen jedoch sehr positive Auswirkungen haben.';
$string['eval_text_fam_herausforderung_2']='Die aktuelle Lehrveranstaltung oder Aufgabe wurde von Ihnen als weder besonders herausfordernd noch einfach eingeschätzt.';
$string['eval_text_fam_herausforderung_1']='Die aktuelle Lehrveranstaltung oder Aufgabe wurde von Ihnen als wenig herausfordernd eingeschätzt. Bei fremdgesteuerten Aufgaben kann dies positive, bei selbstgesteuerten Aufgaben hingegen jedoch sehr negative Auswirkungen haben.';
$string['eval_text_fam_interesse_3']='Sie scheinen ein hohes Interesse am Inhalt der aktuellen Lehrveranstaltung oder Aufgabe zu haben, was gut für Ihr Lernen sein sollte, sofern der Prozess in höherem Maße selbstgesteuert stattfindet.';
$string['eval_text_fam_interesse_2']='Sie scheinen ein moderates Interesse am Inhalt der aktuellen Lehrveranstaltung oder Aufgabe zu haben.';
$string['eval_text_fam_interesse_1']='Sie scheinen ein niedriges Interesse am Inhalt der aktuellen Lehrveranstaltung oder Aufgabe zu haben, was schlecht für Ihr Lernen sein kann, sofern der Prozess in höherem Maße selbstgesteuert stattfindet.';
$string['eval_text_fam_erfolgswahrscheinlichkeit_3']='Den eigenen Erfolg bei der aktuellen Lehrveranstaltung oder Aufgabe haben Sie als hoch wahrscheinlich eingeschätzt. Üblicherweise sollte dies Ihren Lernprozess begünstigen.';
$string['eval_text_fam_erfolgswahrscheinlichkeit_2']='Den eigenen Erfolg bei der aktuellen Lehrveranstaltung oder Aufgabe haben Sie als moderat wahrscheinlich eingeschätzt, was weder besonders guten noch schlechten Einfluss auf Ihren Lernprozess haben sollte.';
$string['eval_text_fam_erfolgswahrscheinlichkeit_1']='Den eigenen Erfolg bei der aktuellen Lehrveranstaltung oder Aufgabe haben Sie als niedrig wahrscheinlich eingeschätzt. Üblicherweise wirkt sich dies negativ auf Ihren Lernprozess aus.';
$string['eval_text_fam_misserfolgsbefuerchtung_3']='Ihre Ergebnisse legen die Annahme nahe, dass die Misserfolgsbefürchtung bei Ihnen hoch ausgeprägt ist. Dies könnte sich aus einer allgemeinen Furcht vor Misserfolg oder Prüfungsängstlichkeit ableiten, sich aber auch auf speziellere, situative Faktoren der aktuellen Lehrveranstaltung oder Aufgabe beziehen. Im Allgemeinen geht man davon aus, dass sich Misserfolgsbefürchtungen negativ auf den Lernerfolg auswirken.';
$string['eval_text_fam_misserfolgsbefuerchtung_2']='Ihre Ergebnisse legen die Annahme nahe, dass die Misserfolgsbefürchtung bei Ihnen moderat ausgeprägt ist. Dies könnte sich aus einer allgemeinen Furcht vor Misserfolg oder Prüfungsängstlichkeit ableiten, sich aber auch auf speziellere, situative Faktoren der aktuellen Lehrveranstaltung oder Aufgabe beziehen. Im Allgemeinen geht man davon aus, dass sich Misserfolgsbefürchtungen negativ auf den Lernerfolg auswirken.';
$string['eval_text_fam_misserfolgsbefuerchtung_1']='Ihre Ergebnisse legen die Annahme nahe, dass die Misserfolgsbefürchtung bei Ihnen niedrig ausgeprägt ist. Dies könnte sich aus einer allgemein nicht vorhandenen Furcht vor Misserfolg oder Prüfungsängstlichkeit ableiten, sich aber auch auf speziellere, situative Faktoren der aktuellen Lehrveranstaltung oder Aufgabe beziehen. Im Allgemeinen geht man davon aus, dass sich fehlende Misserfolgsbefürchtungen positiv auf den Lernerfolg auswirken.';

$string['eval_caption_user']='Teilnehmer';
$string['eval_caption_group']='Gruppe';
$string['eval_caption_course']='Kurs';


$string['consent_alert_message'] = 'Sie müssen den Nutzungsbedingungen zustimmen, um den Fragebogen zu beantworten. Lesen und akzeptieren sie die Nutzungsbedingungen, bevor sie auf "Weiter" klicken.';
$string['consent_opt_in'] = 'Einwilligung (opt-in)';
$string['consent_message'] = '<ul><li>
Der Kursleiter/in oder Dozent/in sieht meine persönlichen Angaben für die Gruppenformation nicht (nur ich selbst
erhalten Feedback zu meinen Persönlichkeitsmerkmalen, sowie zum Durchschnittsvergleich mit der Gruppe).
</li><li>
Meine Angaben werden am Kursende - spätestens nach 360 Tagen gelöscht.</li></ul>';
$string['consent_agree']='Ich bin einverstanden';

$string['questionnaire_delete'] = 'Zustimmung widerrufen';
$string['groupsize'] = 'Gruppengröße: ';
$string['unselect_all'] = 'Auswahl aufheben';
$string['students_selected'] = 'Studenten in Auswahl';
$string['select_info'] = 'Wählen Sie die Gruppenmitglieder aus, um Gruppen zu bearbeiten';

$string['topic_group_info'] = 'Euer Thema ist';
$string['topic'] = "Thema";

















