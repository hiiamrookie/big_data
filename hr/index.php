<?php
include(dirname(dirname(__FILE__)) . '/inc/my_session.php');
include(dirname(dirname(__FILE__)) . '/inc/model_require.php');
include(dirname(dirname(__FILE__)) . '/inc/require_file.php');
include(dirname(dirname(__FILE__)) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

switch (strval(Security_Util::my_get('o'))) {
case 'changepwd':
	change_pwd();
	break;
case 'userlist':
	user_list();
	break;
case 'adduser':
	add_user();
	break;
case 'edituser':
	edit_user();
	break;
case 'companylist':
	company_list();
	break;
case 'addcompany':
	add_company();
	break;
case 'editcompany':
	edit_company();
	break;
case 'departmentlist':
	department_list();
	break;
case 'adddepartment':
	add_department();
	break;
case 'editdepartment':
	edit_department();
	break;
case 'teamlist':
	team_list();
	break;
case 'addteam':
	add_team();
	break;
case 'editteam':
	edit_team();
	break;
case 'getuser':
	getuser();
	break;
default:
	User::no_permission();
}

function change_pwd() {
	$user = new User();
	echo $user->get_change_pwd_html();
	unset($user);
}

function user_list() {
	$user_list = new User_List(
			array(
					'page' => intval(Security_Util::my_get('page')) === 0 ? 1
							: intval(Security_Util::my_get('page')),
					'city' => intval(Security_Util::my_get('city')),
					'dep' => intval(Security_Util::my_get('dep')),
					'team' => intval(Security_Util::my_get('team')),
					'search' => Security_Util::my_get('search')));
	echo $user_list->get_user_list_html();
	unset($user_list);
}

function add_user() {
	$other_user = new Other_User();
	echo $other_user->get_add_user_html();
	unset($other_user);
}

function edit_user() {
	$other_user = new Other_User(Security_Util::my_get('uid'));
	echo $other_user->get_edit_user_html();
	unset($other_user);
}

function company_list() {
	$city_list = new City_List();
	echo $city_list->get_city_list_html();
	unset($city_list);
}

function add_company() {
	$city = new City();
	echo $city->get_add_company_html();
	unset($city);
}

function edit_company() {
	$city = new City(Security_Util::my_get('id'));
	echo $city->get_edit_company_html();
	unset($city);
}

function department_list() {
	$dep_list = new Dep_List();
	echo $dep_list->get_department_list_html();
	unset($dep_list);
}

function add_department() {
	$dep = new Dep();
	echo $dep->get_add_department_html();
	unset($dep);
}

function edit_department() {
	$dep = new Dep(Security_Util::my_get('id'));
	echo $dep->get_edit_department_html();
	unset($dep);
}

function team_list() {
	$team_list = new Team_List();
	echo $team_list->get_team_list_html();
	unset($team_list);
}

function add_team() {
	$team = new Team();
	echo $team->get_add_team_html();
	unset($team);
}

function edit_team() {
	$team = new Team(Security_Util::my_get('id'));
	echo $team->get_edit_team_html();
	unset($team);
}


function getuser() {
	$q = Security_Util::my_get('q');
	$dao = new Dao_Impl();
	$s = '';
	$results = $dao->db
			->get_results(
					'SELECT username,realname FROM users WHERE islive=1 AND (username LIKE "%'
							. $q . '%" OR realname LIKE "%' . $q . '%")');
	if ($results !== NULL) {
		foreach ($results as $result) {
			$s .= $result->realname . ' (' . $result->username . ')' . "\n";
		}
	}
	echo $s;
}

