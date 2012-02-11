/**
 * Triggers an event.
 * @param $eventName name of the event. Should not contain 'event.' in 'event.pageChanged',
 * only pageChanged shall be specified.
 */
function triggerEvent($eventName) {
	var $subscribers = $('.subscriber_' + $eventName);
	$subscribers.trigger('event.' + $eventName);
};
