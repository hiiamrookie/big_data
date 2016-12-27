<?php
include(dirname(__FILE__) . '/inc/my_session.php');
include(dirname(__FILE__) . '/inc/model_require.php');
include(dirname(__FILE__) . '/inc/require_file.php');
include(dirname(__FILE__) . '/user_auth.php');
header('Content-type: text/html; charset=utf-8');

$dao = new Dao_Impl();

// 查询用户
$users = $dao->db->get_results('select m.*,n.teamname
from
(
select a.username,a.realname,b.companyname,c.depname,a.team,a.permissions 
from users a,
hr_company b,
hr_department c
where a.islive=1 and a.city=b.id and a.dep=c.id
) m left join hr_team n
on m.team=n.id');

//系统权限表
$permissons = $dao->db->get_results('select id,name from permissions');
$p_array = array();
if($permissons !== NULL){
	foreach ($permissons as $permisson){
		$p_array['sys' . $permisson->id] = $permisson->name;
	}
}

//部门权限表
$permissions_dep = $dao->db->get_results('select id,name from permissions_dep');
$p_d_array = array();
if($permissions_dep !== NULL){
	foreach ($permissions_dep as $permission_dep){
		$p_d_array['dep' . $permission_dep->id] = $permission_dep->name;
	}
}

if($users !== NULL){
	$s = '';
	foreach ($users as $user){
		$pa = array();
		$p = $user->permissions;
		if(empty($p)){
			$pa[] = '执行单发起人';
		}else{
			$p = explode('^', $p);
			foreach ($p as $pv){
				$pa[] = strpos($pv, 'sys') !== FALSE ? $p_array[$pv] : $p_d_array[$pv];
			}
		}
		$s .= $user->username . ',' . $user->realname . ',' . $user->companyname . ',' . $user->depname . ',' . $user->teamname . ',' . implode('，', $pa). "\n";
	}
	
}

echo $s;
$dao->db->disconnect();


