<section id="module-articles">

	<header>
		<div class="cat-actions">
			<a href="{U_SYNDICATION}" aria-label="${LangLoader::get_message('syndication', 'common')}"><i class="fa fa-syndication" aria-hidden="true"></i></a>
			{@articles}# IF NOT C_ROOT_CATEGORY # - {CATEGORY_NAME}# ENDIF # # IF IS_ADMIN #<a href="{U_EDIT_CATEGORY}" aria-label="${LangLoader::get_message('edit', 'common')}"> <i class="fa fa-edit small" aria-hidden="true"></i></a># ENDIF #
		</div>
		<h1><span itemprop="name">{TITLE}</span></h1>
	</header>

	# INCLUDE NOT_VISIBLE_MESSAGE #

	<div id="article-articles-{ID}" class="article-articles# IF C_NEW_CONTENT # new-content# ENDIF #" itemscope="itemscope" itemtype="http://schema.org/Article">

		<span class="actions">
			# IF C_EDIT #
			<a href="{U_EDIT_ARTICLE}" aria-label="${LangLoader::get_message('edit', 'common')}"><i class="fa fa-edit" aria-hidden="true"></i></a>
			# ENDIF #
			# IF C_DELETE #
			<a href="{U_DELETE_ARTICLE}" aria-label="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="fa fa-delete" aria-hidden="true"></i></a>
			# ENDIF #
		</span>

		<div class="more">
			# IF C_AUTHOR_DISPLAYED #
				# IF C_AUTHOR_CUSTOM_NAME #
					<i class="fa fa-user-o" aria-hidden="true"></i> {AUTHOR_CUSTOM_NAME}
				# ELSE #
					# IF NOT C_ID_CARD #
						<i class="fa fa-user-o" aria-hidden="true"></i> # IF C_AUTHOR_EXIST #<a itemprop="author" href="{U_AUTHOR}" class="{USER_LEVEL_CLASS}" # IF C_USER_GROUP_COLOR # style="color:{USER_GROUP_COLOR}"# ENDIF #>&nbsp;{PSEUDO}&nbsp;</a># ELSE #{PSEUDO}# ENDIF # |&nbsp;
					# ENDIF #
				# ENDIF #
			# ENDIF #
			<i class="fa fa-calendar" aria-hidden="true"></i>&nbsp;<time datetime="# IF NOT C_DIFFERED #{DATE_ISO8601}# ELSE #{PUBLISHING_START_DATE_ISO8601}# ENDIF #" itemprop="datePublished"># IF NOT C_DIFFERED #{DATE}# ELSE #{PUBLISHING_START_DATE}# ENDIF #</time>&nbsp;|
			&nbsp;<i class="fa fa-eye" aria-hidden="true"></i>&nbsp;<span aria-label="{NUMBER_VIEW} {@articles.sort_field.views}">{NUMBER_VIEW}</span>
			# IF C_COMMENTS_ENABLED #
				&nbsp;|&nbsp;<i class="fa fa-comments-o" aria-hidden="true"></i> <a itemprop="discussionUrl" class="small" href="{U_COMMENTS}">&nbsp;{L_COMMENTS}</a>
			# ENDIF #
			&nbsp;|&nbsp;<i class="fa fa-folder-o" aria-hidden="true"></i>&nbsp;<a itemprop="about" class="small" href="{U_CATEGORY}">{CATEGORY_NAME}</a>
			# IF C_KEYWORDS #
			&nbsp;|&nbsp;<i class="fa fa-tags" aria-hidden="true"></i><span class="sr-only">${LangLoader::get_message('form.keywords', 'common')}</span>
				# START keywords #
					<a itemprop="keywords" href="{keywords.URL}">{keywords.NAME}</a># IF keywords.C_SEPARATOR #, # ENDIF #
				# END keywords #
			# ENDIF #
		</div>

		<meta itemprop="url" content="{U_ARTICLE}">
		<meta itemprop="description" content="${escape(DESCRIPTION)}">
		<meta itemprop="datePublished" content="# IF NOT C_DIFFERED #{DATE_ISO8601}# ELSE #{PUBLISHING_START_DATE_ISO8601}# ENDIF #">
		<meta itemprop="discussionUrl" content="{U_COMMENTS}">
		# IF C_HAS_PICTURE #<meta itemprop="thumbnailUrl" content="{PICTURE}"># ENDIF #
		<meta itemprop="interactionCount" content="{NUMBER_COMMENTS} UserComments">

		<div class="content">
			# IF C_PAGINATION #
				# INCLUDE FORM #
				<div class="spacer"></div>
			# ENDIF #

			# IF PAGE_NAME #
				<h2 class="title page_name">{PAGE_NAME}</h2>
			# ENDIF #

			<div itemprop="text">{CONTENTS}</div>
			# IF C_AUTHOR_DISPLAYED #
				# IF NOT C_AUTHOR_CUSTOM_NAME #
					# IF C_ID_CARD #
					 	{ID_CARD}
					# ENDIF #
				# ENDIF #
			# ENDIF #

			${ContentSharingActionsMenuService::display()}

			<hr />

			# IF C_PAGINATION #
				<div class="pages-pagination right">
					# IF C_NEXT_PAGE #
					<a href="{U_NEXT_PAGE}">{L_NEXT_TITLE} <i class="fa fa-arrow-right"></i></a>
					# ELSE #
					&nbsp;
					# ENDIF #
				</div>
				<div class="pages-pagination center"># INCLUDE PAGINATION_ARTICLES #</div>
				<div class="pages-pagination">
					# IF C_PREVIOUS_PAGE #
					<a href="{U_PREVIOUS_PAGE}"><i class="fa fa-arrow-left"></i> {L_PREVIOUS_TITLE}</a>
					# ENDIF #
				</div>
			# ENDIF #
			<div class="spacer"></div>
		</div>
		<aside>
			# IF C_SOURCES #
			<div id="articles-sources-container">
				<span>${LangLoader::get_message('form.sources', 'common')}</span> :
				# START sources #
				<a itemprop="isBasedOnUrl" href="{sources.URL}" class="small" rel="nofollow">{sources.NAME}</a># IF sources.C_SEPARATOR #, # ENDIF #
				# END sources #
			</div>
			# ENDIF #
			# IF C_DATE_UPDATED #
			<div><i>${LangLoader::get_message('form.date.update', 'common')} : <time datetime="{DATE_UPDATED_ISO8601}" itemprop="datePublished">{DATE_UPDATED}</time></i></div>
			# ENDIF #
			<div class="spacer"></div>
			# IF C_NOTATION_ENABLED #
			<div class="left smaller">
				{KERNEL_NOTATION}
			</div>
			# ENDIF #
			<div class="spacer"></div>
			# IF C_COMMENTS_ENABLED #
				# INCLUDE COMMENTS #
			# ENDIF #
		</aside>
		<footer></footer>
	</div>
	<footer></footer>
</section>
