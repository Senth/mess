<div id="menu">
<div id="menu-left"></div>
<div id="menu-inner">
<?php if (isset($buttons)) {
	foreach($buttons as $button) {
		// Create a button with link
		if ($this->router->fetch_class() != $button['link']) {
			echo '	<a href="' . site_url($button['link']) . '">' . $button['name'] . "</a>\n";
		}
		// Create a 'button' without link
		else {
			echo '<p class="current">' . $button['name'] . '</p>';
		}
	}
}
?>
</div>
<div id="menu-right"></div>
</div>
