<ul id="usergrouplist" data-sort-groups="<?php p($_['sortGroups']); ?>">
	<!-- Add new group -->
	<li id="newgroup-init">
		<a href="#">
			<span><?php p($l->t('Add Group'))?></span>
		</a>
	</li>
	<li id="newgroup-form" style="display: none">
		<form>
			<input type="text" id="newgroupname" placeholder="<?php p($l->t('Group')); ?>..." />
			<input type="submit" class="button icon-add svg" value="" />
		</form>
	</li>
	<!-- Everyone -->
	<li id="everyonegroup" data-gid="_everyone" data-usercount="" class="isgroup">
		<a href="#">
			<span class="groupname">
				<?php p($l->t('Everyone')); ?>
			</span>
		</a>
		<span class="utils">
			<span class="usercount" id="everyonecount">

			</span>
		</span>
	</li>

	<!-- Group template -->
	<li class="template isgroup hidden">
		<a href="#" class="dorename">
			<span class="groupname">{{name}}</span>
		</a>
		<span class="utils">
			<span class="usercount">{{userCount}}</span>
			<a href="#" class="action delete" original-title="<?php p($l->t('Delete'))?>">
				<img src="<?php print_unescaped(image_path('core', 'actions/delete.svg')) ?>" class="svg" />
			</a>
		</span>
	</li>
</ul>
