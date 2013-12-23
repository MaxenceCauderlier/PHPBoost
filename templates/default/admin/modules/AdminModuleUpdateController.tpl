# INCLUDE UPLOAD_FORM #

# INCLUDE MSG #

# IF C_UPDATES #
	<form action="{REWRITED_SCRIPT}" method="post">
		<div class="message-helper warning">
			<i class="fa fa-warning"></i>
			<div class="message-helper-content">{@modules.updates_available}</div>
		</div>
		
		<table>
			<caption>{@modules.updates_available}</caption>
			<thead>
				<tr>
					<th>{@modules.name}</th>
					<th>{@modules.description}</th>
					<th>{@modules.upgrade_module}</th>
				</tr>
			</thead>
			<tbody>
				# START modules_upgradable #
				<tr> 	
					<td>					
						<img src="{PATH_TO_ROOT}/{modules_upgradable.ICON}/{modules_upgradable.ICON}.png" alt="" /> <span class="text-strong">{modules_upgradable.NAME}</span> <span class="text-italic">({modules_upgradable.VERSION})</span>
					</td>
					<td style="text-align:left;">	
						<span class="text-strong">{@modules.author} :</span> {modules_upgradable.AUTHOR} {modules_upgradable.AUTHOR_WEBSITE}<br />
						<span class="text-strong">{@modules.description} :</span> {modules_upgradable.DESCRIPTION}<br />
						<span class="text-strong">{@modules.compatibility} :</span> PHPBoost {modules_upgradable.COMPATIBILITY}<br />
						<span class="text-strong">{@modules.php_version} :</span> {modules_upgradable.PHP_VERSION}
					</td>
					<td>
						<input type="hidden" name="token" value="{TOKEN}">
						<button type="submit" name="upgrade-{modules_upgradable.ID}" value="true">{@modules.upgrade_module}</button>
						<input type="hidden" name="module_id" value="{modules_upgradable.ID}">
					</td>
				</tr>	
				# END modules_upgradable #
			</tbody>
		</table>
	</form>
# ELSE #
	<div class="message-helper warning" style="margin-top:100px;">
		<i class="fa fa-warning"></i>
		<div class="message-helper-content">{@modules.no_upgradable_module_available}</div>
	</div>
# ENDIF #