	# IF C_ERRORS #
	<form action="{U_CLEAR_LOGGED_ERRORS}" method="post" class="fieldset-content">
		<fieldset>
			<legend>{@clear_list}</legend>
			<div class="form-element">
				<label>{@clear_list} <span class="field-description">{@clear_list_explain}</span></label>
				<div class="form-field"><label><button type="submit" name="clear" data-confirmation="{@logged_errors_clear_confirmation}" value="true">{@clear_list}</button></label></div>
			</div>
		</fieldset>
		<input type="hidden" name="token" value="{TOKEN}">
	</form>
	<div class="spacer">&nbsp;</div>
	# ENDIF #
	<table>
		<caption>{@logged_errors_list}</caption>
		# IF C_ERRORS #
		<thead>
			<tr> 
				<th style="width:80%;overflow:auto;">
					${LangLoader::get_message('description', 'main')}
				</th>
				<th>
					${LangLoader::get_message('date', 'date-common')}
				</th>
			</tr>
		</thead>
		# IF C_PAGINATION #
		<tfoot>
			<tr>
				<th colspan="2">
					# INCLUDE PAGINATION #
				</th>
			</tr>
		</tfoot>
		# ENDIF #
		<tbody>
			# START errors #
			<tr>
				<td> 
					<div class="message-helper {errors.CLASS}">
						<i class="fa fa-{errors.CLASS}"></i>
						<div class="message-helper-content">
							<strong>{errors.ERROR_TYPE} : </strong>{errors.ERROR_MESSAGE}<br /><br /><br />
							<em>{errors.ERROR_STACKTRACE}</em>
						</div>
					</div>
				</td>
				<td>
					{errors.DATE}
				</td>
			</tr>
			# END errors #
		</tbody>
		# ELSE #
		<tbody>
			<tr>
				<td>{@no_error}</td>
			</tr>
		</tbody>
		# ENDIF #
	</table>
