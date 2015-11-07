<?php
/**
 *
 */

class mod_groupformation_template_builder{
    // Pfad zum Template
    private $path;

    // Name des Templates, in dem Fall das Standardtemplate.
    private $template = 'default';

    /**
     * Enthält die Variablen, die in das Template eingebetet
     * werden sollen.
     */
    private $_ = array();


    public function __construct(){
        global $CFG;
        $this->path = $CFG->dirroot . '/mod/groupformation/templates';
    }

    /**
     * Ordnet eine Variable einem bestimmten Schluessel zu.
     *
     * @param String $key Schluessel
     * @param String $value Variable
     */
    public function assign($key, $value){
        $this->_[$key] = $value;
    }


    /**
     * Setzt den Namen des Templates.
     *
     * @param String $template Name des Templates.
     */
    public function set_template($template = 'default'){
        $this->template = $template;
    }

    /**
     * Das Template-File laden und zurückgeben
     *
     * @param string $tpl Der Name des Template-Files (falls es nicht vorher
     * 						über steTemplate() zugewiesen wurde).
     * @return string Der Output des Templates.
     */
    public function load_template(){
        $tpl = $this->template;
        // Pfad zum Template erstellen & überprüfen ob das Template existiert.
        $file = $this->path . DIRECTORY_SEPARATOR . $tpl . '.php';
        $exists = file_exists($file);

        if ($exists){

            // Der Output des Scripts wird n einen Buffer gespeichert, d.h.
            // nicht gleich ausgegeben.
            ob_start();

            // Das Template-File wird eingebunden und dessen Ausgabe in
            // $output gespeichert.
            include $file;
            $output = ob_get_contents();
            ob_end_clean();

            // Output zurückgeben.
            return $output;

        }
        else {
            // Template-File existiert nicht-> Fehlermeldung.
            return 'could not find template';
        }
    }

}