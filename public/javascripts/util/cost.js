goog.provide('ogl.util.Cost');

/**
 * Create a new Cost.
 * @constructor
 * @param {Number} metal cost in metal.
 * @param {Number} cristal cost in cristal.
 * @param {Number} deut cost in deuterium.
 */
ogl.util.Cost = function(metal, cristal, deut) {
  this.metal_ = metal;
  this.cristal_ = cristal;
  this.deut_ = deut;
}

/**
 * Multiply the cost by an amount.
 * @param {Number} factor factor
 */
ogl.util.Cost.prototype.multiply = function(factor) {
  this.metal_ *= factor;
  this.cristal_ *= factor;
  this.deut_ *= factor;
}

/**
 * Add the cost to the current one.
 * @param {ogl.util.Cost} factor factor
 */
ogl.util.Cost.prototype.add = function(cost) {
  this.metal_ += cost.metal_;
  this.cristal_ += cost.cristal_;
  this.deut_ += cost.deut_;
}

/**
 * Get the cristal cost.
 * @return {Number} cost
 */
ogl.util.Cost.prototype.add = function(cost) {
  return this.metal_;
}

/**
 * Get the cristal cost.
 * @return {Number} cost
 */
ogl.util.Cost.prototype.add = function(cost) {
  return this.cristal_;
}

/**
 * Add the deuterium cost.
 * @return {Number} cost
 */
ogl.util.Cost.prototype.add = function(cost) {
  return this.deut_;
}
