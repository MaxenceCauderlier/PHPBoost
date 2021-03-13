<section id="module-pages" class="category-{CATEGORY_ID}">
	<header class="section-header">
		<div class="controls align-right">
			<a href="{U_SYNDICATION}" aria-label="${LangLoader::get_message('syndication', 'common')}"><i class="fa fa-rss warning" aria-hidden="true"></i></a>
			{@module.title}# IF NOT C_ROOT_CATEGORY # - {CATEGORY_NAME}# ENDIF #
			# IF IS_ADMIN #<a href="{U_EDIT_CATEGORY}" aria-label="${LangLoader::get_message('edit', 'common')}"><i class="far fa-edit" aria-hidden="true"></i></a># ENDIF #
		</div>
		<h1><span id="name" itemprop="name">{TITLE}</span></h1>
	</header>
	<div class="sub-section">
		<div class="content-container">
			# IF NOT C_PUBLISHED #
				# INCLUDE NOT_VISIBLE_MESSAGE #
			# ENDIF #
			<article itemscope="itemscope" itemtype="https://schema.org/CreativeWork" id="pages-item-{ID}" class="pages-item single-item# IF C_NEW_CONTENT # new-content# ENDIF #">
				<div class="flex-between">
					<div class="more">
						# IF C_AUTHOR_DISPLAYED #
							<span class="pinned" aria-label="${LangLoader::get_message('author', 'common')}">
								<i class="far fa-fw fa-user" aria-hidden="true"></i>
								# IF C_AUTHOR_CUSTOM_NAME #
									{AUTHOR_CUSTOM_NAME}
								# ELSE #
									# IF C_AUTHOR_EXIST #<a itemprop="author" rel="author" class="{AUTHOR_LEVEL_CLASS}" href="{U_AUTHOR}" # IF C_AUTHOR_GROUP_COLOR # style="color:{AUTHOR_GROUP_COLOR}" # ENDIF #>{AUTHOR_DISPLAY_NAME}</a># ELSE #<span class="visitor">{AUTHOR_DISPLAY_NAME}</span># ENDIF #
								# ENDIF #
							</span>
						# ENDIF #
						<span class="pinned" aria-label="${LangLoader::get_message('form.date.creation', 'common')}">
							<i class="far fa-fw fa-calendar-alt" aria-hidden="true"></i>
							<time datetime="# IF NOT C_DEFFERED_PUBLISHING #{DATE_ISO8601}# ELSE #{DEFFERED_PUBLISHING_START_DATE_ISO8601}# ENDIF #" itemprop="datePublished"># IF NOT C_DEFFERED_PUBLISHING #{DATE}# ELSE #{DEFFERED_PUBLISHING_START_DATE}# ENDIF #</time>
						</span>
						<span class="pinned" aria-label="${LangLoader::get_message('category', 'categories-common')}"><i class="far fa-fw fa-folder"></i> <a itemprop="about" href="{U_CATEGORY}">{CATEGORY_NAME}</a></span>
						# IF C_VIEWS_NUMBER #<span class="pinned" aria-label="{VIEWS_NUMBER} # IF C_SEVERAL_VIEWS #{@pages.views}# ELSE #{@pages.view}# ENDIF #"><i class="far fa-fw fa-eye"></i> {VIEWS_NUMBER}</span># ENDIF #
						# IF C_ENABLED_COMMENTS #
							<span"pinned" aria-label="${LangLoader::get_message('sort_by.comments.number', 'common')}"><i class="far fa-fw fa-comments"></i># IF C_COMMENTS # {COMMENTS_NUMBER}# ENDIF # {L_COMMENTS}</span>
						# ENDIF #
					</div>
					# IF C_CONTROLS #
						<div class="controls align-right">
							# IF C_EDIT #<a href="{U_EDIT}" aria-label="${LangLoader::get_message('edit', 'common')}"><i class="far fa-fw fa-edit" aria-hidden="true"></i></a># ENDIF #
							# IF C_DELETE #<a href="{U_DELETE}" aria-label="${LangLoader::get_message('delete', 'common')}" data-confirmation="delete-element"><i class="far fa-fw fa-trash-alt" aria-hidden="true"></i></a># ENDIF #
						</div>
					# ENDIF #
				</div>

				<div class="content">
					# IF C_HAS_THUMBNAIL #
						<img class="item-thumbnail" src="{U_THUMBNAIL}" alt="{NAME}" itemprop="thumbnailUrl" />
					# ENDIF #

					<div itemprop="text">{CONTENT}</div>
				</div>
				# IF C_HAS_UPDATE_DATE #<span class="pinned notice small text-italic modified-date">${LangLoader::get_message('status.last.update', 'common')} <time datetime="{UPDATE_DATE_ISO8601}" itemprop="dateModified">{UPDATE_DATE_FULL}</time></span># ENDIF #

				<aside>${ContentSharingActionsMenuService::display()}</aside>

				# IF C_SOURCES #
					<aside class="sources-container">
						<span class="text-strong"><i class="fa fa-map-signs" aria-hidden="true"></i> ${LangLoader::get_message('form.sources', 'common')}</span> :
						# START sources #
							<a itemprop="isBasedOnUrl" href="{sources.URL}" class="pinned link-color" rel="nofollow">{sources.NAME}</a># IF sources.C_SEPARATOR ## ENDIF #
						# END sources #
					</aside>
				# ENDIF #
				# IF C_KEYWORDS #
					<aside class="tags-container">
						<span class="text-strong"><i class="fa fa-tags" aria-hidden="true"></i> ${LangLoader::get_message('form.keywords', 'common')} : </span>
						# START keywords #
							<a itemprop="pinned link-color" href="{keywords.URL}">{keywords.NAME}</a># IF keywords.C_SEPARATOR #, # ENDIF #
						# END keywords #
					</aside>
				# ENDIF #
				# IF C_ENABLED_COMMENTS #
					<aside>
						# INCLUDE COMMENTS #
					</aside>
				# ENDIF #
			</article>
		</div>
	</div>
	<footer>
		<meta itemprop="url" content="{U_ITEM}">
		<meta itemprop="description" content="${escape(SUMMARY)}" />
		# IF C_ENABLED_COMMENTS #
		<meta itemprop="discussionUrl" content="{U_COMMENTS}">
		<meta itemprop="interactionCount" content="{COMMENTS_NUMBER} UserComments">
		# ENDIF #
	</footer>
</section>
