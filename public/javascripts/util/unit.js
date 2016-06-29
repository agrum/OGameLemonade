goog.provide('ogl.util.Unit');

goog.provide('ogl.util.Unit');

var rapidFire = {
  unit: String,
  fire: Number
};

var unitSchema = new mongoose.Schema({
  _id: String,
  name: String,
  isShip: Boolean,
  canAttack: Boolean,
  canDefend: Boolean,
  metal: Number,
  cristal: Number,
  deut: Number,
  hull: Number,
  shield: Number,
  power: Number,
  rapidFireArray:[ rapidFire ]
});

/**
 * Create a new Unit.
 * @constructor
 * @param {unitSchema} unitSchema full unit object data.
 */
ogl.util.Unit = function(unitSchema) {
  this._id_ = unitSchema._id;
  this.name_ = unitSchema.name;

  this.isShip_ = unitSchema.isShip;
  this.canAttack_ = unitSchema.canAttack;
  this.canDefend_ = unitSchema.canDefend;

  this.cost_ = new ogl.util.Cost(unitSchema.metal, unitSchema.cristal, unitSchema.deut);

  this.power_ = unitSchema.power;
  this.hull_ = unitSchema.hull;
  this.shield_ = unitSchema.shield;

  this.rapidFireArray_ = unitSchema.rapidFireArray;
}

/**
 * Return the rapid fire of this unti against an other.
 * @return {Number} rapidfire.
 */
ogl.util.Unit.prototype.rapidfireAgainstUnit = function(unit) {
  if(this.rapidFireArray_.indexOf(unit._id_) != -1)
    return this.rapidFireArray_[unit._id_];
  return 1;
};

/**
 * Return the cost of this unit.
 * @return {ogl.util.Group} clone.
 */
ogl.util.Unit.prototype.getCost = function() {
  return this.cost_;
};

/**
 * Return the power value.
 * @return {ogl.util.Group} clone.
 */
ogl.util.Unit.prototype.getPower = function() {
  return this.power_;
};

/**
 * Return the cost of this unit.
 * @return {ogl.util.Group} clone.
 */
ogl.util.Unit.prototype.getShield = function() {
  return this.shield_;
};

/**
 * Return the cost of this unit.
 * @return {ogl.util.Group} clone.
 */
ogl.util.Unit.prototype.getHull = function() {
  return this.hull_;
};
