<div id="admin-quick-menu">
    <ul>
        <li class="title-menu">{L_WEBSITE_UPDATES}</li>
        <li>
            <a href="updates.php"><img src="{PATH_TO_ROOT}/templates/default/images/admin/updater.png" alt="" /></a>
            <br />
            <a href="updates.php" class="quick-link">{L_WEBSITE_UPDATES}</a>
        </li>
        <li>
            <a href="updates.php?type=kernel"><img src="{PATH_TO_ROOT}/templates/default/images/admin/configuration.png" alt="" /></a>
            <br />
            <a href="updates.php?type=kernel" class="quick-link">{L_KERNEL}</a>
        </li>
        <li>
            <a href="updates.php?type=module"><img src="{PATH_TO_ROOT}/templates/default/images/admin/modules.png" alt="" /></a>
            <br />
            <a href="updates.php?type=module" class="quick-link">{L_MODULES}</a>
        </li>
        <li>
            <a href="updates.php?type=template"><img src="{PATH_TO_ROOT}/templates/default/images/admin/themes.png" alt="" /></a>
            <br />
            <a href="updates.php?type=template" class="quick-link">{L_THEMES}</a>
        </li>
    </ul>
</div>

<div id="admin-contents">
	<div style="clear:right;"></div>
    # IF C_UNEXISTING_UPDATE #
        <div class="message-helper warning message-helper-small">
			<i class="fa fa-warning"></i>
			<div class="message-helper-content">{L_UNEXISTING_UPDATE}</div>
		</div>
    # ELSE #
        <h1>{L_APP_UPDATE_MESSAGE}</h1>
        <table>
        	<tbody>
	            <tr>
		            <td style="vertical-align:top;padding-right:10px;">
			            <div class="block-container">
			                <div class="block_top"><span>{APP_NAME} - {APP_VERSION} ({APP_LANGUAGE})</span></div>
			                <div class="block_contents">
								{APP_DESCRIPTION}
								<p class="smaller" style="text-align:right;margin:0">{APP_PUBDATE}</p>
							</div>
			            </div>
			            # IF C_NEW_FEATURES #
			                <div class="block-container">
			                    <div class="block_top"><span>{L_NEW_FEATURES}</span></div>
			                    <div class="block_contents">
									<ul class="list"># START new_features #<li>{new_features.description}</li># END new_features #</ul>
								</div>
			                </div>
			            # END IF #
			            # IF C_IMPROVMENTS #
			                <div class="block-container">
			                    <div class="block_top"><span>{L_IMPROVMENTS}</span></div>
			                    <div class="block_contents">
									<ul class="list"># START improvments #<li>{improvments.description}</li># END improvments #</ul>
								</div>
			                </div>
			            # END IF #
			            <div class="block-container">
			                <div class="block_top"><span class="{PRIORITY_CSS_CLASS}">{L_WARNING} - {APP_WARNING_LEVEL}</span></div>
			                <div class="block_contents">
								{APP_WARNING}
							</div>
			            </div>
			        </td>
			        <td style="vertical-align:top;min-width:200px;">
			            <div class="block-container">
			                <div class="block_top"><span>{L_DOWNLOAD}</span></div>
			                <div class="block_contents">
								<ul class="list">
									<li><a href="{U_APP_DOWNLOAD}">{L_DOWNLOAD_PACK}</a></li>
									# IF U_APP_UPDATE #
									<li><a href="{U_APP_UPDATE}">{L_UPDATE_PACK}</a></li>
									# END IF #
								</ul>
							</div>
			            </div>
			            <div class="block-container">
			                <div class="block_top"><span>{L_AUTHORS}</span></div>
			                <div class="block_contents">
								<ul class="list"># START authors #<li><a href="mailto:{authors.email}">{authors.name}</a></li># END authors #</ul>
							</div>
			            </div>
			            # IF C_BUG_CORRECTIONS #
			                <div class="block-container">
			                    <div class="block_top"><span>{L_FIXED_BUGS}</span></div>
			                    <div class="block_contents">
									<ul class="list"># START bugs #<li>{bugs.description}</li># END bugs #</ul>
								</div>
			                </div>
			            # END IF #
			            # IF C_SECURITY_IMPROVMENTS #
			                <div class="block-container">
			                    <div class="block_top"><span>{L_SECURITY_IMPROVMENTS}</span></div>
								<div class="block_contents">
									<ul class="list"># START security #<li>{security.description}</li># END security #</ul>
								</div>
			                </div>
			            # END IF #
	                </td>
	            </tr>
			</tbody>
        </table>
    # END IF #
</div>
    