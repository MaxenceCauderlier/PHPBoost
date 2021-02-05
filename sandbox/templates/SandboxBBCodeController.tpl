<section id="sandbox-bbcode">
	# INCLUDE SANDBOX_SUBMENU #
	<header class="section-header">
		<h1>
			{@sandbox.module.title} - {@title.bbcode}
		</h1>
	</header>

	<div class="sub-section"><div class="content">{@H|bbcode.explain}</div></div>

	<div class="sub-section"><div class="content"># INCLUDE TYPOGRAPHY #</div></div>

	<div class="sub-section"><div class="content"># INCLUDE BLOCKS #</div></div>

	<div class="sub-section"><div class="content"># INCLUDE CODE #</div></div>

	<div class="sub-section"><div class="content"># INCLUDE LIST #</div></div>

	<div class="sub-section"><div class="content"># INCLUDE TABLE #</div></div>

	<div class="sub-section">
		<div id="bbcode-wiki" class="sandbox-block">
			<h2>{@wiki.module}</h2>
			# IF C_WIKI #
				<article>
					<p class="message-helper bgc notice">{@wiki.conditions}</p>
					<div class="content">
						# START wikimenu #
							<div class="wiki-summary">
								<div class="wiki-summary-title">{@wiki.table.of.contents}</div>
								{wikimenu.MENU}
							</div>
						# END wikimenu #
						{WIKI_CONTENTS}
					</div>
				</article>
			# ELSE #
			 	{@wiki.not}
			# ENDIF #
		</div>
	</div>

	<footer></footer>
</section>
