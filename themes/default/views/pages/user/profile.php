<div class="col_3">
	<article class="container base">
		<header class="cf">
			<div class="property-title">
				<h1><?php echo __("Activity"); ?></h1>
			</div>
		</header>
		<section id="activity_stream" class="property-parameters">
			<?php echo $activity_stream; ?>
		</section>
	</article>
</div>

<div class="col_9">
	<article class="container action-list base">
		<header class="cf">
			<div class="property-title">
				<h1><?php echo __("Rivers"); ?></h1>

				<?php if ( ! $owner): ?>
				<p class="button-white add-parameter follow">
					<a href="#" title="<?php echo __("Subscribe"); ?>">
						<span class="icon"></span><?php echo __("Subscribe to all"); ?>
					</a>
				</p>
				<?php endif; ?>

			</div>
		</header>
		<section id="river_listing" class="property-parameters">
			<!-- List of all rivers accessible to the visitor -->
		</section>
	</article>
	
	<article class="container action-list base">
		<header class="cf">
			<div class="property-title">
				<h1><?php echo __("Buckets"); ?></h1>

				<?php if ( ! $owner): ?>
				<p class="button-white add-parameter follow">
					<a href="#" title="<?php echo __("Subscribe"); ?>">
						<span class="icon"></span><?php echo __("Subscribe to all"); ?>
					</a>
				</p>
				<?php endif; ?>

			</div>
		</header>
		<section id="bucket_listing" class="property-parameters">
			<!-- List of all buckets accessible to the visitor -->
		</section>
	</article>
</div>

<?php echo $profile_js; ?>