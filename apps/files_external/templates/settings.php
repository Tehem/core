<?php
	use \OCA\Files_External\Lib\BackendConfig;
	use \OCA\Files_External\Lib\BackendParameter;
?>
<form id="files_external" class="section" data-encryption-enabled="<?php echo $_['encryptionEnabled']?'true': 'false'; ?>">
	<h2><?php p($l->t('External Storage')); ?></h2>
	<?php if (isset($_['dependencies']) and ($_['dependencies']<>'')) print_unescaped(''.$_['dependencies'].''); ?>
	<table id="externalStorage" class="grid" data-admin='<?php print_unescaped(json_encode($_['isAdminPage'])); ?>'>
		<thead>
			<tr>
				<th></th>
				<th><?php p($l->t('Folder name')); ?></th>
				<th><?php p($l->t('External storage')); ?></th>
				<th><?php p($l->t('Configuration')); ?></th>
				<?php if ($_['isAdminPage']) print_unescaped('<th>'.$l->t('Available for').'</th>'); ?>
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php $_['mounts'] = array_merge($_['mounts'], array('' => array('id' => ''))); ?>
		<?php foreach ($_['mounts'] as $mount): ?>
			<?php
				$mountBackend = null;
				if (isset($mount['class'])) {
					$mountBackend = $_['backends'][$mount['class']];
				}
			?>
			<tr <?php print_unescaped(isset($mount['mountpoint']) ? 'class="'.OC_Util::sanitizeHTML($mount['class']).'"' : 'id="addMountPoint"'); ?> data-id="<?php p($mount['id']) ?>">
				<td class="status">
					<span></span>
				</td>
				<td class="mountPoint"><input type="text" name="mountPoint"
											  value="<?php p(isset($mount['mountpoint']) ? $mount['mountpoint'] : ''); ?>"
											  data-mountpoint="<?php p(isset($mount['mountpoint']) ? $mount['mountpoint'] : ''); ?>"
											  placeholder="<?php p($l->t('Folder name')); ?>" />
				</td>
				<?php if (!isset($mountBackend)): ?>
					<td class="backend">
						<select id="selectBackend" class="selectBackend" data-configurations='<?php p(json_encode($_['backends'])); ?>'>
							<option value="" disabled selected
									style="display:none;"><?php p($l->t('Add storage')); ?></option>
							<?php foreach ($_['backends'] as $backend): ?>
								<option value="<?php p($backend->getClass()); ?>"><?php p($backend->getText()); ?></option>
							<?php endforeach; ?>
						</select>
					</td>
				<?php else: ?>
					<td class="backend" data-class="<?php p($mountBackend->getClass()); ?>"><?php p($mountBackend->getText()); ?>
					</td>
				<?php endif; ?>
				<td class ="configuration">
					<?php if (isset($mountBackend)): ?>
						<?php foreach ($mountBackend->getParameters() as $parameter): ?>
							<?php
								$value = '';
								if (isset($mount['options'][$parameter->getName()])) {
									$value = $mount['options'][$parameter->getName()];
								}
								$placeholder = $parameter->getText();
								$is_optional = $parameter->isFlagSet(BackendParameter::FLAG_OPTIONAL);

								switch ($parameter->getType()) {
								case BackendParameter::VALUE_PASSWORD: ?>
									<input type="password"
										   <?php if ($is_optional): ?> class="optional"<?php endif; ?>
										   data-parameter="<?php p($parameter->getName()); ?>"
										   value="<?php p($value); ?>"
										   placeholder="<?php p($placeholder); ?>" />
								<?php
									break;
								case BackendParameter::VALUE_BOOLEAN: ?>
									<label><input type="checkbox"
												  data-parameter="<?php p($parameter->getName()); ?>"
												  <?php if ($value == 'true'): ?> checked="checked"<?php endif; ?>
												  /><?php p($placeholder); ?></label>
								<?php
									break;
								case BackendParameter::VALUE_HIDDEN: ?>
									<input type="hidden"
										   data-parameter="<?php p($parameter->getName()); ?>"
										   value="<?php p($value); ?>" />
								<?php
									break;
								default: ?>
									<input type="text"
										   <?php if ($is_optional): ?> class="optional"<?php endif; ?>
										   data-parameter="<?php p($parameter->getName()); ?>"
										   value="<?php p($value); ?>"
										   placeholder="<?php p($placeholder); ?>" />
							<?php
								}
							?>
						<?php endforeach; ?>
						<?php
							$customJs = $mountBackend->getCustomJs();
							if (isset($customJs)) {
								\OCP\Util::addScript('files_external', $customJs);
							}
						?>
					<?php endif; ?>
				</td>
				<?php if ($_['isAdminPage']): ?>
				<td class="applicable"
					align="right"
					data-applicable-groups='<?php if (isset($mount['applicable']['groups']))
													print_unescaped(json_encode($mount['applicable']['groups'])); ?>'
					data-applicable-users='<?php if (isset($mount['applicable']['users']))
													print_unescaped(json_encode($mount['applicable']['users'])); ?>'>
					<input type="hidden" class="applicableUsers" style="width:20em;" value=""/>
				</td>
				<?php endif; ?>
				<td class="mountOptionsToggle <?php if (!isset($mount['mountpoint'])) { p('hidden'); } ?>"
					><img
						class="svg action"
						title="<?php p($l->t('Advanced settings')); ?>"
						alt="<?php p($l->t('Advanced settings')); ?>"
						src="<?php print_unescaped(image_path('core', 'actions/settings.svg')); ?>" />
					<input type="hidden" class="mountOptions" value="<?php isset($mount['mountOptions']) ? p(json_encode($mount['mountOptions'])) : '' ?>" />
					<?php if ($_['isAdminPage']): ?>
					<?php if (isset($mount['priority'])): ?>
					<input type="hidden" class="priority" value="<?php p($mount['priority']) ?>" />
					<?php endif; ?>
					<?php endif; ?>
				</td>
				<td <?php if (isset($mount['mountpoint'])): ?>class="remove"
					<?php else: ?>class="hidden"
					<?php endif ?>><img alt="<?php p($l->t('Delete')); ?>"
										title="<?php p($l->t('Delete')); ?>"
										class="svg action"
										src="<?php print_unescaped(image_path('core', 'actions/delete.svg')); ?>" /></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<br />

	<?php if ($_['isAdminPage']): ?>
		<br />
		<input type="checkbox" name="allowUserMounting" id="allowUserMounting"
			value="1" <?php if ($_['allowUserMounting'] == 'yes') print_unescaped(' checked="checked"'); ?> />
		<label for="allowUserMounting"><?php p($l->t('Enable User External Storage')); ?></label> <span id="userMountingMsg" class="msg"></span>

		<p id="userMountingBackends"<?php if ($_['allowUserMounting'] != 'yes'): ?> class="hidden"<?php endif; ?>>
			<?php p($l->t('Allow users to mount the following external storage')); ?><br />
			<?php $i = 0; foreach ($_['backends'] as $backend): ?>
				<input type="checkbox" id="allowUserMountingBackends<?php p($i); ?>" name="allowUserMountingBackends[]" value="<?php p($backend->getClass()); ?>" <?php if ($backend->isVisibleFor(BackendConfig::VISIBILITY_PERSONAL)) print_unescaped(' checked="checked"'); ?> />
				<label for="allowUserMountingBackends<?php p($i); ?>"><?php p($backend->getText()); ?></label> <br />
				<?php $i++; ?>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>
</form>
