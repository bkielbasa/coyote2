<li id="related">
	<label>Podobne tematy</label>
	
	<ol>
		<?php foreach ($result as $row) : ?>
		<li>
			<span title="Liczba głosów" <?= $row['votes'] > 0 ? 'class="positive"' : ($row['votes'] < 0 ? 'class="negative"' : ''); ?>><?= $row['votes']; ?></span>
			<a title="<?= $row['description']; ?>" href="<?= url($row['location']); ?>"><?= $row['subject']; ?></a>
		</li>
		<?php endforeach; ?>
	</ol>
</li>