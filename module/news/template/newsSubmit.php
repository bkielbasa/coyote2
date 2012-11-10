<script type="text/javascript">
<!--

	var images = [];
	images[0] = '<?= Url::base(); ?>module/news/template/img/thumbnail.png';

	function loadThumbnails()
	{
		if (images.length > 1)
		{
			var pointer = 0;
			$('<li id="thumbnail"><label>Miniatura</label> </li>').insertAfter($('#submit-form li:eq(1)'));

			$('#thumbnail').append('<div><a class="prev"></a> <img width="50" height="50" src="' + images[0] + '" /> <a class="next"></a></div>');
			$('#thumbnail').append('<small style="padding-left: 15px; padding-top: 5px">Obraz <strong>1</strong> z <strong>' + images.length + '</strong></small>');
			$('#submit-form').append('<input type="hidden" name="thumbnail" value="" />');

			function setThumbnail()
			{
				$('#thumbnail img').attr('src', images[pointer]);
				$('#submit-form input[name=thumbnail]').val(pointer == 0 ? '' : images[pointer]);

				$('#thumbnail small strong:eq(0)').text(pointer + 1);
			}

			$('#thumbnail .next').click(function()
			{
				if (images.length > pointer + 1)
				{
					++pointer;
					setThumbnail();
				}
			});

			$('#thumbnail .prev').click(function()
			{
				if (pointer -1 >= 0)
				{
					--pointer;
					setThumbnail();					
				}
			});
			
		}
		
	}

	function loadSite(url)
	{
		if (!$('#submit-form input[name=title]').val().length && !$('#submit-form textarea[name=content]').val().length)
		{
			$.ajax(
			{
				url: '<?= url($page->getLocation()) . '/fetch'; ?>',
				data: {'url': url}, 
				dataType: 'json',
				type: 'GET',
				beforeSend: function()
				{
					$('#submit-form input[name=url]').val(url);
					$('#submit-form :input').attr('disabled', 'disabled');
					$('body').append('<div id="news-loader-layer"></div>');

					$('#news-loader-layer').css(
					{
						height:					$('body').height()
					});		

					$('body').append('<div id="news-loader">Proszę czekać...</div>');		
				},
				success: function(data)
				{
					$('#submit-form :input').removeAttr('disabled');
					$('#news-loader').remove();
					$('#news-loader-layer').remove();

					$('#submit-form input[name=title]').val(data.title);
					$('#submit-form textarea[name=content]').val(data.description);

					$('#thumbnail').remove();
					$(':hidden[name=thumbnail]').remove();
					$('#submit-form ul').remove();

					thumbnail = images[0];
					images = [];
					images.push(thumbnail);

					if (data.images.length)
					{
						for (item in data.images)
						{
							images.push(data.images[item]);
						}
					}

					loadThumbnails();
				}
			});
		}
	}
	
	$(document).ready(function()
	{
		$('input[name=url]').blur(function()
		{
			element = this;
			url = $(element).val();
			
			if ($.trim(url).length)
			{
				loadSite(url);						
			}			
		});

		<?php if ($url) : ?>
		loadSite('<?= $url; ?>');
		<?php endif; ?>	

		$('#submit-form').submit(function()
		{
			$(':submit', this).attr('disabled', 'disabled');
		});	
	});

//-->
</script>

<?= $form; ?>