/**
 * @package Coyote CMF
 * @author Adam Boduch <adam@boduch.net>
 * @copyright Copyright (c) 4programmers.net
 * @license http://www.opensource.org/licenses/bsd-license.php New BSD License
 */

$(document).ready(function()
{
	$('#comment-form').submit(function()
	{
		valueList = $(this).serialize();		
		form = this;
			
		$.ajax(
		{
			type: 'POST',
			url: this.action,
			data: valueList,
			beforeSend : function()
			{
				$(':input', form).attr('disabled', 'disabled');
				$('input[type=submit]', form).val('Proszę czekać...');
			},
			success : function(data)
			{
				$(':input', form).removeAttr('disabled');
				$('input[type=submit]', form).val('Zapisz komentarz');
				
				window.location.hash = '#box-comment';
				$(data).hide().insertAfter('#box-comment h3:first').fadeIn(900);	
				
				$('textarea', form).val('');
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				$(':input', form).removeAttr('disabled');
				$('input[type=submit]', form).val('Zapisz komentarz');
				
				$.windowError(
				{
					windowTitle: 'Błąd',
					windowMessage: xhr.responseText
				});
			}
		});				
		
		return false;
	});
	
	$(':checkbox[name=notify]').click(function()
	{
		checkbox = this;		
		form = $(this).parents('form');
		
		isWatched = $(this).is(':checked');

		$.ajax(
		{
			type: 'POST',
			url: $(form).attr('action') + '?watch=1',
			data: $(form).serialize(),
			beforeSend: function()
			{
				$(checkbox).attr('disabled', 'disabled');
			},
			success: function()
			{
				if (isWatched)
				{
					$(checkbox).next('span').html('Informowanie o nowych komentarzach na tej stronie zostało <strong>włączone</strong>');
				}
				else
				{
					$(checkbox).next('span').html('Informowanie o nowych komentarzach zostało <strong>wyłączone</strong>');
				}
				
				$(checkbox).removeAttr('disabled');
			},
			error: function(xhr, ajaxOptions, thrownError)
			{
				$(checkbox).removeAttr('disabled');
				
				$.windowError(
				{
					windowTitle: 'Błąd',
					windowMessage: xhr.responseText
				});
			}			
		});
	});
});