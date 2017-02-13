function js_escape(content){content=content.replace(/&/g,'&amp;');content=content.replace(/</g,'&lt;').replace(/>/g,'&gt;');content=content.replace(/\'/g,'&#39;').replace(/\"/g,'&quot;');return content;}
function PWD(a,m){var m
var a
if(!a){a=="6"}
if(m=="0"){var chars="1234506789";}
if(m=="1"){var chars="^[_]!#$%&()<=>{}|?@*+-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ~abcdefghijklmnopqrstuvwxyz";}
if(m=="2"){var chars="0123456789abcdef";}
if(m=="3"){var chars="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";}
if(m=="4"){var chars="101";}
if(m=="5"){var chars="435XH781$xr!x0fORoi-+^@~|_|)(/";}
if(m=="6"){var chars="AaĀāBbCcČčDdEeĒēFfGgĢģHhIiĪīJjKkĶķLlĻļMmNnŅņOoPpRrSsŠšTtUuŪūVvZzŽž";}
if(m=="7"){var chars="АаБбВвГгДдЕеЁёЖжЗзИиЙйКкЛлМмНнОоПпРрСсТтУуФфХхЦцЧчШшЩщЪъЫыЬьЭэЮюЯя";}
var pass=""
for(x=0;x<a;x++){rand=Math.random()*chars.length;genn=Math.round(rand);while(genn<=0){genn++;}
pass+=chars.charAt(genn);}
return pass;}
function show_message(message){alert(message);}
function check_actor(field, rules, i, options){
	if($("#actor").val() == ""){
		return "请至少选择一个执行人员";
	}
}
function set_token(id,token){
	$("#" + id).val(token);	
}

$(document).ready(function() {
	$("#btn_open").click(function(){
		$("html").toggleClass("open");
	});
});