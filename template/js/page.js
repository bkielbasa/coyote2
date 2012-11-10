/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

var parentMode = false;

function onTreeClick(event)
{
	event.preventDefault();
	pageId = $(this).prev().prev().attr('id').replace('page-', '');

	$('input[name=page_parent]').val(pageId);
	$('#parent').html($(this).text());
}

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

	$('#content').append('<div class="box-hint" style="width: ' + width + '; height: ' + height + ';"><img width="' + width + '" height="' + height + '" src="' + path + '"/></div>');

	$('.box-hint').css('top', e.pageY + 15);
	$('.box-hint').css('left', e.pageX);
}

function onImageOut()
{
	$('.box-hint').remove();
}

function onImageClick(name, isImage)
{
	value = '{{' + (isImage ? 'Image:' : 'File:') + name + '}}';

	$('#page-content fieldset').hide();
	$('#page-content fieldset:eq(0)').show();

	$('.page-menu li a').removeClass('focus');
	$('.page-menu li a:eq(0)').addClass('focus');

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
		$.get(baseUrl + 'Attachment/Delete', {id: attachmentId});
		$('.attachment-' + attachmentId).remove();

		$(element).parent().parent('tr').remove();

		if ($('#media tbody tr').length == 0)
		{
			$('#media tbody').append('<tr><td colspan="5" style="text-align: center;">Brak załączników przypisanych do tego dokumentu</td></tr>');
		}
	}

	$('#media tr:odd').addClass('alternate');
}

function onIconClick()
{
	id = $(this).prev('em').attr('id').replace('page-', '');
	window.location.href = baseUrl + 'adm/Page/View/' + id;
}

function toggleBranch(branch)
{
	pageId = $(this).attr('id').replace('page-', '');
	mode = $(this).attr('class');

	if (mode == 'close')
	{
		if ($(this).parent().children('ul:first').length)
		{
			$(this).parent().children('ul:first').toggle();				
		}
		else
		{
			pageList(pageId);
		}

		$(this).removeClass('close');
		$(this).addClass('open');
	}
	else
	{
		$(this).parent().children('ul:first').toggle();	

		$(this).removeClass('open');
		$(this).addClass('close');
	}
}

function pageList(parentId, pageId)
{
	$.ajax(
	{
		type: 'GET',
		url: baseUrl + 'adm/Page/__displayTree',
		data: {'parentId': parentId, 'pageId': pageId}, 
		beforeSend : function()
		{
			$('#page-tree').append('<div class="page-loader"></div>');
		},
		success : function(data)
		{
			if (!parentId)
			{
				$('#page-tree ul').remove();
				$('#page-tree').append(data);
				$('#page-tree ul:first').css('margin-left', '0');
			}
			else
			{
				$('#page-' + parentId).parent().append(data);
			}
			
			$('em.close, em.open').bind('click', toggleBranch);
			$('.page-loader').remove();

			$('#page-tree img').bind('click', onIconClick);

			if (parentMode)
			{
				$('#page-tree ul li a').bind('click', onTreeClick);
			}
		}
	});
}	

function toggleSide()
{
	if ($('#page-tree').css('margin-left') == '0px')
	{
		width = $('#page-tree').outerWidth();

		$('#page-tree').animate({
			marginLeft:		'-' + (width - 40) + 'px'
		});
	}
	else
	{
		$('#page-tree').animate({
			marginLeft:		'0px'
		});
	}
}

function find()
{
	$.ajax(
	{
		type: 'GET',
		url: baseUrl + 'adm/Page/__find',
		data: {'subject': $('input[name=subject]').val()}, 
		beforeSend : function()
		{
			$('#page-tree').append('<div class="page-loader"></div>');
		},
		success : function(data)
		{
			$('#page-tree ul').remove();
			$('#page-tree').append(data);
			$('#page-tree ul:first').css('margin-left', '0');
			
			$(' em.open').bind('click', toggleBranch);
			$('.page-loader').remove();
		}
	});
}

function purge(element)
{
	$.ajax(
	{
		type: 'GET',
		url: baseUrl + 'adm/Page/__purge',
		beforeSend : function()
		{
			$('#page-tree').append('<div class="page-loader"></div>');
		},
		success : function(data)
		{
			pageList(0);
			$('.page-loader').remove();

			$(element).attr('id', 'page-bin-empty').attr('title', 'Brak dokumentów w koszu');
		}
	});
}

$(document).ready(function()
{
	$('#page-content fieldset:not(:eq(0))').hide();
	$('.page-menu li a:eq(0)').addClass('focus');

	$('.page-menu li a').each(function(index)
	{
		$(this).bind('click', function()
		{
			var element = $('#page-content fieldset');

			$('.page-menu li a').removeClass('focus');
			$(this).addClass('focus');

			element.hide();
			$(element[index]).show();
		});
	});

	$('#page-tree').css('height', $('#page').outerHeight() + 'px');
	$('#media tr:odd').addClass('alternate');

	pageList(0, pageId);		

	$('select[name=moduleId]').bind('change', function()
	{
		moduleId = $(this).val();
		$('#connectorId option').remove();

		if (moduleId > 0)
		{
			for (var index in connector[moduleId])
			{
				$('#connectorId').append(new Option(connector[moduleId][index], index));
			}

			$('.connector-row').show();
		}
		else
		{
			$('.connector-row').hide();
		}
	});

	$('#page-toggle').bind('click', function()
	{
		$('em.open').addClass('close').removeClass('open');
		$('#page-tree ul:not(:eq(0))').hide();
	});

	$('input[name=page_path]').bind('blur', function()
	{
		element = $(this);

		$.get(baseUrl + 'adm/Page/__pathencode', {path: element.val(), connectorId: $('select[name=page_connector]').val()},
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
			$.get(baseUrl + 'adm/Page/__pathencode', {path: $(this).val(), connectorId: $('select[name=page_connector]').val()},
				function(data)
				{
					element.val(data);
				}
			);
		}
	});		

	$('input[name=page_parent]').each(function()
	{
		$(this).parent().append('<strong id="parent">' + parentPageSubject + '</strong>');
		$(this).hide();

		$('#parent').bind('click', function()
		{
			if (!parentMode)
			{
				$(this).addClass('enable');
				$('#page-tree ul li a').bind('click', onTreeClick);
			}
			else
			{
				$(this).removeClass('enable');
				$('#page-tree ul li a').unbind('click');
			}

			parentMode = !parentMode;
		});
	});

	$('select[name=page_connector], #rtf').bind('change', function()
	{
		if (typeof getContent == 'function')
		{
			$('#text_content').val(getContent());
		}
		// przed przeladowaniem strony musimy dac znac kontrolerowi, aby nie walidowal danych - nie dodajemy dokumentu
		$('form[name=form]').append('<input type="hidden" name="reloadPage" value="1" />');
		document.form.submit();
	});

	$('#delete').bind('click', function()
	{
		if (confirm('Czy na pewno chcesz usunąć ten dokument? Podrzędne dokumenty też zostaną usunięte!'))
		{
			window.location.href = baseUrl + 'adm/Page/Delete/' + pageId;
		}
	});

	$('#restore').bind('click', function()
	{
		if (confirm('Czy na pewno chcesz przywrócić ten dokument? Podrzędne dokumenty też zostaną przywrócone!!'))
		{
			window.location.href = baseUrl + 'adm/Page/Restore/' + pageId;
		}
	});
	
	$('#remove').bind('click', function()
	{
		if (confirm('Czy na pewno chcesz usunąć ten dokument? Strony nie będzie można przywrócić. Podrzędne dokumenty też zostaną usunięte!'))
		{
			window.location.href = baseUrl + 'adm/Page/Remove/' + pageId;
		}
	});
});