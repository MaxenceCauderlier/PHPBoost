		<div id="admin_quick_menu">
			<ul>
				<li class="title_menu">{L_NEWS_MANAGEMENT}</li>
				<li>
					<a href="admin_news.php"><img src="news.png" alt="" /></a>
					<br />
					<a href="admin_news.php" class="quick_link">{L_NEWS_MANAGEMENT}</a>
				</li>
				<li>
					<a href="admin_news_add.php"><img src="news.png" alt="" /></a>
					<br />
					<a href="admin_news_add.php" class="quick_link">{L_ADD_NEWS}</a>
				</li>
				<li>
					<a href="admin_news_cat.php"><img src="news.png" alt="" /></a>
					<br />
					<a href="admin_news_cat.php" class="quick_link">{L_CAT_NEWS}</a>
				</li>
				<li>
					<a href="admin_news_config.php"><img src="news.png" alt="" /></a>
					<br />
					<a href="admin_news_config.php" class="quick_link">{L_CONFIG_NEWS}</a>
				</li>
			</ul>
		</div>
		
		<div id="admin_contents">
			# START list #
				
				<script type="text/javascript">
				<!--
				function Confirm() {
					return confirm("{L_CONFIRM_DEL_NEWS}");
				}
				-->
				</script>
				<table class="module_table">
					<tr style="text-align:center;">
						<th>
							{L_TITLE}
						</th>
						<th>
							{L_CATEGORY}
						</th>
						<th>
							{L_PSEUDO}
						</th>
						<th>
							{L_DATE}
						</th>
						<th>
							{L_APROB}
						</th>
						<th>
							{L_ARCHIVE}
						</th>
						<th>
							{L_UPDATE}
						</th>
						<th>
							{L_DELETE}
						</th>
					</tr>
					
					# START list.news #
					<tr style="text-align:center;"> 
						<td class="row2"> 
							<a href="news.php?id={list.news.IDNEWS}">{list.news.TITLE}</a>
						</td>
						<td class="row2"> 
							{list.news.CATEGORY}
						</td>
						<td class="row2"> 
							{list.news.PSEUDO}
						</td>
						<td class="row2">
							{list.news.DATE}
						</td>
						<td class="row2">
							{list.news.APROBATION} 
							<br />
							<span class="text_small">{list.news.VISIBLE}</span>
						</td>
						<td class="row2">
							{list.news.ARCHIVE} 
						</td>
						<td class="row2"> 
							<a href="admin_news.php?id={list.news.IDNEWS}"><img src="../templates/{THEME}/images/{LANG}/edit.png" alt="{L_UPDATE}" title="{L_UPDATE}" /></a>
						</td>
						<td class="row2">
							<a href="admin_news.php?delete=true&amp;id={list.news.IDNEWS}" onClick="javascript:return Confirm();"><img src="../templates/{THEME}/images/{LANG}/delete.png" alt="{L_DELETE}" title="{L_DELETE}" /></a>
						</td>
					</tr>
					# END list.news #
				</table>

				<br /><br />
				<p style="text-align: center;">{PAGINATION}</p>	
		</div>	
			# END list #


			# START news #

			<link href="{news.MODULE_DATA_PATH}/news.css" rel="stylesheet" type="text/css" media="screen, handheld">

			<script type="text/javascript" src="../templates/{THEME}/images/calendar.js"></script>
			<script type="text/javascript">
			<!--
			function check_form(){
				if(document.getElementById('title').value == "") {
					alert("{L_REQUIRE_TITLE}");
					return false;
				}
				if(document.getElementById('contents').value == "") {
					alert("{L_REQUIRE_TEXT}");
					return false;
				}
				return true;
			}
			-->
			</script>

				# START news.preview #
				<table  class="module_table">
						<tr> 
							<th colspan="2">
								{L_PREVIEW}
							</th>
						</tr>
						<tr> 
							<td class="row1">
								<div class="news_container">
									<div class="msg_top_l"></div>			
									<div class="msg_top_r"></div>
									<div class="msg_top">
										<div style="float:left"><a href="rss.php" title="Rss"><img class="valign_middle" src="../templates/{THEME}/images/rss.gif" alt="Rss" title="Rss" /></a> <h3 class="title valign_middle">{news.preview.TITLE}</h3></div>
										<div style="float:right"></div>
									</div>													
									<div class="news_content">
										{news.preview.IMG}
										{news.preview.CONTENTS}
										<br /><br />	
										{news.preview.EXTEND_CONTENTS}	
									</div>									
									<div class="news_bottom_l"></div>		
									<div class="news_bottom_r"></div>
									<div class="news_bottom">
										<span style="float:left"><a class="small_link" href="../member/member{news.U_MEMBER_ID}">{news.preview.PSEUDO}</a></span>
										<span style="float:right">{L_ON}: {news.preview.DATE}</span>
									</div>
								</div>		
							</td>
						</tr>
				</table>	
				<br /><br /><br />
				# END news.preview #

				
				# IF C_ERROR_HANDLER #
				<div class="error_handler_position">
					<span id="errorh"></span>
					<div class="{ERRORH_CLASS}" style="width:500px;margin:auto;padding:15px;">
						<img src="../templates/{THEME}/images/{ERRORH_IMG}.png" alt="" style="float:left;padding-right:6px;" /> {L_ERRORH}
						<br />	
					</div>
				</div>
				# ENDIF #
			
				<form action="admin_news.php" name="form" method="post" style="margin:auto;" onsubmit="return check_form();" class="fieldset_content">
					<fieldset>
						<legend>{L_ADD_NEWS}</legend>
						<p>{L_REQUIRE}</p>
						<dl>
							<dt><label for="title">* {L_TITLE}</label></dt>
							<dd><label><input type="text" size="65" maxlength="100" id="title" name="title" value="{news.TITLE}" class="text" /></label></dd>
						</dl>
						<dl>
							<dt><label for="idcat">* {L_CATEGORY}</label></dt>
							<dd><label>
								<select id="idcat" name="idcat">				
								# START news.select #				
									{news.select.CAT}				
								# END news.select #				
								</select>
							</label></dd>
						</dl>
						<br />
							<label for="contents">* {L_TEXT}</label></dt>
							# INCLUDE handle_bbcode #
							<label><textarea type="text" rows="15" cols="86" id="contents" name="contents">{news.CONTENTS}</textarea></label>
						<br />
						<br />

			# END news #
