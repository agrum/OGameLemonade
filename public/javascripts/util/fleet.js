goog.provide('ogl.util.Fleet');

/**
 * Create a new fleet.
 * @constructor
 * @param {Number} amount Amount of untis in the division.
 */
ogl.util.Fleet = function(composition, name) {
  this.composition = groups;
  this.name = name;
};

/**
 * Clone current fleet.
 * @return {ogl.util.Fleet} clone.
 */
ogl.util.Fleet.prototype.clone = function() {
	var composition = [];
	for(var i in this.composition){
		composition.push(this.composition[i].clone());
	}

	return new ogl.util.Fleet(composition, this.name);
};

/**
 * Return the average rapid fire received one this fleet from a specific brand.
 * @return {Number} rapid fire received by brand.
 */
ogl.util.Fleet.prototype.getRapidFireReceivedFrom = function(model) {
	var tech = [0, 0, 0];
	var group = new ogl.util.Group(p_model, 1, tech);

	return group->rapidFireAgainstComposition(this.composition);
};

/**
 * Return the fleet's name.
 * @return {String} name.
 */
ogl.util.Fleet.prototype.getName = function() {
	return this.name;
};

/**
 * Return the fleet's composition.
 * @return {[ogl.util.Group]} composition.
 */
ogl.util.Fleet.prototype.getComposition = function() {
	return this.composition;
};

/**
 * Return the cost of the fleet in mineral.
 * @return {Number} value.
 */
ogl.util.Fleet.prototype.getValue = function() {
	var value = 0;

	for(var i in this.composition){
		this.composition[i].getValue();
	}
};

/**
 * Change the value of a fleet. The composition stays unchanged. Only the amount of units change.
 * @param {Number} value value.
 */
ogl.util.Fleet.prototype.setValue = function(value) {
	var changeCoef = value / this.getValue();

	for(var i in this.composition){
		this.composition[i].multiplyValue(changeCoef);
	}
};

/**
 * enagage two fleets in combat.
 * @param {ogl.util.Fleet} fleet1 Fleet one.
 * @param {ogl.util.Fleet} fleet2 Fleet two.
 */
ogl.util.Fleet.engage = function(fleet1, fleet2) {
	var ended = false;

	for(var i = 0; i < 6 && !ended; i++){
		console.log("Round " + (i+1));
		ended = ogl.util.Fleet.processRound(fleet1, fleet2);
	}

	return [fleet1, fleet2];
};

/**
 * execute a round between two fleets.
 * @param {ogl.util.Fleet} fleet1 Fleet one.
 * @param {ogl.util.Fleet} fleet2 Fleet two.
 */
ogl.util.Fleet.processRound = function(fleet1, fleet2) {
	fleet1.initRound_();
	fleet2.initRound_();

	console.log("FleetA");
	fleet1.attackedBy_(fleet2);

	console.log("FleetA");
	fleet2.attackedBy_(fleet1);

	fleet1.applyRound_();
	fleet2.applyRound_();

	return fleet1.isDestroyed() || fleet2.isDestroyed();
};

/**
 * Returns true if the fleet has been destroyed.
 * @private
 * @return {Boolean} isDestroyed.
 */
ogl.util.Fleet.prototype.isDestroyed_ = function() {
	for(var i in this.composition){
		if(this.composition[i].getAmountUnit())
			return false;
	}

	return true;
};

/**
 * prepare fleet for a new round.
 * @private
 * @return {Boolean} isDestroyed.
 */
ogl.util.Fleet.prototype.initRound_ = function() {
	for(var i in this.composition){
		this.composition[i].initRound();
	}
};

/**
 * process an atatck received on this fleet.
 * @private
 * @param {ogl.util.Fleet} fleet1 Attacking fleet.
 */
ogl.util.Fleet.prototype.attackedBy_ = function(fleet) {
	for(var i in fleet.composition){
		var attackingGroup = fleet.composition[i];

		if(attackingGroup.getAmountUnit() <= 0.0)
			continue;

		var amountDefendingUnits = 0;
		for(var i in this.composition)
			amountDefendingUnits += this.composition[i].getAmountUnit();

		var shots = attackingGroup.getAmountUnit() * attackingGroup.rapidFireAgainstComposition(this.m_groupArr);
		for(var i in this.composition)
		{
			var defendingGroup = this.composition[i];
			if(defendingGroup.getAmountUnitTemp() <= 0.0)
				continue;

			var proportion = defendingGroup.getAmountUnit() / amountDefendingUnit;
			defendingGroup.receiveWave(shots * proportion, attackingGroup.getModel().getPower());
		}
	}
};

/**
 * finalize a round (remove the unit destroyed).
 * @private
 */
ogl.util.Fleet.prototype.applyRound_ = function() {
	for(var i in this.composition)
		this.composition.applyRound();
};
