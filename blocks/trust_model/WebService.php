<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/blocks/trust_model/lib.php');
global $DB, $USER;
$opc = optional_param('opc', '',PARAM_TEXT);
if (isguestuser()) {
	redirect($CFG->wwwroot);
}

$url = new moodle_url('/blocks/trust_model/WebService.php');
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_context(context_user::instance($USER->id));
$tm = get_string('pluginname', 'block_trust_model');
$PAGE->navbar->add($tm);
$security = get_string('webService', 'block_trust_model');
$PAGE->navbar->add($security);
$PAGE->set_title("{$SITE->shortname}: $tm");
$PAGE->set_heading("{$SITE->shortname}: $tm");
echo $OUTPUT->header();
echo $OUTPUT->box_start();
echo html_writer::start_tag('div', array('class' => 'mdl-align'));
echo html_writer::tag('h4', get_string('externalTrust', 'block_trust_model'));
echo html_writer::end_tag('div');

if($opc=='connect'){
	$location=$_POST['locationWS'];
	$metodo=$_POST['functionWS'];
	$lstTrust=webService($location, $metodo);//Conectarce con el servicio Web
	if(is_array($lstTrust)){
		$lstUser=webServiceFilteredSave($lstTrust); //Actualizo la base de datos
		$cell1=  '<div style="overflow:hidden;">';
		$cell1.= '<label style="width: 25%; float:left;">'.get_string('webService', 'block_trust_model').':&nbsp;</label>';
		$cell1.= '<label style="width: 75%; float:left;">'.get_string('externalTrust', 'block_trust_model').'</label>';
		$cell1.= '<label style="width: 25%; float:left;">'.get_string('description', 'block_trust_model').':&nbsp;</label>';
		$cell1.= '<label style="width: 75%; float:left;">'.get_string('updateTrustExternal', 'block_trust_model').'</label>';
		$cell1.= '<label style="width: 25%; float:left;">'.get_string('numberTrustExternal', 'block_trust_model').':&nbsp;</label>';
		$cell1.= '<label style="width: 75%; float:left;">'.count($lstUser).'</label>';
		$cell1.= '</div>';
		
		$form  = '<div>';
		$form .= '<form method="post" action="'.$CFG->wwwroot.'/blocks/trust_model/WebService.php" >';
		$form .= $cell1;
		$form .= '<button  type="submit">'.get_string('exit', 'block_trust_model').'</button>';
		$form .= '</form></div>';
		echo $form;
	}else{
		echo get_string('error', 'block_trust_model').'<br>'.$lstTrust;
	}
}else{
	$form  = '<div>';
	$form .= '<form method="post" action="'.$CFG->wwwroot.'/blocks/trust_model/WebService.php?opc=connect" >';
	$form .= '<p align="justify">'.get_string('descriptionWebService', 'block_trust_model').'</p><br>';
	$form .=  '<div style="overflow:hidden;">';
	$form .= '<div style="width: 20%; float:left;">'.get_string('webServiceUri', 'block_trust_model').':&nbsp;</div>';
	$form .= '<div style="width: 80%; float:left;"><input size="50" name="locationWS" type="text" placeholder="'.get_string('webServiceLocation', 'block_trust_model').'"  required></div>';
	$form .= '<div style="width: 20%; float:left;">'.get_string('webServiceFunction', 'block_trust_model').':&nbsp;</div>';
	$form .= '<div style="width: 80%; float:left;"><input size="50" name="functionWS" type="text" placeholder="'.get_string('webServiceFunction', 'block_trust_model').'"  required></div>';
	$form .= '</div>';
	$form .= '<input id="id_submitbutton"  type="submit" value="'.get_string('connectServiceWeb', 'block_trust_model').'">';
	$form .= '</form></div>';
	echo $form;
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();



