<div class="publicform fix">
<table cellpadding="0" cellspacing="0" border="0" class="sbd1" width="100%">
    <tr>
        <td colspan="3" style="font-weight:bold">需求响应</td>
    </tr>
    [REQUIREMENTS]
</table>
<br>
<form id="formID" method="post" action="[BASE_URL]tec/action.php" target="post_frame">
<table cellpadding="0" cellspacing="0" border="0" class="sbd2" width="100%">
    <tr>
        <td colspan="4" style="font-weight:bold">需求响应</td>
    </tr>
    <tr width="10%"><td><b>编号</b></td><td><b>需求</b></td><td width="120px;"><b>响应</b></td><td><b>响应备注</b></td></tr>
    [REQUIREMENTLISTS]
</table>
<div class="btn_div"><input type="hidden" name="requirementids" value="[REQUIREMENTIDS]"><input type="hidden" name="id" value="[ID]"/><input type="hidden" name="vcode" value="[VCODE]"/><input type="hidden" name="action" value="project_response"/><input type="submit" value="提 交" class="btn_sub" id="submit" />
</div>
</form>
<iframe name="post_frame" id="post_frame" style="display: none;"></iframe>
</div>