<h1>Reguły routingu</h1>

<p>Umożliwia konfiguracje mechanizmu routingu, adresów URL oraz akcji, które będą podejmowane wskutek wykrycia określonych adresów.
<b>UWAGA!</b> Zmiana konfiguracji tego mechanizmu może spowodować nieprawidłowe działanie witryny!</p>

<?php

	function img_sort($order, $max)
	{
		if ($order == 1) 
		{
			echo Html::a('?order=' . $order . '&mode=down', Html::img(Media::img('adm/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
			echo Html::img(Media::img('adm/up_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
		}		
		else if ($order == $max)
		{
			echo Html::img(Media::img('adm/down_disabled.gif'), array('style' => 'margin: 0 4px 0 4px')); 
			echo Html::a('?order=' . $order . '&mode=up', Html::img(Media::img('adm/up.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
		}
		else
		{
			echo Html::a('?order=' . $order . '&mode=down', Html::img(Media::img('adm/down.gif'), array('style' => 'margin: 0 4px 0 4px'))); 
			echo Html::a('?order=' . $order . '&mode=up', Html::img(Media::img('adm/up.gif'), array('style' => 'margin: 0 4px 0 4px')));
		}
	}
?>

<?php if (!is_writeable('config/route.xml')) : ?>
<p class="error">Zapis konfiguracji routingu jest niemożliwy! Zmień prawa dostępu do plik <i>/config/route.xml</i> na <b>0666</b></p>
<?php endif; ?>
<table>
	<caption>Reguły routingu</caption>
	<thead>
		<tr>
			<th>Nazwa reguły</th>
			<th>Reguła</th>
			<th>Kontroler</th>
			<th>Akcja</th>
			<th>Łącznik</th>
			<th>Kolejność</th>
		</tr>
	</thead>	
	<tbody>
		<?php $counter = sizeof($router->getRoutes()); ?>
		<?php foreach ($router->getRoutes() as $row) : ?>
		<tr>
			<td><?= Html::a(url('adm/Route/Submit/' . $row['name']), $row['name']); ?></td>
			<td><?= $row['url']; ?></td>
			<td><?= def(@$row['controller'], ':controller'); ?></td>
			<td><?= def(@$row['action'], ':action'); ?></td>
			<td><?= isset($row['connector']) ? $connector[$row['connector']] : '(brak)'; ?></td>
			<td><?= img_sort($row['order'], $counter); ?></td>
		</tr>
		<?php endforeach; ?>		
	</tbody>
</table>

<?= Form::button('', 'Dodaj nową regułę', array('class' => 'button', 'onclick' => 'window.location.href = \'' . url('adm/Route/Submit') . '\'')); ?>