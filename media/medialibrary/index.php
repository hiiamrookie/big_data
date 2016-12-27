<?php
include(dirname(dirname(dirname(__FILE__))) . '/inc/my_session.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/model_require.php');
include(dirname(dirname(dirname(__FILE__))) . '/inc/require_file.php');
include(dirname(dirname(dirname(__FILE__))) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (Security_Util::my_get('o')) {
case 'mtlist':
	media_list();
	break;
case 'mtadd':
	media_add();
	break;
case 'mtedit':
	media_edit();
	break;
case 'mtinfo':
	media_info();
	break;
default:
	User::no_permission();
}

function media_list() {
	$media_list = new Media_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page'))));
	echo $media_list->get_media_list_html();
	unset($media_list);
}

function media_add() {
	$media = new Media();
	echo $media->get_media_add_html();
	unset($media);
}

function media_edit() {
	$media = new Media(Security_Util::my_get('id'));
	echo $media->get_media_edit_html();
	unset($media);
}

function media_info() {
	$media = new Media(Security_Util::my_get('id'));
	echo $media->get_media_info_html();
	unset($media);
}
