goog.provide('ogl.util.Division');

/**
 * Create a new divisipn.
 * @constructor
 * @param {Number} amount Amount of untis in the division.
 */
ogl.util.Division = function(amount) {
	this.amount = amount;
	this.integrity = amount;
};

/**
 * Clone the current object.
 * @return {ogl.util.Division} clone.
 */
ogl.util.Division.prototype.clone = function() {
	return new ogl.util.Division(
		this.amount,
		this.integrity
	);
};

/**
 * Return the norm of the integrity of this division.
 * @return {Number} norm.
 */
ogl.util.Division.prototype.integrityNorm = function() {
	return this.integrity / this.amount;
};
