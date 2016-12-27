<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>网迈OA - 打印发票</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="pragma" content="no-cache"/> 
<meta http-equiv="Cache-Control" content="no-cache, must-revalidate"/> 
<meta http-equiv="expires" content="Wed, 26 Feb 1997 08:21:57 GMT"/>
<!--<link href="/css/style.css" rel="stylesheet" media="screen" type="text/css" />-->
<style>
@charset "utf-8";
/* Clear all predefined styles */

body, div, dl, dt, dd, ul, ol, li, h1, h2, h3, h4, h5, h6, pre, form, fieldset, input, textarea, p, blockquote, th, td {
	margin:0;
	color:#666;
	padding:0
}
body {
	font-size:20px;
	line-height:1.66;
	font-family:"\5B8B\4F53", Tahoma, sans-serif;
}
th, td {
	text-align:left;
	vertical-align:top
}
h1, h2, h3, h4, h5, h6 {
	font-size:1em;
	font-weight:normal
}
p {
	margin:0 0 1em 0
}
blockquote {
	margin:0 0 1em 0
}
a {
	text-decoration:none;
	outline:none;
	color:#666;
}
a:hover {
	text-decoration:none;
	color:#000;
}
img {
	border:0
}
ul {
	margin:0;
	padding:0;
	list-style:none
}
ol {
	padding:0 0 0 2em
}
dl, dt, dd {
	margin:0;
	padding:0
}
input, label, select, option, textarea, button, fieldset, legend {
	font-size:12px;
	color:#333;
	outline:none;
	font-family:"\5B8B\4F53", Tahoma, sans-serif
}
.text {
	border-color:#d2d2d0 #d9d9d7 #dededc #d9d9d7;
	border-style:solid;
	border-width:1px;
	padding:1px;
	font-size:12px;
	height:16px;
	line-height:16px;
	font-family:"\5B8B\4F53", Tahoma, sans-serif;
	background:#ececea url(../images/bg_text.png) repeat-x;
	vertical-align:middle;
}
.textarea {
	border-color:#d2d2d0 #d9d9d7 #dededc #d9d9d7;
	border-style:solid;
	border-width:1px;
	padding:1px;
	font-size:12px;
	line-height:16px;
	font-family:"\5B8B\4F53", Tahoma, sans-serif;
	background:#ececea url(../images/bg_text.png) repeat-x;
}
.text:focus, .textarea:focus, .select:focus {
	border-color:#dedede;
}
.text:active, .textarea:active, .select:active {
	border-color:#dedede;
}
input.file {
	vertical-align:middle;
}
input.radio, input.checkbox {
	margin-right:2px;
	vertical-align:-2px
}
input.radio, input.checkbox {
	margin-left:-1px
}
select.select {
	height:20px;
	padding:1px;
	display:inline-block;
	border-color:#d2d2d0 #d9d9d7 #dededc #d9d9d7;
	border-style:solid;
	border-width:1px;
	vertical-align:middle;
}
input.btn {
	height:20px;
	width:46px;
	padding:0;
	border:0;
	color:#fff;
	display:inline-block;
	vertical-align:middle;
	background:url(../images/bg_btn.gif) repeat-x;
}
textarea {
	padding:1px;
	overflow:auto;
}
 @-moz-document url-prefix() {
 textarea {
padding:0;
}
}
.fix:after {
	content:".";
	display:block;
	height:0;
	clear:both;
	visibility:hidden
}
.fix {
	display:inline-block;
	min-height:1%
}
*html .fix {
	zoom:1
}
.fix {
	display:block
}
.dis {
	display:block;
}
.undis {
	display:none;
}
.fleft {
	float:left;
}
object {
	display:block;
}
/* Clear styles end */

body {
	min-width:1000px;
	background:#fff url(../images/bg_side.png) repeat-y;
}
.w1000 {
	width:1000px;
	margin:0 auto;
}
/* header styles */

#logo {
	float:left;
	width:450px;
	height:65px;
	padding-top:29px;
	background:url(../images/logo.gif) no-repeat 0 29px;
}
#header img {
	display:block
}
/* main styles */

#main {
	background:#fff;
	padding:20px;
	margin-left:210px;
}
#copyright {
	padding:10px;
	width:190px;
	position:relative;
	bottom:0;
	left:0;
}
#copyright h6 {
	text-align:right;
	color:#fff;
	font-family:Arial, Helvetica, sans-serif;
}
#content {
	border:solid 1px #cfcfc8;
	background:url(../images/bg_tab.png) repeat-x;
}
#side {
	float:left;
	width:210px;
	position:absolute;
	top:0;
	left:0;
	height:100%;
}
.logo {
	text-align:right;
	padding:30px 15px 0 0;
}
div.Wdate {
	text-align:right;
	color:#999;
	padding:10px 15px 0 0;
}
div.SYS_Wdate {
	text-align:right;
	color:#999;
	padding:10px 15px 0 0;
}
#side_nav {
	width:179px;
	padding:10px 0 0 31px;
}
#side_nav h2 {
	margin:0;
	height:35px;
	line-height:30px;
	width:160px;
	color:#000;
	clear:both;
	text-align:right;
	padding-right:19px;
	cursor:default;
	background:url(../images/bg_side_h2.png) no-repeat;
}
#side_nav h2.current {
	background:url(../images/bg_side_h2_.png) no-repeat;
}
#side_nav ul {
	display:none;
	width:179px;
	padding:10px 0;
}
#side_nav ul li {
	width:160px;
	height:26px;
	padding-right:19px;
	overflow:hidden;
	text-align:right;
	line-height:26px;
}
#side_nav ul li.on {
	background:url(../images/bg_side_li.png) no-repeat 100% 50%;
}
#side_nav ul li a {
	color:#fff;
}
#side_nav ul li.on a {
	color:#cccc66;
}
.nav_top {
	line-height:22px;
	padding:10px 0;
}
.nav_top a {
	color:#ff6600;
}
.nav_top .my {
	height:30px;
	padding-top:8px;
	border-bottom:solid 1px #f0f0f0;
}
.nav_top .my b {
	text-align:right;
	float:right;
	font-weight:normal;
}
.diary {
	height:30px;
	padding-top:8px;
}
.nav_top img {
	vertical-align:middle;
	padding:0 4px;
}
.crumbs {
	line-height:24px;
	padding:2px 0 0 10px;
}
.tab {
	padding-top:6px;
	border-top:solid 1px #549e01;
	background:#80c500 url(../images/bg_line_80c500.png) repeat-x 0 100%;
}
.tab ul {
	height:31px;
	padding-left:5px;
	line-height:30px;
	background:url(../images/bg_line.png) repeat-x 0 100%;
}
.tab ul li {
	height:30px;
	float:left;
}
.tab ul li a {
	display:inline-block;
	height:30px;
	min-width:80px;
	text-align:center;
	padding:0 9px;
	cursor:pointer;
	font-weight:normal;
}
.tab ul li.on a {
	background:#fff;
	border-top-left-radius:4px;
	border-top-right-radius:4px;
	border:solid 1px #549e01;
	border-bottom:0;
}
.tab ul li a span {
	color:#e24d0a;
}
.box {
	min-height:40px;
	margin:auto;
	background:#fff;
	padding:10px 0;
	position:relative;
}
.box h3 {
	position:absolute;
	top:-7px;
	left:0;
	margin:0;
	display:inline-block;
	height:31px;
	background:url(../images/bg_title.png) no-repeat;
}
.box h3 strong {
	display:inline-block;
	height:31px;
	line-height:31px;
	text-align:center;
	font-weight:normal;
	color:#fff;
	padding-left:16px;
	padding-right:16px;
	background:url(../images/bg_titler.png) no-repeat 100% 0;
}
.publicform {
	padding:10px;
}
.publicform th {
	padding:6px 5px;
	font-weight:normal;
	white-space:nowrap;
	width:12%;
}
.publicform td {
	padding:6px 5px;
}
.publicform label {
	display:inline-block;
	height:20px;
	color:#666;
	line-height:20px;
	padding-left:4px;
	padding-right:15px;
	white-space:nowrap;
}
.publicform span input {
	vertical-align:top;
}
.publicform ul {
/*
	background:#f0fafc;

	border:solid 1px #c2dce1;
*/
}
.publicform ul li {
	float:left;
	white-space:nowrap;
}
.publicform i {
	display:inline-block;
	height:22px;
	line-height:22px;
	width:18px;
	font-style:normal;
	color:red;
	text-align:center;
}
.publicform .btn_div {
	text-align:left;
}
.btn_div {
	height:50px;
	padding-top:15px;
	text-align:center;
}
.btn_div .btn_sub {
	height:31px;
	width:94px;
	padding:0;
	border:0;
	color:#fff;
	background:url(../images/bg_btn.png) no-repeat;
}
.btn_div .btn_qr {
	height:31px;
	width:94px;
	padding:0;
	border:0;
	color:#fff;
	text-indent:24px;
	background:url(../images/btn_pass.png) no-repeat;
}
.btn_div .btn_fj {
	height:31px;
	width:94px;
	padding:0;
	border:0;
	color:#fff;
	text-indent:24px;
	background:url(../images/btn_no.png) no-repeat;
}
.btn_div .btn_bh {
	height:31px;
	width:94px;
	padding:0;
	border:0;
	color:#fff;
	text-indent:24px;
	background:url(../images/btn_back.png) no-repeat;
}
.myform .btn_div {
	height:25px;
	padding-top:10px;
	text-align:left;
	margin:auto;
}
.myform .btn_div .btn_sub {
	height:25px;
	width:52px;
	padding:0;
	border:0;
	color:#fff;
	background:url(../images/bg_btn.png) no-repeat;
}
.myform .ptable {
	width:100%;
	line-height:22px;
	margin:auto;
	background:#c2dce1;
}
.myform .ptable th {
	background:#f2f5f6;
	font-weight:normal;
	padding:4px 0;
}
.myform .ptable th label {
	width:40px;
	padding-left:20px;
}
.myform .ptable td {
	background:#f0fafc;
}
.listform .etable ul li {
	float:left;
	width:25%;
}
.etable a {
	text-decoration:underline;
}
.box .listform {
	padding:10px;
}
.box .listform tbody {
	margin-bottom:10px;
}
.listform .etable {
	width:100%;
	margin:auto;
	border-collapse:separate;
	empty-cells:show;
}
.listform .etable th {
	background:#e6e5e5 url(../images/bg_th.png) repeat-x;
	padding:6px 5px;
	text-align:center;
	border-bottom:solid 1px #cfcfc8;
}
.listform .etable td {
	padding:5px 5px;
	text-align:center;
	background:#fff;
	border-bottom:solid 1px #eee;
}
.bw td {
	background:#e6e6e6 !important;
}
.gradeA td {
	color:#ff6600;
}
.gradeB td {
	color:#cc6600;
}
.gradeC td {
	color:#993300;
}
.gradeD td {
	color:#64a443;
}
.gradeB td {
	background:url(../images/bg_lh.png) repeat-x;
}
.gradeB th {
	background:url(../images/bg_lh.png) repeat-x;
	color:#64a443;
}
.tdl * {
	text-align:left !important;
}
th.cl {
	background:#79c200 !important;
	color:#fff;
	font-weight:normal;
	padding-left:15px !important;
}
td.cl {
	color:#669900;
	padding-left:15px !important;
}
th.cr {
	background:#bec057 !important;
	color:#fff;
	font-weight:normal;
	padding-left:15px !important;
}
td.cr {
	color:#999933;
	padding-left:15px !important;
}
.pd td {
	padding:0 6px 0 0;
}
.page_nav {
	clear:both;
	padding:10px;
	height:15px;
	line-height:15px;
}
.page_nav u {
	display:inline-block;
	float:left;
	text-decoration:none;
}
.page_nav ul {
	display:inline-block;
	height:15px;
	width:auto;
	float:right;
}
.page_nav li {
	height:15px;
	float:left;
	display:inline-block;
}
.page_nav li a {
	padding:0 4px;
	height:15px;
	display:inline-block;
}
.page_nav li span {
	padding:0 2px;
	color:#ccc;
	display:inline-block;
}
.page_nav li.page_nav_current span {
	padding:0 4px;
	background:#669933;
	color:#fff;
}
.page_nav_prev, .page_nav_next, .page_nav_first, .page_nav_last {
	display:inline-block;
	float:right;
}
.page_nav_prev *, .page_nav_next *, .page_nav_first *, .page_nav_last * {
	height:100%;
	display:inline-block;
	padding:0 4px;
}
.page_nav_first span, .page_nav_last span, .page_nav_prev span, .page_nav_next span {
	color:#ccc;
}
.uploadifyQueueItem {
	background-color: #FFFFFF;
	border: none;
	border-bottom: 1px solid #E5E5E5;
	font: 12px Verdana, Geneva, sans-serif;
	height: 30px;
	margin-top: 0;
	padding: 10px;
}
.uploadifyError {
	background-color: #FDE5DD !important;
	border: none !important;
	border-bottom: 1px solid #FBCBBC !important;
}
.uploadifyQueueItem .cancel {
	float: right;
}
.uploadifyQueue .completed {
	color: #C5C5C5;
}
.uploadifyProgress {
	background-color: #E5E5E5;
	margin-top: 6px;
	width: 100%;
}
.uploadifyProgressBar {
	background-color: #0099FF;
	height: 3px;
	width: 1px;
}
#custom-queue {
	border: 1px solid #E5E5E5;
	background-color: #FFFFFF;
	height: 153px;
	overflow:auto;
	width: 370px;
}

pre {
  white-space:pre-wrap; /* css-3 */
  white-space:-moz-pre-wrap; /* Mozilla, since 1999 */
  white-space:-pre-wrap; /* Opera 4-6 */
  white-space:-o-pre-wrap; /* Opera 7 */
  word-wrap:break-word; /* Internet Explorer 5.5+ */
  -moz-binding: url('./wordwrap.xml#wordwrap'); /* 再多这行 */
}

bb{
	font-weight: bold;
}


.sbd1 {
border: #E9E4E9 solid;
border-width:1px 0 0 1px;
}
.sbd1 td {
border: #E9E4E9 solid;
border-width:0 1px 1px 0;
}

.bw1 td {
	background:#E9E7BA !important;
}
</style>
</head>
<body>
    <div class="box">
    	<div align="center"><input type="button" value="打 印" onclick="print();" /></div>
        <div class="publicform fix">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" class="sbd1">
		        <tr>
		          <td style="font-weight:bold;width:150px">申请时间</td>
		          <td>[TIME]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;width:150px">开票金额</td>
		          <td><font color="#ff9933"><b>[AMOUNT]</b></font> 元</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;">发票类型</td>
		          <td>[INVOICETYPE]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold;">开票类型</td>
		          <td>[TYPE]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">开票抬头</td>
		          <td>[TITLE]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">开票内容</td>
		          <td>[CONTENT]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">备注</td>
		          <td>[REMARK]</td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">所属执行单</td>
		          <td>[PIDINFO] </td>
		        </tr>
		        <tr>
		          <td style="font-weight:bold">申请人</td>
		          <td>[USERINFO] </td>
		        </tr>
		        [OTHERS]
      		</table>
        </div>
        <div align="center"><input type="button" value="打 印" onclick="print();" /></div>
    </div>
</body>
</html>
