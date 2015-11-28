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
 * Folder module renderer
 *
 * @package   mod_folder
 * @copyright 2009 Petr Skoda  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class mod_folder_renderer extends plugin_renderer_base {

    /**
     * Returns html to display the content of mod_folder
     * (Description, folder files and optionally Edit button)
     *
     * @param stdClass $folder record from 'folder' table (please note
     *     it may not contain fields 'revision' and 'timemodified')
     * @return string
     */
    public function display_folder(stdClass $folder) {
        $output = '';
        $folderinstances = get_fast_modinfo($folder->course)->get_instances_of('folder');
        if (!isset($folderinstances[$folder->id]) ||
                !($cm = $folderinstances[$folder->id]) ||
                !($context = context_module::instance($cm->id))) {
            // Some error in parameters.
            // Don't throw any errors in renderer, just return empty string.
            // Capability to view module must be checked before calling renderer.
            return $output;
        }

        if (trim($folder->intro)) {
            if ($folder->display != FOLDER_DISPLAY_INLINE) {
                $output .= $this->output->box(format_module_intro('folder', $folder, $cm->id),
                        'generalbox', 'intro');
            } else if ($cm->showdescription) {
                // for "display inline" do not filter, filters run at display time.
                $output .= format_module_intro('folder', $folder, $cm->id, false);
            }
        }

        $foldertree = new folder_tree($folder, $cm);
        if ($folder->display == FOLDER_DISPLAY_INLINE) {
            // Display module name as the name of the root directory.
            $foldertree->dir['dirname'] = $cm->get_formatted_name();
        }
        $output .= $this->output->box($this->render($foldertree),
                'generalbox foldertree');

        // Do not append the edit button on the course page.
        if ($folder->display != FOLDER_DISPLAY_INLINE && has_capability('mod/folder:managefiles', $context)) {
            $output .= $this->output->container(
                    $this->output->single_button(new moodle_url('/mod/folder/edit.php',
                    array('id' => $cm->id)), get_string('edit')),
                    'mdl-align folder-edit-button');
        }
        return $output;
    }

    public function render_folder_tree(folder_tree $tree) {
        static $treecounter = 0;

        $content = '';
        $id = 'folder_tree'. ($treecounter++);
        $content .= '<div id="'.$id.'" class="filemanager">';
        $content .= $this->htmllize_tree($tree, array('files' => array(), 'subdirs' => array($tree->dir)));
        $content .= '</div>';
        $showexpanded = true;
        if (empty($tree->folder->showexpanded)) {
            $showexpanded = false;
        }
        $this->page->requires->js_init_call('M.mod_folder.init_tree', array($id, $showexpanded));
        return $content;
    }

    /**
     * Internal function - creates htmls structure suitable for YUI tree.
     */
    protected function htmllize_tree($tree, $dir) {
        global $CFG, $USER, $COURSE, $DB, $PAGE;

        if (empty($dir['subdirs']) and empty($dir['files'])) {
            return '';
        }
        
		
			
		
		$result = '<ul>';
        foreach ($dir['subdirs'] as $subdir) {
            $image = $this->output->pix_icon(file_folder_icon(24), $subdir['dirname'], 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
						html_writer::tag('span', s($subdir['dirname']), array('class' => 'fp-filename'));
					
					// ==================Trust Model Directorio==========================
					//html_writer::tag('span', implode(' | ', $comandoTrust), array('class'=>'commands'));
					//====================================================
					
			$filename = html_writer::tag('div', $filename, array('class' => 'fp-filename-icon'));
			
			$result .= html_writer::tag('li', $filename. $this->htmllize_tree($tree, $subdir));

        }
        foreach ($dir['files'] as $file) {
            $filename = $file->get_filename();
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                    $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $filename, false);
            if (file_extension_in_typegroup($filename, 'web_image')) {
                $image = $url->out(false, array('preview' => 'tinyicon', 'oid' => $file->get_timemodified()));
                $image = html_writer::empty_tag('img', array('src' => $image));
            } else {
                $image = $this->output->pix_icon(file_file_icon($file, 24), $filename, 'moodle');
            }
			
			// ==================Trust Model Folder file==========================
			if(!isguestuser()){
				$user_id = $USER->id;
				$course_id = $COURSE->id;
				$f_file_id= $file->get_id();
				$f_file_user=$file->get_userid();
				$like= get_string('like', 'block_trust_model');
				$not_like= get_string('not_like', 'block_trust_model');
				$comandoTrust= [];
				//Controla, no calificar un recurso creado por el mismo usuario
				if($user_id != $f_file_user){
					//Verifica si ya evaluo el usuario 
					$count = $DB->count_records('trust_f1w1_history_folder', array('user_id' => $user_id,'course_id' => $course_id,'folder_file_id' => $f_file_id));
					if($count==0){//Ingresa si no existe un registro, no evaluo 
						$url = $PAGE->url;
						$comandoTrust[] = html_writer::link(new moodle_url('/blocks/trust_model/F1W1_Previous_Experience.php', array('opc' => 4, 'u' => $user_id, 'c' => $course_id, 'ff' => $f_file_id,'ffu' => $f_file_user, 'mc' => +1, 'url' => $url)),$like);
						$comandoTrust[] = html_writer::link(new moodle_url('/blocks/trust_model/F1W1_Previous_Experience.php', array('opc' => 4, 'u' => $user_id, 'c' => $course_id,'ff' => $f_file_id,'ffu' => $f_file_user, 'mc' => -1, 'url' => $url)),$not_like);
					}
				}
			}else{
				$comandoTrust= [];
			}
			//=======================================================================

            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
						html_writer::tag('span', $filename, array('class' => 'fp-filename')).
						
			// ==================Trust Model Folder file==========
					html_writer::tag('span', implode(' | ', $comandoTrust), array('class'=>'commands'));
				
			//====================================================
						
            $filename = html_writer::tag('span',
                    html_writer::link($url->out(false, array('forcedownload' => 1)), $filename),
                    array('class' => 'fp-filename-icon'));
            $result .= html_writer::tag('li', $filename);
        }
        $result .= '</ul>';

        return $result;
    }
}

class folder_tree implements renderable {
    public $context;
    public $folder;
    public $cm;
    public $dir;

    public function __construct($folder, $cm) {
        $this->folder = $folder;
        $this->cm     = $cm;

        $this->context = context_module::instance($cm->id);
        $fs = get_file_storage();
        $this->dir = $fs->get_area_tree($this->context->id, 'mod_folder', 'content', 0);
    }
}
