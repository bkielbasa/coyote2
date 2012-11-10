<script type="text/javascript">
<!--

	$(document).ready(function()
	{
		$(window).hashchange(function()
		{
			$('#box-notify, #box-config').toggle();
			$('a[href=#config], a[href=#notify]').parent().toggleClass('focus');
		});

		$(window).hashchange();
	});
//-->
</script>


<?php if (!User::data('email')) : ?>
<p class="note">Nie podałeś adresu e-mail. Dopóki nie podasz adresu e-mail, nie będziesz otrzymywał żadnych powiadomień e-mail z serwisu.</p>
<?php elseif (!User::data('confirm')) : ?>
<p class="note">Nie potwierdziłeś adresu e-mail. Dopóki nie potwierdzisz adresu e-mail, nie będziesz dostawał żadnych powiadamień na e-mail. <a href="<?= url('@user?controller=Confirm'); ?>">Dowiedz się więcej.</a></p>
<?php endif; ?>

<?php $selected = 'notify-icon'; include('_partialUserMenu.php'); ?>

<div id="box-user" class="box">

	<div class="box box-pm" style="margin-top: 40px;">

		<ul class="box-menu">
			<li class="focus"><a href="#notify">Konfiguracja powiadomień</a></li>
			<li><a href="#config">Powiadomienia</a></li>
		</ul>

		<div id="box-config">
			<?= Form::open('', array('method' => 'post')); ?>
				<table id="notifications">
					<thead>
						<tr>
							<th style="text-align: left;">Powiadamiaj o...</th>
							<th>Informacja w profilu</th>
							<th>Informacja na e-mail</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($notifications as $row) : ?>
						<tr>
							<td><?= $row['notify_name']; ?></td>

							<td style="background-color: #f8f8f8; text-align: center;">
								<?= Form::checkbox('notify[' . $row['notify_id'] . '][]', 1, isset($notifiers[$row['notify_id']]) ? ($notifiers[$row['notify_id']] & Notify::PROFILE) : false); ?>
							</td>
							<td style="background-color: #f8f8f8; text-align: center;">
								<?= Form::checkbox('notify[' . $row['notify_id'] . '][]', 2, isset($notifiers[$row['notify_id']]) ? ($notifiers[$row['notify_id']] & Notify::EMAIL) : false, !User::data('confirm') || !$row['notify_email'] ? array('disabled' => 'disabled') : array()); ?>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>

				</table>

				<?= Form::submit('', 'Zapisz zmiany'); ?>
			<?= Form::close(); ?>
		</div>

		<div id="box-notify" style="display: none;">
			<a class="feed" title="Powiadomienia przez kanał Atom" href="<?= url('@user?controller=Notify&action=Feed'); ?>">Przez Atom</a>
			<?php if ($pagination->getTotalPages() > 1) : ?>
			<p>Strony <?= $pagination ;?> z <?= $pagination->getTotalPages(); ?></p>
			<?php endif; ?>

			<?php foreach ($headers as $date => $rowset) : ?>
			<h3><?= $date; ?></h3>

			<ul class="notify">
				<?php foreach ($rowset as $row) : ?>
				<li <?= $row['header_read'] && $row['header_read'] < User::data('session_start') ? '' : 'class="unread"'; ?>>
					<a href="<?= url($row['header_url']); ?>">
						<?php if ($row['user_photo']) : ?>
						<img alt="" src="<?= Url::site(); ?>store/_a/<?= $row['user_photo']; ?>" width="35" height="30" />
						<?php else : ?>
						<img alt="" src="<?= Url::site(); ?>template/img/avatar.jpg" width="35" height="30" />
						<?php endif; ?>

						<?= $row['header_message']; ?>
						<small><?= User::date($row['header_time']); ?></small>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endforeach; ?>

			<?php if ($pagination->getTotalPages() > 1) : ?>
			<p>Strony <?= $pagination ;?> z <?= $pagination->getTotalPages(); ?></p>
			<?php endif; ?>
		</div>

	</div>

</div>

<div style="clear: both;"></div>