$(document).ready(function() {
	var DIV_OFFSET_X = 100;
	var DIV_OFFSET_Y = 25;

	var $debug_html = '<div id="debug_message" style="position: absolute; display: none; width: 200px; font-size: 10pt; background-color: #fff; border: 1px solid #000; padding: 2px"></div>';

	$('body').append($debug_html);

	$(document).bind('mousemove', function(event) {
		if ($('#debug_message').css('display') == 'block') {
			var $messageOffset =  {
				left: event.pageX + DIV_OFFSET_X,
				top: event.pageY + DIV_OFFSET_Y
			};

			$('#debug_message').offset($messageOffset);
		}
	});
});

/**
 * Displays a debug message in a window beside the mouse
 * @param $message the debug message to display
 */
function debugMessage($message) {
	$('#debug_message').html($message).css('display', 'block');
}
