var EPSILON_DEFAULT = 0.01;

/**
 * Compare float values to see if they are close enough. If epsilon isn't defined
 * it will default to 0.001
 * @param $lhs left hand side of the comparison
 * @param $rhs right hand side of the comparison
 * @param $epsilon the threshold value used to compare if they are close enough
 */
function compareFloat($lhs, $rhs, $epsilon) {
	if ($epsilon === undefined) {
		return Math.abs($lhs - $rhs) < EPSILON_DEFAULT;
	} else {
		return Math.abs($lhs - $rhs) < $epsilon;
	}
}

/**
 * Checks if a point is inside the specified window
 * @param window an object which has the elements: top, left, bottom, right
 * @param pointX x position of the point
 * @param pointY y position of the point
 */
function mouseInside($window, $pointX, $pointY) {
	if ($pointX < $window.left) {
		return false;
	} else if ($pointX > $window.right) {
		return false;
	} else if ($pointY < $window.top) {
		return false;
	} else if ($pointY > $window.bottom) {
		return false;
	}

	return true;
}
