<span class="pagination">
	<?php if ($previousPage) : ?>
		<a href="<?= $url . 'start=' . ($previousPage -1) * $itemsPerPage; ?>">&laquo;&nbsp;</a>
	<?php endif; ?>

	<?php if (!$totalPages) : ?>

		<strong>1</strong>

	<?php elseif ($totalPages < 10) : ?>

		<?php for ($i = 1; $i <= $totalPages; $i++) : ?>

			<?php if ($i == $currentPage) : ?>
				<strong><?= $i; ?></strong>
			<?php else : ?>
				<a href="<?= $url . 'start=' . ($i -1) * $itemsPerPage; ?>"><?= $i; ?></a>
			<?php endif; ?>
		<?php endfor; ?>

	<?php elseif ($currentPage < 6) : ?>

		<?php for ($i = 1; $i <= 7; $i++) : ?>

			<?php if ($i == $currentPage) : ?>
				<strong><?= $i; ?></strong>
			<?php else : ?>
				<a href="<?= $url . 'start=' . ($i -1) * $itemsPerPage; ?>"><?= $i; ?></a>
			<?php endif; ?>

		<?php endfor; ?>

		&hellip;
		<a href="<?= $url . 'start=' . ($totalPages -2) * $itemsPerPage; ?>"><?= $totalPages -1; ?></a>
		<a href="<?= $url . 'start=' . ($totalPages -1) * $itemsPerPage; ?>"><?= $totalPages; ?></a>

	<?php elseif ($currentPage > $totalPages -5) : ?>

		<a href="<?= $url . 'start=0'; ?>">1</a>
		<a href="<?= $url . 'start=' . $itemsPerPage; ?>">2</a>
		&hellip;

		<?php for ($i = $totalPages -6; $i <= $totalPages; $i++) : ?>

			<?php if ($i == $currentPage) : ?>
				<strong><?= $i; ?></strong>
			<?php else : ?>
				<a href="<?= $url . 'start=' . ($i -1) * $itemsPerPage; ?>"><?= $i; ?></a>
			<?php endif; ?>

		<?php endfor; ?>
		
	<?php else : ?>

		<a href="<?= $url . 'start=0'; ?>">1</a>
		<a href="<?= $url . 'start=' . $itemsPerPage; ?>">2</a>
		&hellip;

		<?php for ($i = $currentPage -2; $i <= $currentPage + 2; $i++) : ?>

			<?php if ($i == $currentPage) : ?>
				<strong><?= $i; ?></strong>
			<?php else : ?>
				<a href="<?= $url . 'start=' . ($i -1) * $itemsPerPage; ?>"><?= $i; ?></a>
			<?php endif; ?>

		<?php endfor; ?>

		&hellip; 
		<a href="<?= $url . 'start=' . ($totalPages -2) * $itemsPerPage; ?>"><?= $totalPages -1; ?></a>
		<a href="<?= $url . 'start=' . ($totalPages -1) * $itemsPerPage; ?>"><?= $totalPages; ?></a>

	<?php endif; ?>

	<?php if ($nextPage) : ?>
		<a href="<?= $url . 'start=' . ($nextPage -1) * $itemsPerPage; ?>">&nbsp;&raquo;</a>		
	<?php endif; ?>
</span>