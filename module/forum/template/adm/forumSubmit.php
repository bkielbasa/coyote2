<script type="text/javascript">

	function selectType(type)
	{
		index = type.options[type.selectedIndex].value;
		if (index == <?= Forum_Model::NORMAL; ?>)
		{
			$('#fieldUrl').hide();
			$('#fieldDescription').show();
		}
		else if (index == <?= Forum_Model::LINK; ?>)
		{
			$('#fieldUrl').show();
			$('#fieldDescription').hide();
		}
		else if (index == <?= Forum_Model::CATEGORY; ?>)
		{
			$('#fieldUrl').hide();
			$('#fieldDescription').hide();
		}
	}
</script>

<?= Form::open('adm/Forum/Submit/' . @$forum_id, array('method' => 'post')); ?>
	<fieldset>
		<legend>Edycja forum</legend>

		<ol>
			<li>
				<label>Nazwa forum</label> 
				<?= Form::input('subject', $input->post('subject', @$forum_subject)); ?> <ul><?= $filter->formatMessages('subject'); ?></ul>
			</li>
			<li>
				<label>Forum macierzyste</label>
				<?= Form::select('parent', Form::option($forum, @$forum_parent, true, $forumAttributes)); ?>
			</li>
			<li id="fieldDescription" style="display: <?= $forum_type == Forum_Model::NORMAL ? '' : 'none'; ?>">
				<label>Opis forum</label>
				<?= Form::textarea('description', $input->post('description', @$forum_description), array('cols' => 50, 'rows' => 10)); ?> <ul><?= $filter->formatMessages('description'); ?></ul>
			</li>
			<li>
				<label>Typ forum</label>
				<?= Form::select('type', Form::option(__User::getForumType(), $input->post('type', @$forum_type)), array('onchange' => 'selectType(this)')); ?> <ul><?= $filter->formatMessages('type'); ?></ul>
			</li>
			<li id="fieldUrl" style="display: <?= isset($forum_id) && $forum_type == Forum_Model::LINK ? '' : 'none'; ?>">
				<label>Przekierowanie</label>
				<?= Form::input('url', $input->post('url', @$forum_url)); ?> <ul><?= $filter->formatMessages('url'); ?></ul>
			</li>
			<li>		
				<label>Zasady forum</label>
				<?= Form::textarea('rules', $input->post('rules', @$forum_rules), array('cols' => 50, 'rows' => 10)); ?> <ul><?= $filter->formatMessages('rules'); ?></ul>
			</li>
			<li>
				<label>Forum zablokowane</label>
				<?= Form::checkbox('lock', 1, (bool)def(@$forum_lock, 0)); ?> <ul><?= $filter->formatMessages('lock'); ?></ul>
			</li>
			<li>
				<label>Kasowanie tematów</label>
				<?= Form::input('prune', $input->post('prune', @$forum_prune), array('size' => 4)); ?> <small>(ilość dni; 0 - tematy nie są kasowane)</small> <ul><?= $filter->formatMessages('prune'); ?></ul>
			</li>
			<?php if (@$forum_id) : ?>
			<li>
				<label>Kolejność wyświetlania</label>
				
				<fieldset>
					<ol>
						<?php foreach ($order as $count => $row) : ?>							
						<li>
							<label>
								<?php if ($count == 0) 
								{
									echo Html::a('?f=' . $row['forum_id'] . '&mode=down', Html::img(url('template/adm/img/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
									echo Html::img(url('template/adm/img/up_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
								}		
								else if ($count == count($order) -1)
								{
									echo Html::img(url('template/adm/img/down_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
									echo Html::a('?f=' . $row['forum_id'] . '&mode=up', Html::img(url('template/adm/img/up.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
								}
								else
								{
									echo Html::a('?f=' . $row['forum_id'] . '&mode=down', Html::img(url('template/adm/img/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
									echo Html::a('?f=' . $row['forum_id'] . '&mode=up', Html::img(url('template/adm/img/up.gif'), array('style' => 'margin: 0 4px 0 4px')));
								}
								?>
							</label> 
							<?= $row['forum_subject']; ?>
						</li>
						<?php endforeach; ?>
					</ol>
				</fieldset>
			</li>
			<?php endif; ?>
			<li>
				<label></label>
				<?= Form::submit('', 'Zapisz zmiany'); ?>
				<?php if (@$forum_id) : ?>
				<?= Form::button('del', 'Usuń forum', array('onclick' => 'window.location.href = \'' . url('adm/Forum/Delete/' . $forum_id) . '\';')); ?>
				<?php endif; ?>
			</li>
		</ol>
	</fieldset>	
</form>