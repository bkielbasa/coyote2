<script type="text/javascript">
<!--
	function onImageOver(path, width, height, e)
	{
		if (!width && !height)
		{
			return;
		}
		if (width > 160)
		{
			scale = height / width;
			width = 160;
			height = width * scale;
		}

		$('body').append('<div class="box-bubble" style="width: ' + width + '; height: ' + height + ';"><img width="' + width + '" height="' + height + '" src="' + path + '"/></div>');

		$('.box-bubble').css('top', e.pageY + 15);
		$('.box-bubble').css('left', e.pageX);
	}

	function onImageOut()
	{
		$('.box-bubble').remove();
	}

	function onImageClick(name, isImage)
	{
		value = '{{' + (isImage ? 'Image:' : 'File:') + name + '}}';

		$('#edit fieldset').hide();
		$('#edit fieldset:eq(0)').show();

		$('.box-menu li').removeClass('focus');
		$('.box-menu li:eq(0)').addClass('focus');

		element = document.getElementById('text_content');

		if (document.selection)
		{
			element.focus();
			sel = document.selection.createRange();
			sel.text = value;

			element.focus();
		}
		else if (element.selectionStart || element.selectionStart == '0')
		{
			var startPos = element.selectionStart;
			var endPos = element.selectionEnd;
			var scrollTop = element.scrollTop;
			element.value = element.value.substring(0, startPos) + value + element.value.substring(endPos, element.value.length);

			element.focus();
			element.selectionStart = startPos + value.length;
			element.selectionEnd = startPos + value.length;
			element.scrollTop = scrollTop;
		}
		else
		{
			element.value += (openWith + value + closeWith);
			element.focus();
		}
	}

	function onAttachmentDelete(element, attachmentId)
	{
		if (confirm('Czy chcesz usunąć ten załącznik?'))
		{
			$.get('<?= Url::site(); ?>Attachment/Delete', {id: attachmentId});
			$('.attachment-' + attachmentId).remove();

			$(element).parent().parent('tr').remove();

			if ($('#media tbody tr').length == 0)
			{
				$('#media tbody').append('<tr><td colspan="5" style="text-align: center;">Brak załączników przypisanych do tego dokumentu</td></tr>');
			}
		}

		$('#media tr:odd').addClass('alternate');
	}

	$(document).ready(function()
	{
		$('#edit fieldset:gt(0)').hide();

		$('.box-menu li a').bind('click', function()
		{
			index = $('.box-menu li a').index(this);
			$('.box-menu li').removeClass('focus');

			$(this).parent('li').addClass('focus');
			$('#edit fieldset').hide();
			$('#edit fieldset:eq(' + index + ')').show();
		});

		$('.editor').wikiEditor();

		$('input[name=page_path]').bind('blur', function()
		{
			element = $(this);

			$.get('', {path: element.val()},
				function(data)
				{
					element.val(data);
				}
			);
		});

		$('input[name=page_subject]').bind('blur', function()
		{
			element = $('input[name=page_path]');

			if (!element.val().length)
			{
				$.get('', {path: $(this).val()},
					function(data)
					{
						element.val(data);
					}
				);
			}
		});

		$('.box-menu li:last').click(function()
		{
			valueList = { revision: '<?= (int) $page->getTextId(); ?>' };

			$.ajax(
			{
				type: 'POST',
				url: '<?= url('Preview'); ?>',
				dataType: 'html',
				data: $('#edit').serialize(),
				beforeSend : function()
				{
					$('#preview-content').html('<div class="page-loader"></div>');
				},
				success : function(data)
				{
					$('.page-loader').remove();
					$('#preview-content').html(data);
				},
				error: function(xhr, ajaxOptions, thrownError)
				{
					alert('Błąd: ' + xhr.responseText);
				}
			});
		});

		new AjaxUpload('#attach-button',
		{
			action: '<?= Url::site(); ?>Attachment/Submit',
			name: 'attachment',
			responseType: 'json',
			autoSubmit : true,
			onSubmit : function()
			{
				this.disable();
				$('#attach-button').addClass('attachment-loader').val('Proszę czekać...');
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
					$('#edit').append('<input type="hidden" name="attachment[]" value="' + data.id + '" class="attachment-' + data.id + '" />');
					$('#media tr:odd').addClass('alternate');
				}

				this.enable();
				$('#attach-button').removeClass('attachment-loader').val('Dodaj załącznik');
			}
		});
	});
//-->
</script>

<div class="box" style="margin-top: 40px">
	<ul class="box-menu">
		<li class="focus"><a>Zawartość</a></li>
		<li><a>Znaczniki meta</a></li>
		<li><a>Załączniki</a></li>
		<li><a>Podgląd</a></li>
	</ul>

	<?= Form::open('', array('method' => 'post', 'id' => 'edit')); ?>
		<?= Form::hidden('revision', $page->getTextId()); ?>
		<?= Form::hidden('location', $page->getLocation()); ?>

		<?php foreach ((array)$input->post->attachment as $attachmentId) : ?>
		<?= Form::hidden('attachment[]', $attachmentId, array('class' => 'attachment-' . $attachmentId)); ?>
		<?php endforeach; ?>

		<?= $contentForm; ?>
		<?= $metaForm; ?>

		<fieldset style="margin-top: 0;">
			<table id="media" style="margin: auto;">
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
					<?= Form::button('', 'Dodaj załącznik', array('id' => 'attach-button', 'class' => 'attach-button')); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset id="preview-content" style="margin-top: 0;">

		</fieldset>

	<?= Form::close(); ?>

</div>