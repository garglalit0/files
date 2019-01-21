<?php
/**
 * @file
 * Contains \Drupal\taxonomy_tree\Controller\FileFolderController.
 */

namespace Drupal\taxonomy_tree\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\user\Entity\User;
use Drupal\taxonomy_tree;

class FileFolderController extends ControllerBase {
public function loadFileFolder(){
	$file_data = \Drupal\file\Entity\File::load($file_id);
	$get_folder_id = $_POST['send_folderId'];
	$vid = 'file_folders';
	$parent_tid = $get_folder_id; // the parent term id
	//$get_folder_id = 308;
	$depth = 1; // 1 to get only immediate children, NULL to load entire tree
	$load_entities = FALSE; // True will return loaded entities rather than ids
	$child_tids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid, $parent_tid, $depth, $load_entities);
	$connection = Database::getConnection();
	$cms_doc_list = $connection->query("SELECT entity_id FROM {node__field_document_folder} WHERE field_document_folder_target_id = ('".$get_folder_id."')")->fetchAll();
	//kint($cms_doc_list);
		//die();
	$doc_file_ids = array();
	$cms_docs_data = array();
	foreach ($cms_doc_list as $cms_doc_listKey => $cms_doc_listValue) {
		$node_entity_id = $cms_doc_listValue->entity_id;
		$node_fid = $connection->query("SELECT fid FROM {file_usage} WHERE id = ('".$node_entity_id."')")->fetchField();
	$doc_file_ids[] = $node_fid;
	}
	foreach ($doc_file_ids as $doc_file_ids_key => $doc_file_ids_value) {
		$file_raw_data = \Drupal\file\Entity\File::load($doc_file_ids_value);
		$cms_docs_data[$doc_file_ids_key]['url'] = $file_raw_data->getFileUri();
		$cms_docs_data[$doc_file_ids_key]['name']  = $file_raw_data->getFilename();
		$cms_docs_data[$doc_file_ids_key]['author_id'] =$file_raw_data->getOwnerId();
		$cms_docs_data[$doc_file_ids_key]['created_time']  = $file_raw_data->getCreatedTime();
		//$cms_docs_data[$doc_file_ids_key]['mime_type']  =$file_raw_data->getMimeType();
		$cms_docs_data[$doc_file_ids_key]['mime_type']  = t('File');
		$cms_docs_data[$doc_file_ids_key]['file_size']  = $file_raw_data->getSize();

	}
	$file_data.='<div class="table-responsive folder_view_lowerpart">
                        <table class="table folderlisting">
                          <thead>
                            <tr>
                              <th class="abc">'.t('Name').'</th>
                              <th>'.t('Date').'</th>
                              <th>'.t('Author').'</th>
                              <th>'.t('Size').'</th>
                              <th>'.t('Opreations').'</th>
                            </tr>
                          </thead>';
if(!empty($child_tids)){
	foreach ($child_tids as $child_tidskey => $child_tidsvalue) {
		$time_stamp = $child_tidsvalue->changed;
		$date = date('d/m/Y', $time_stamp);
		$folder_id = $child_tidsvalue->tid;
		$author_id = \Drupal\taxonomy\Entity\Term::load($folder_id)->get('field_folder_author')->value;
		$author_data_values = User::load($author_id);
		$user_first_name = $author_data_values->get('field_name')->value;
		$user_sur_name = $author_data_values->get('field_surname')->value;
		$author_name = $user_first_name.' '.$user_sur_name;
		$file_data.='<tr>';
		$file_data.='<td class="folder" data-name="'.$child_tidsvalue->name.'" data-id="'.$child_tidsvalue->tid.'" id="child'.$child_tidsvalue->tid.'">'.$child_tidsvalue->name.'</td>';
		$file_data.='<td>'.$date.'</td>';
		$file_data.='<td>'.$author_name.'</td>';
		$file_data.='<td></td>';
		$file_data.='<td><a href="javascript:void(0);" class="rename_folder" data-folder-name="'.$child_tidsvalue->name.'" data-folder-id="'.$child_tidsvalue->tid.'">Edit</a></td>';
		$file_data.='</tr>';

	}
	
	if(!empty($cms_docs_data)){

foreach ($cms_docs_data as $cms_docs_datakey => $cms_docs_datavalue) {
		$auth_id = $cms_docs_datavalue["author_id"];
		$file_author_data_values = User::load($auth_id);
		$file_user_first_name = $file_author_data_values->get('field_name')->value;
		$file_user_sur_name = $file_author_data_values->get('field_surname')->value;
		$file_author_name = $file_user_first_name.' '.$file_user_sur_name;
		$file_time_stamp = $cms_docs_datavalue["created_time"];
		$file_date = date('d/m/Y', $file_time_stamp);
		$file_formated_size = formatSizeUnits($cms_docs_datavalue["file_size"]);
		$file_data.='<tr>';
		$file_data.='<td class="file"><a href="'.file_create_url($cms_docs_datavalue["url"]).'" target="_blank" download>'.$cms_docs_datavalue["name"].'</a></td>';
		$file_data.='<td>'.$file_date.'</td>';
		$file_data.='<td>'.$file_author_name.'</td>';
		$file_data.='<td>'.$file_formated_size.'</td>';
		$file_data.='<td></td>';
		$file_data.='</tr>';

	}

}


}
else{
	if(!empty($cms_docs_data)){

foreach ($cms_docs_data as $cms_docs_datakey => $cms_docs_datavalue) {
		$auth_id = $cms_docs_datavalue["author_id"];
		$file_author_data_values = User::load($auth_id);
		$file_user_first_name = $file_author_data_values->get('field_name')->value;
		$file_user_sur_name = $file_author_data_values->get('field_surname')->value;
		$file_author_name = $file_user_first_name.' '.$file_user_sur_name;
		$file_time_stamp = $cms_docs_datavalue["created_time"];
		$file_date = date('d/m/Y', $file_time_stamp);
		$file_formated_size = formatSizeUnits($cms_docs_datavalue["file_size"]);
		$file_data.='<tr>';
		$file_data.='<td class="file"><a href="'.file_create_url($cms_docs_datavalue["url"]).'" target="_blank" download>'.$cms_docs_datavalue["name"].'</a></td>';
		$file_data.='<td>'.$file_date.'</td>';
		$file_data.='<td>'.$file_author_name.'</td>';
		$file_data.='<td>'.$file_formated_size.'</td>';
		$file_data.='<td></td>';
		$file_data.='</tr>';

	}

}else{
	$file_data.='<tr>';
		$file_data.='<th colspan="5">'.t('No data found!!').'</th>';
		$file_data.='</tr>';
}
}
	$file_data.='</thead>
                        </table>
                      </div>';
	return new JsonResponse($file_data);
} 
}

/* function to get 
if the size is less than 1 MB, show the size in KB
if it's between 1 MB - 1 GB show it in MB
if it's larger - in GB
*/
function formatSizeUnits($bytes){
	if ($bytes >= 1073741824){
    	$bytes = number_format($bytes / 1073741824, 2) . ' GB';
    }elseif ($bytes >= 1048576){
       	$bytes = number_format($bytes / 1048576, 2) . ' MB';
    }elseif ($bytes >= 1024){
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    }elseif ($bytes > 1){
        $bytes = $bytes . ' bytes';
    }elseif ($bytes == 1){
        $bytes = $bytes . ' byte';
    }else{
        $bytes = '0 bytes';
    }
	return $bytes;
}