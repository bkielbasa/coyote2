<script type="text/javascript">
<!--
	var textRevision = <?= (int)@$text_id; ?>;
	var parentPageSubject = '<?= (string)$parentPageSubject; ?>';

	$(document).ready(function()
	{
		new AjaxUpload('#attach-button',
		{
			action: baseUrl + 'Attachment/Submit',
			name: 'attachment',
			responseType: 'json',
			autoSubmit : true,
			onSubmit : function()
			{
				this.disable();
				$('#attach-button').addClass('attachment-loader');
			},
			onComplete : function(file, data)
			{
				if (data.error)
				{
					alert(data.error);
				}
				else
				{
					$('#media tr td[colspan]').remove();

					$('#media tbody').append('<tr><td><a title="Kliknij, aby wstawić do tekstu" onclick="onImageClick(\'' + data.name + '\', ' + (data.width > 0 ? true : false) + ');" onmouseover="onImageOver(\'' + data.path + '\', ' + data.width + ', ' + data.height + ', event);" onmouseout="onImageOut();" class="' + data.suffix + '">' + data.name + '</a></td><td>' + data.mime + '</td><td>' + Math.ceil(data.size) + ' kB</td><td>' + data.time + '</td><td><input type="button" value="Usuń" class="delete-button" onclick="onAttachmentDelete(this, ' + data.id + ');" /></td></tr>');
					$('form[name=form]').append('<input type="hidden" name="attachment[]" value="' + data.id + '" class="attachment-' + data.id + '" />');
					$('#media tr:odd').addClass('alternate');
				}

				this.enable();
				$('#attach-button').removeClass('attachment-loader');
			}
		});
	});

	function preview()
	{
		height = $(window).height();

		$('body').append('<div id="preview"><b class="overlay-top"><b class="round1"></b><b class="round2"></b><b class="round3"></b><b class="round4"></b></b><a title="Kliknij, aby zamknąć podgląd" class="overlay-close"></a><div id="preview-content"></div><b class="overlay-bottom"><b class="round4"></b><b class="round3"></b><b class="round2"></b><b class="round1"></b></b></div>');
		$('#preview-content').css('min-height', height + 'px');

		$('#preview').bind('click', function()
		{
			$('#preview').remove();
			return false;
		});

		valueList = { revision: textRevision };
		$(':input').each(function()
			{
				if (this.name == 'attachment')
				{
					// continue
				}
				else if ($(this).attr('type') == 'checkbox')
				{
					if ($(this).is(':checked'))
					{
						valueList[this.name] = $(this).val();
					}
				}
				else if (this.name == 'text_content' && typeof getContent == 'function')
				{
					valueList[this.name] = getContent();
				}
				else
				{
					valueList[this.name] = $(this).val();
				}
			}
		);

		$.ajax(
		{
			type: 'POST',
			url: '<?= url('adm/Preview'); ?>',
			data: valueList,
			beforeSend : function()
			{
				$('#preview-content').append('<div class="page-loader"></div>');
			},
			success : function(data)
			{
				$('.page-loader').remove();
				$('#preview-content').append(data);
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				alert(xhr.responseText);
			}
		});
	}

//-->
</script>

<div id="page" style="overflow: hidden;">
	<?php include('_partialPage.php'); ?>

	<div id="page-content">

		<?= Form::open('', array('method' => 'post', 'name' => 'form')); ?>
			<?php foreach ((array)$input->post->attachment as $attachmentId) : ?>
			<?= Form::hidden('attachment[]', $attachmentId, array('class' => 'attachment-' . $attachmentId)); ?>
			<?php endforeach; ?>

			<div style="float: right;">
				<?= Form::button('', 'Informacje', array('onclick' => 'window.location.href = \'' . url('adm/Page/View/' . $id) . '\'')); ?>
				<?= Form::submit('', 'Zapisz', array('class' => 'accept-button')); ?>
				<?php if ($page->getId()) : ?>
				<?php if (!$page->isDeleted()) : ?>
				<?= Form::button('', 'Usuń', array('id' => 'delete', 'class' => 'delete-button')); ?>
				<?php else : ?>
				<?= Form::button('', 'Przywróć', array('id' => 'restore', 'class' => 'restore-button')); ?>
				<?php endif; ?>
				<?= Form::button('', 'Usuń permanentnie', array('id' => 'remove', 'class' => 'delete-button')); ?>
				<?php endif; ?>
				<?= Form::button('', 'Podgląd', array('onclick' => 'preview();', 'class' => 'preview-button')); ?>
			</div>
			<br style="clear: both;" />

			<div class="page-menu">
				<ul>
					<?php foreach ($page->getFieldsets() as $fieldset) : ?>
					<li><a><?= $fieldset->getLabel(); ?></a></li>
					<?php endforeach; ?>
					<li><a>Parsery</a></li>
					<li><a>Załączniki</a></li>
					<li><a>Dostęp</a></li>
					<?php if ($moduleConfig->getElements()) : ?>
					<li><a>Konfiguracja modułu</a></li>
					<?php endif; ?>
				</ul>
			</div>
			<div class="menu-block">

				<?php foreach ($page->getFieldsets() as $fieldset) : ?>
				<fieldset>
					<ol>
						<?= $fieldset; ?>
					</ol>
				</fieldset>
				<?php endforeach; ?>

				<fieldset>
					<ol>
						<?php foreach ($parser as $key => $row) : ?>
						<li>
							<label title="<?= $row['parser_description']; ?>"><?= $row['parser_text']; ?></label>
							<?= Form::checkbox("parser[$key]", $row['parser_id'], $input->isPost() ? (bool)in_array($row['parser_id'], (array)$input->post['parser']) : ($page->getId() ? in_array($row['parser_id'], $pageParser) : $row['parser_default'])); ?>
						</li>
						<?php endforeach; ?>
					</ol>
				</fieldset>

				<fieldset style="margin-top: 0;">
					<table id="media" style="width: 97%; margin: auto;">
						<thead>
							<tr>
								<th>Nazwa pliku</th>
								<th>Typ MIME</th>
								<th>Rozmiar</th>
								<th>Data dodania</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php if ($attachment) : ?>
							<?php foreach ($attachment as $row) : ?>
							<tr>
								<td><a title="Kliknij, aby dodać do tekstu" href="#" class="<?= strtolower(pathinfo($row->getName(), PATHINFO_EXTENSION)); ?>" onclick="onImageClick('<?= $row->getName(); ?>', <?= $row->isImage() > 0 ? 'true' : 'false'; ?>);" onmouseover="onImageOver('<?= url($row->getPath()); ?>', <?= $row->getWidth(); ?>, <?= $row->getHeight(); ?>, event);" onmouseout="onImageOut();"><?= $row->getName(); ?></a></td>
								<td><?= $row->getMime(); ?></td>
								<td><?= Text::fileSize($row->getFileSize()); ?></td>
								<td><?= User::formatDate($row->getTime()); ?></td>
								<td><?= Form::button('', 'Usuń', array('class' => 'delete-button', 'onclick' => 'onAttachmentDelete(this, ' . $row->getId() . ')')); ?></td>
							</tr>
							<?php endforeach; ?>
							<?php else : ?>
							<tr>
								<td colspan="5" style="text-align: center;">Brak załączników przypisanych do dokumentu</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>

					<ol>
						<li>
							<label><?= Form::button('', 'Dodaj załącznik', array('id' => 'attach-button', 'class' => 'attach-button')); ?></label>

						</li>
					</ol>
				</fieldset>

				<fieldset>
					<ol>
						<li>
							<label>Dostęp dla grup:</label>

							<div style="overflow: hidden;">
								<ol>
									<?php foreach ($groupList as $id => $name) : ?>
									<li style="margin-left: 0"><?= Form::checkbox('group[]', $id, (bool)in_array($id, $input->isPost() ? (array)$input->post['group'] : $pageGroup)); ?> <?= $name; ?></li>
									<?php endforeach; ?>
								</ol>
							</div>
						</li>
						<li>
							<label></label>
							<?= Form::checkbox('children', 1, false); ?> Zastosuj dla stron potomnych
						</li>
					</ol>
				</fieldset>

				<?php if ($moduleConfig->getElements()) : ?>
				<fieldset>
					<ol>
						<?= $moduleConfig; ?>
					</ol>
				</fieldset>
				<?php endif; ?>

			</div>
		<?= Form::close(); ?>
	</div>
</div>