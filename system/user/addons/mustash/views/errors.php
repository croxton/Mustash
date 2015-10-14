<?php if ( isset($errors) && count($errors) > 0) : ?>
	<div class="stash_errors">
		<ul><?php foreach($errors AS $msg): ?>
			<li><?php echo $msg?></li>
		<?php endforeach; ?></ul>
	</div>
<?php endif; ?>