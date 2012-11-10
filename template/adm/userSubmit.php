<script type="text/javascript">
<!--
	$(document).ready(function()
	{
		$('.page-menu li a').each(function(index)
		{
			$(this).bind('click', function()
			{
				var element = $('fieldset').not('.group');

				$('.page-menu li a').removeClass('focus');
				$(this).addClass('focus');

				element.hide();
				$(element[index]).show();
			});

			<?php if (isset($input->get->start)) : ?>
			$('.page-menu li:last a').trigger('click');
			<?php endif; ?>
		});
	});

	function setGroup(button)
	{
		$(button).val('Proszę czkać...').attr('disabled', 'disabled');

		var queryString = '';
		$('.group input:checkbox:checked').each(function(i, checked)
		{
			queryString += '&g[]=' + $(checked).val();
		});

		$.get('<?= url('adm/User/__group'); ?>?id=<?= @$user_id; ?>' + queryString, {},
			function(data)
			{
				if (data.error)
				{
					$('.error').html(data.error).show();
				}
				else
				{
					$('.error').hide();
				}

				if (data.message)
				{
					$('.message').html('');

					for (var element in data.message)
					{
						$('.message').append(data.message[element] + '<br />');
					}
					$('.message').show();
				}

				$(button).val('Zapisz zmiany').attr('disabled', '');
			}, 'json'
		);
	}
//-->
</script>

<h1><?= @$user_name; ?></h1>

<div class="page-menu">
	<ul>
		<li><a class="focus">Podstawowe dane</a></li>
		<li><a>Informacje dodatkowe</a></li>
		<?php if (@$user_id) : ?>
		<li><a>Informacje o użytkowniku</a></li>
		<li><a>Grupy użytkownika</a></li>
		<li><a>Aktywność użytkownika</a></li>
		<?php endif; ?>
	</ul>
</div>
<div class="menu-block">

	<?= Form::openMultipart('adm/User/Submit/' . @$user_id, array('method' => 'post')); ?>
		<fieldset style="border: none;">
			<ol>
				<li>
					<label>Nazwa użytkownika <em>*</em></label>
					<?= Form::input('name', $input->post('name', $input->post('name', @$user_name))); ?>
					<ul><?= $filter->formatMessages('name'); ?></ul>
				</li>
				<li>
					<label>E-mail</label>
					<?= Form::input('email', $input->post('email', $input->post('email', @$user_email))); ?>
					<ul><?= $filter->formatMessages('email'); ?></ul>
				</li>
				<?php if (Auth::get('a_password')) : ?>
				<li>
					<label>Hasło</label>
					<?= Form::password('password', $input->post('password')); ?>
					<ul><?= $filter->formatMessages('password'); ?></ul>
				</li>
				<li>
					<label>Hasło powtórnie</label>
					<?= Form::password('password_confirm', $input->post('password_confirm')); ?>
					<ul><?= $filter->formatMessages('password_confirm'); ?></ul>
				</li>
				<?php endif; ?>
				<li>
					<label>Konto aktywne</label>
					<?= Form::select('active', Form::option(array('Nie', 'Tak'), $input->post('active', @$user_active))); ?>
				</li>
				<li>
					<label>Potwierdzony adres e-mail</label>
					<?= Form::select('confirm', Form::option(array('Nie', 'Tak'), $input->post('confirm', @$user_confirm))); ?>
				</li>
				<?php if (sizeof($groupList) > 1) : ?>
				<li>
					<label>Domyślna grupa</label>
					<?= Form::select('group', Form::option($groupList, $input->post('group', @$user_group))); ?>
				</li>
				<?php endif; ?>
				<li>
					<label title="Możesz ograniczyć dostęp do profilu, jedynie z określonych adresów IP. Można użyć operatora *">Dostęp z adresów IP:</label>

					<fieldset class="group">
						<ol>
							<li style="margin-top: 0">
								<?= Form::text('ip[]', @$ip[0], array('tabindex' => 1, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[1], array('tabindex' => 2, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[2], array('tabindex' => 3, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[3], array('tabindex' => 4, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>

							<li>
								<?= Form::text('ip[]', @$ip[4], array('tabindex' => 5, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[5], array('tabindex' => 6, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[6], array('tabindex' => 7, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[7], array('tabindex' => 8, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>

							<li>
								<?= Form::text('ip[]', @$ip[8], array('tabindex' => 9, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[9], array('tabindex' => 10, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[10], array('tabindex' => 11, 'maxlength' => 3, 'style' => 'width: 20px')); ?> .
								<?= Form::text('ip[]', @$ip[11], array('tabindex' => 12, 'maxlength' => 3, 'style' => 'width: 20px')); ?>
							</li>
						</ol>
					</fieldset>
				</li>
				<li>
					<label>&nbsp;</label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>

		<fieldset style="border: none; display: none;">
			<ol>
				<?= $form; ?>

				<li>
					<label>&nbsp;</label>
					<?= Form::submit('', 'Zapisz zmiany'); ?>
				</li>
			</ol>
		</fieldset>
	<?= Form::close(); ?>

	<?php if (@$user_id) : ?>
	<fieldset style="border: none; display: none;">
		<ol>
			<li>
				<label>Data rejestracji</label>
				<?= User::formatDate($user_regdate); ?>
			</li>
			<li>
				<label>Ostatnia wizyta</label>
				<?= User::formatDate($user_lastvisit); ?>
			</li>
			<li>
				<label>Ilość wizyt</label>
				<?= $user_visits; ?>
			</li>
			<li>
				<label>Ostatnio z IP</label>
				<abbr class="whois-ip"><?= $user_ip; ?></abbr>
			</li>
			<li>
				<label>Ostatnie logowanie</label>
				<?= def($user_ip_login, 'Brak danych'); ?>
			</li>
			<li>
				<label>Niedane logowanie</label>
				<?= def($user_ip_invalid, 'Brak danych'); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="border: none; display: none;">

		<p class="error" style="display: none;"></p>
		<p class="message" style="display: none;"></p>

		<ol>
			<li>
				<label>Grupy użytkownika</label>

				<fieldset class="group">
					<ol>
						<?php foreach ($group as $row) : ?>
						<li><?= Form::checkbox('group[]', $row['group_id'], empty($row['user_id']) ? false : true); ?> <?= $row['group_name']; ?></li>
						<?php endforeach; ?>
					</ol>
				</fieldset>
			</li>
			<li>
				<label>&nbsp;</label>
				<?= Form::button('', 'Zapisz zmiany', array('onclick' => 'setGroup(this);')); ?>
			</li>
		</ol>
	</fieldset>

	<fieldset style="border: none; display: none;">
		<?php if ($pagination->getTotalPages() > 1) : ?>
		<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
		<?php endif; ?>

		<table style="border: none;">
			<caption>Dziennik zdarzeń użytkownika</caption>
			<thead>
				<tr>
					<?= Sort::displayTh('log_id', 'ID'); ?>
					<?= Sort::displayTh('page_id', 'ID strony'); ?>
					<?= Sort::displayTh('log_type', 'Zdarzenie'); ?>
					<?= Sort::displayTh('user_name', 'Użytkownik'); ?>
					<?= Sort::displayTh('log_time', 'Data i czas'); ?>
					<?= Sort::displayTh('log_ip', 'IP'); ?>
				</tr>
			</thead>
			<tbody>
				<?php if ($log) : ?>
				<?php foreach ($log as $row) : ?>
				<tr>
					<td><?= $row['page_id'] ? Html::a(url('adm/Page/View/' . $row['page_id']), $row['log_id']) : $row['log_id']; ?></td>
					<td><?= $row['page_id'] ? Html::a(url($row['location_text']), $row['page_subject']) : '<i>Brak</i>'; ?></td>
					<td>
						<?= element($logType, $row['log_type']); ?>

						<?php if ($row['log_message']) : ?>
						<br />» <small><?= $row['log_message']; ?></small>
						<?php endif; ?>
					</td>
					<td><?= $row['user_name']; ?></td>
					<td><?= User::date($row['log_time']); ?></td>
					<td><abbr class="whois-ip"><?= $row['log_ip']; ?></abbr></td>
				</tr>
				<?php endforeach; ?>
				<?php else : ?>
				<tr>
					<td colspan="7" style="text-align: center;">Brak rekordów spełniających podane kryteria.</td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<?php if ($pagination->getTotalPages() > 1) : ?>
		<p>Strony [<?= $pagination; ?>] z <?= $pagination->getTotalPages(); ?></p>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>
</div>