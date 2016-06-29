goog.provide('ogl.util.Tech');

/**
 * Create a new Tech.
 * @constructor
 * @param {Number} weapon weapon level.
 * @param {Number} shield shield level.
 * @param {Number} armor armor level.
 */
ogl.util.Tech = function(weapon, shield, armor) {
  this.weapon_ = weapon;
  this.shield_ = shield;
  this.armor_ = armor;
}

/**
 * Create a clone of the tech.
 * @return {ogl.util.Tech} clone.
 */
ogl.util.Tech.prototype.clone = function() {
  return new ogl.util.Tech(this.weapon_, this.shield_, this.armor_);
}

/**
 * Return the weapon tech level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getWeapon = function() {
  return this.weapon_;
}

/**
 * Return the weapon shield level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getShield = function() {
  return this.shield_;
}

/**
 * Return the armor tech level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getArmor = function() {
  return this.armor_;
}

/**
 * Return the weapon tech level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getWeaponFactor = function() {
  return (1 + this.weapon_ / 10);
}

/**
 * Return the weapon shield level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getShieldFactor = function() {
  return (1 + this.shield_ / 10);
}

/**
 * Return the armor tech level.
 * @return {Number} weapon levl.
 */
ogl.util.Tech.prototype.getArmorFactor = function() {
  return (1 + this.armor_ / 10);
}
