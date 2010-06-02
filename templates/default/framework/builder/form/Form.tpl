# IF C_VALIDATION_ERROR #
<div class="error">
# START validation_error_messages #
	{validation_error_messages.ERROR_MESSAGE}<br />
# END validation_error_messages #
</div>
# ENDIF #

<script type="text/javascript">
<!--
	loadLib('phpboost/form/form.js');
	loadLib('phpboost/form/validator.js');
	var form = new HTMLForm("{E_HTML_ID}");
	HTMLForms.add(form);
-->
</script>


<form id="{E_HTML_ID}" action="{E_TARGET}" method="{E_METHOD}" onsubmit="return HTMLForms.get('{E_HTML_ID}').validate();" class="{E_FORMCLASS}">
	# IF C_HAS_REQUIRED_FIELDS #
	<p style="text-align:center;">{L_REQUIRED_FIELDS}</p>
	# ENDIF #
	
	# START fieldsets #
		# INCLUDE fieldsets.FIELDSET #
	# END fieldsets #
	
	<fieldset class="fieldset_submit">
		<legend>{L_SUBMIT}</legend>
		# START buttons #
			# INCLUDE buttons.BUTTON #
		# END buttons #
		<input type="hidden" id="{E_HTML_ID}_disabled_fields" name="{E_HTML_ID}_disabled_fields" value="" />
		<input type="hidden" id="{E_HTML_ID}_disabled_fieldsets" name="{E_HTML_ID}_disabled_fieldsets" value="" />
	</fieldset>
</form>