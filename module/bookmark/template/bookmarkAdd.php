<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$('input[name=url]').blur(function()
		{
			element = this;
			url = $(element).val();

			form = $(element).parents('form');
			
			if ($.trim(url).length)
			{
				if (!$('input[name=title]').val().length && !$('textarea[name=content]').val().length)
				{
					$.ajax(
					{
						url: '<?= url($page->getLocation()) . '/fetch'; ?>',
						data: {'url': url}, 
						dataType: 'json',
						type: 'GET',
						beforeSend: function()
						{
							$('<small>Proszę czekać...</small>').insertAfter(element);	
							$(':input', form).attr('disabled', 'disabled');						
						},
						success: function(data)
						{
							$(':input', form).removeAttr('disabled');
							$(element).next('small').remove();

							$('input[name=title]').val(data.title);
							$('textarea[name=content]').val(data.description);
						}
					});
				}		
			}
		});
	});

//-->
</script>

<?= $form; ?>