<div class="logo">
	<img src="<?php echo esc_url_raw(WETEXT_PLUGIN_URL.'images/logo_big.png'); ?>" width="327" height="161" alt="wetext logo" class="logoimg" />
</div>

<ul class="navigation">
	<li><a class="<?php echo $settings_active_class; ?>" href="<?php echo esc_url_raw($base_path.'&mode=settings'); ?>">Settings</a></li>
	<li><a class="<?php echo $subscribes_active_class; ?>" href="<?php echo esc_url_raw($base_path.'&mode=subscribers'); ?>">Subscribers</a></li>
	<li><a class="<?php echo $messages_active_class; ?>" href="<?php echo esc_url_raw($base_path.'&mode=messages'); ?>">Messages</a></li>
</ul>