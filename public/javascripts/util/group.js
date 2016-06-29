goog.provide('ogl.util.Group');

goog.require('ogl.util.Division');

//Here lies the magic 'woulouloouuuu....'

//Core class of the project. A group is a set of unit of the same type, e.g. 100 Light Fighters.
//Thus the class holds as first members a unit of unit as well as an amount of this unit.

//At the construction,the integrity is set to 1.0, meaning 100%.
//One rule of the fight in OGame is "If a ship has less than 70% of it's hull (here integrity)
//	each consecutive hit (if not deflected) has a chance to make the unit explode". Since
//	this simulator manages groups instead of stand alone units, this concept must be well managed.
//	The solution here is to acknoledge the instable units and dissociate them from the amount
//	of stable units. The group is then divided in two : stable units ($m_stable) and instable
//	ones ($m_unstable). Since the instables result from heavy weaponery, it would be a mistake
//	to share the integrity of stable units with unstable ones. Each group has its own integrity
//	variable.
//The variable set at each round beginning has been explained. There is two other type of
//	variables in this class :
//-Temp variables. Those variables are highly instable. They are bluntly modified during a round
//	and thus can't be used as reference during a round. At each end of round, they are used
//	to update the non-temp variable.
//-InWave variables. Those variable are stable during a whole wave. In this simulator, each round
//	is divided in waves. A wave correspond to a group attacking an other one, thus a set of same
//	units attacking an other set of same units. InWave variables are stable during a whole wave.

/**
 * Create a new group.
 * @constructor
 * @param {ogl.util.Unit} unit Type of unit that composes the group.
 * @param {Number} amount Amount of untis in the group.
 * @param {[Number]} techs Tech levels.
 */
ogl.util.Group = function(unit, amount, techs) {
  this.unit = unit;
	this.tech = tech.clone();
	this.unitPower = this.unit.getPower() * this.tech.getWeaponFactor();
	this.unitShield = this.unit.getShield() * this.tech.getShieldFactor();
	this.unitHull = this.unit.getHull() * this.tech.getHullFactor();
	this.divCoverage = 0.5;
	this.amountDivInteg = 1 + Math.ceil(Math.max(0,Math.log(this.unitHull)) / log(2.0));
	this.amountDivShield = 1 + Math.ceil(Math.max(0,Math.log(this.unitShield)) / log(10.0));

	this.integArr[0] = new ogl.util.Division(amount);
	for(var i = 1; i < this.amountDivInteg; i++)
		this.integArr[i] = new ogl.util.Division(0);
};

/**
 * Create a clone of the group.
 * @return {ogl.util.Group} clone.
 */
ogl.util.Group.prototype.clone = function() {
	var group = new ogl.util.Group(this.unit, 0, this.tech);

	for(var i = 0; i < this.integArr.length; i++)
		this.integArr[$i] = this.integArr[$i].clone();
};

/**
 * Gives the theoric rapidfire of the group against a set of groups.
 * @return {Number} Average rapidfire.
 */
ogl.util.Group.prototype.rapidFireAgainstComposition = function(composition) {
	var rapidFireProba = 0;
	var totalAmountUnit = 0;

	//Get the amount of units on the opposite side
	for(var i in composition){
		totalAmountUnit += composition[i].getAmountUnit();
	}

	//Get the rapid fire against each group,
	//Convert it to a probabilistic rapidfire (p = (r-1)/r)
	//Multuply it to the proportion of unit in the overall package
	for(var i in composition){
		var rapidFireAgainstGroup = this.unit.rapidfireAgainstUnit(composition[i].unit);
		var rapidFireAgainstGroupProba = (rapidFireAgainstGroup-1.0)/rapidFireAgainstGroup;
		rapidFireProba += rapidFireAgainstGroupProba * (composition[i].getAmountUnit() / totalAmountUnit);
	}

	//Convert back the probabilistic rapidfire to the ogame unit system (r = 1/(1-p))
	var rapidFire = 1 / (1-rapidFireProba);

	return rapidFire;
};

/**
 * Returns the group cost in minerals.
 * @return {Number} Cost.
 */
ogl.util.Group.prototype.getValue = function() {
	return this.unit.getCost() * this.getAmountUnit();
};

/**
 * Multiply the amount of units in the group by the input.
 * @return {Number} Cost.
 */
ogl.util.Group.prototype.multiplyValue = function(coef) {
	for(var i in this.integArr) {
		this.integArr[i].amount *= coef;
		this.integArr[i].integrity *= coef;
	}
};

/**
 * Returns the amount of unit outside of rounds rounds.
 * @return {Number} Cost.
 */
ogl.util.Group.prototype.getAmountUnit = function() {
	var amount = 0;
	for(var i in this.integArr) {
		amount += this.integArr[i].amount;
	}
	return amount;
};

/**
 * Returns the amount of unit outside of rounds rounds.
 * @return {Number} Cost.
 */
ogl.util.Group.prototype.getAmountUnit = function() {
	var amount = 0;
	for(var i in this.integArrTemp) {
		amount += this.integArrTemp[i].amount;
	}
	return amount;
};

/**
 * Returns the amount of unit outside of rounds rounds.
 * @return {Number} Cost.
 */
ogl.util.Group.prototype.getAmountUnit = function() {
	var amount = 0;
	for(var i in this.integArrInWave) {
		amount += this.integArrInWave[i].amount;
	}
	return amount;
};

/**
 * Forgot.
 * @return {Number} Forgot.
 */
ogl.util.Group.prototype.getTopIntegrityForDiv_ = function(id) {
	return this.divCoverage * (this.integArr.length - id) / this.integArr.length + (1.0 - this.divCoverage);
};

/**
 * Forgot.
 * @return {Number} Forgot.
 */
ogl.util.Group.prototype.getDivIDFromIntegrityNorm_ = function(integrityNorm) {
	return round((this.integArr.length - 1) * (1.0 - (Math.max(integrityNorm - (1.0 - this.divCoverage), 0.0) / this.divCoverage)));
};

/**
 * Prepare group for a round.
 */
ogl.util.Group.prototype.initRound = function() {
	//old shield design
	this.amountWShieldTemp = this.getAmountUnit();
	this.shieldTemp = this.unitShield*this.getAmountUnit();

	//Integrity
	for (var i in this.integArr)
		this.integArrTemp[i] = this.integArr[i].clone();

	//Shield
	this.shieldArrTemp[0] = new Division(this-getAmountUnit());
	for(var i = 1; i < this.amountDivShield; i++)
		this.shieldArrTemp[i] = new Division(0);
};

/**
 * Part of a round, which is just a set of fires of different magnitudes
 * @param {Number} amountHit Amount of hit received for a specific Group
 * @param {Number} power Power of each hit
 */
ogl.util.Group.prototype.receiveWave = function(amountHit, power) {
	if(this.unitShield > 100*power)
	{
		return; //Deflected TODO wrong
	}

	global $model;
	amountHit = amountHit * this.getAmountUnitTemp() / this.getAmountUnit();
	var amountUnit = this->amountUnitTemp();
	var amountUnitHit = amountUnit * ( 1 - 1/Math.exp(amountHit / amountUnit)); //Magiiiic
	if(amountUnitHit == 0)
		return;

	console.log("Receive wave begin on " + this.unit->name() + " (" + this.getAmountUnitTemp().toFixed(2) +" units)");
	console.log("_Amount of shots : " + amountHit.toFixed(2));
	console.log("_Proportion managed : " + (this->amountUnitTemp() / this.getAmountUnit()).toFixed(2));
	console.log("_Power : " + power);
	console.log("_Amount unit hit : " + amountUnitHit.toFixed(2));

	//Set the constant variables during the wave
	//We want to recall
	//-the average shield on each unit
	//-the probabilty to hit a shield
	//-the amount of unit in each division at the beginning of the wave
	if(this.amountWShieldTemp > 0)
		this.averageShieldInWave = this.shieldTemp / this.amountWShieldTemp;
	else
		this.averageShieldInWave = 0;
	this.probaHitShieldInWave = this.amountWShieldTemp / this.getAmountUnitTemp();
	for(var i in this.integArrTemp)
		this.integArrInWave[i] = this.integArrTemp[i].clone();
	for(var i in this.shieldArrTemp)
		this.shieldArrInWave[i] = this.shieldArrTemp[i].clone();

	var prop = amountHit / amountUnit;
	var amountHitLeft = amountHit - amountUnitHit;
	if(amountHitLeft / amountUnitHit >= 1)
	{
		var invDistribution = window.OGLemonade.presets.invDistribution;

		var proportionTouchedOnceOrMore = 1 - 1/Math.exp(prop);
		var proportion = amountHitLeft / amountUnitHit;
		var proportionNotTouchedMore = 1/Math.exp(proportion);

		this.ackImpact(
				amountUnitHit * proportionNotTouchedMore,
				power,
				1.0);

		var startInvDist = proportionNotTouchedMore;
		var startCumGauss = invDistribution[Math.floor(startInvDist * invDistribution.length)];
		var span = 2.84*Math.pow(5.0 * $proportion, 0.5);
		var leftSpan = Math.min($span/2, $proportion);
		var startCombined = $proportion - $leftSpan;
		//this->debug("__prop : " . $prop . "__<br/>");
		//this->debug("__proportion : " . $proportion . "__<br/>");
		//this->debug("__span : " . $span . "__<br/>");
		//this->debug("__startCombined : " . $startCombined . "__<br/>");
		//this->debug("__startInvDist : " . $startInvDist . "__<br/>");
		//this->debug("__startCumGauss : " . $startCumGauss . "__<br/>");
		//this->debug("__proportionTouchedOnceOrMore : " . $proportionTouchedOnceOrMore . "__<br/>");
		//this->debug("__proportionNotTouchedMore : " . $proportionNotTouchedMore . "__<br/>");

		var div = 4;

		var propOnce = proportionTouchedOnceOrMore * proportionNotTouchedMore / prop;
		var propMore = 0;
		var addOn = [];

		//manage gaussian
		var coverageFractal = 3;
		var covered = 0;
		var coverage = [];
		for(var i = div-1; i >= 0; i--)
		{
			var newCoverage = 1.0 / Math.pow(coverageFractal, i);
			var halfPoint = (1.0 - covered)/2.0 + (1.0 - newCoverage)/2.0;
			var cumGauss = invDistribution[Math.floor((halfPoint * (1.0 - startInvDist) + startInvDist)*invDistribution.length)];
			addOn[i] = 1.0 + startCombined + span*(cumGauss - startCumGauss)/(1.0 - startCumGauss);
			coverage[i] = (newCoverage - covered);
			//this->debug("__Coverage : " . $coverage[$i] . "__<br/>");
			//this->debug("__HalfPoint : " . $halfPoint . "__<br/>");
			//this->debug("__AddOn : " . $addOn[$i] . "__<br/>");
			propMore += addOn[i] * (proportionTouchedOnceOrMore * (1 - proportionNotTouchedMore) * (newCoverage - covered)) / prop;
			covered = newCoverage;
		}


		//this->debug("__PropOnce : " . $propOnce . "__<br/>");
		//this->debug("__PropMore : " . $propMore . "__<br/>");
		var balance = (1 - propOnce) / propMore;
		//this->debug("__Balance : " . $balance . "__<br/>");
		for(var i = 0; i < div; i++)
		{
			addOn[i] *= balance;
		}
		//$subtBalance = (1 - $propOnce) - $propMore;
		//$coverageFirstAddOn = 1.0 - 1.0 / $coverageFractal;
		//$addOn[0] += $subtBalance / (($proportionTouchedOnceOrMore * (1 - $proportionNotTouchedMore) * $coverageFirstAddOn) / $prop);

		total = 0;
		for(i = 0; i < div; i++)
		{
			//this->debug("__" . floor(((1+2*$i)*$halfPartCoverage + $proportionNotTouchedMore)*$g_countInvDistribution) . "__<br/>");

			total += addOn[i] * (amountUnitHit * (1.0 - proportionNotTouchedMore) * coverage[i]);
			this.ackImpact(
				amountUnitHit * (1.0 - proportionNotTouchedMore) * coverage[i],
				power * addOn[i],
				addOn[i]);
		}
		console.log("__TOTAL : " + total);
	}
	else
	{
		//Remember the power of a soloing unit
		var uniquePower = power;
		var combinedPower = power;
		//Because we will combine powers
		var combined = 1;

		//Combination of power loop
		//At the beginning we have a certain amount of different units hit. However, there is
		//	more single shots than units hit. Thus, some units receive more than one hit. This
		//	loop combine the shots one by one until no more shot/unit is left unmanaged
		while(amountUnitHit > 1)
		{
			//If the combined power outcast the shield + hull of the unit
			//	just send the shots and stop the loop
			if(combinedPower >= (this.unitHull + this.unitShield))
			{
				this.ackImpact(amountUnitHit, combinedPower * amountHit / amountUnitHit, combined * amountHit / amountUnitHit);
				amountUnitHit = 0;
				amountHit = 0;
				break;
			}

			//Same fantastic equation to know how many units have been hit more than once
			amountUnitHitOnce = amountUnitHit * Math.pow((amountUnitHit-1) / amountUnitHit, amountHit - amountUnitHit);
			amountUnitHitMoreThanOnce = amountUnitHit - amountUnitHitOnce;

			//We treat the units hit once
			if(amountUnitHitOnce > 1.0)
			{
				this.ackImpact(amountUnitHitOnce, combinedPower, combined);
				amountHit -= amountUnitHitOnce;
			}
			else if(amountUnitHitMoreThanOnce <= 1)
				break;
			else
				amountUnitHitMoreThanOnce += amountUnitHitOnce;

			//And increase the power by combining the shots for the next loop iteration
			amountHit /= 2;
			amountUnitHit = amountUnitHitMoreThanOnce;
			combinedPower *= 2;
			combined *= 2;
		}

		//Process residuals
		//Being here means there is less than one unit left unmanaged but some
		//	fire power left. We manage it.
		if(amountHit > 0 && amountUnitHit > 0)
		{
			combinedPower *= amountHit / amountUnitHit;
			combined *= amountHit / amountUnitHit;
			this.ackImpact(amountUnitHit, combinedPower, combined);
		}
	}

	console.log("Receive wave end with " + this->amountUnitTemp().toFixed(1) + " units left" );
	for(var i = 0; i < this.integArrTemp.length; i++)
	{
		var div = this.integArrTemp[i];
		if(div.amount > 0)
			console.log("_Division " + i + " " + this->getTopIntegrityForDiv(i).toFixed(2) + " : " + div.amount.toFixed(3) + " (Integrity of " + div.getIntegrityNorm().toFixed(2) + ")" );
	}
	console.log("");
}

/**
* Acknowledge an damage froma set of hits.
* @param {Number} amount amount of units hit
* @param {Number} power Power of each hit
* @param {Number} combined Power on each unit
 */
ogl.util.Group.prototype.ackImpact = function(amount, power, combined) {
	//No use continuing with a group wiped out
	if(this.getAmountUnitTemp() <= 0)
		return;

	console.log("Unit hit " + combined.toFixed(1) + " times (" + amount.toFixed(2) + " units with " + (amount*combined).toFixed(2) + " shots)");
	console.log("_Power received : " + (amount * power).toFixed(2) + " (" + power.toFixed(2)." each)");

	var amountInWave = this.getAmountUnitInWave();
	if(amountInWave == 0)
		return;

	for(var i = 0; i < this.shieldArrTemp.length; i++)
	{
		if(this.shieldArrInWave[i].amount / amountInWave > 0)
		{
			var affected = amount * this.shieldArrInWave[i].amount / amountInWave;
			var shieldNorm = this.shieldArrInWave[i].getIntegrityNorm();
			//this->debug( "_Shield norm : ".number_format($shieldNorm, 2)." <br/>" );

			//Shield effect
			var consumedShield = Math.min(shieldNorm, power/this.unitShield);
			var power = power - consumedShield * this.unitShield;
			console.log("_Power left : " + power.toFixed(2));

			//Displace data structure
			var shieldLeft = Math.max(0.0, shieldNorm - consumedShield);
			var destDivId = Math.round((this.shieldArrTemp.length - 1) * (1.0 - shieldLeft));
			//this->debug( "_Shield div destination : ".$destDivId."<br/>" );

			//Direct hit after absorbtion (must trigger even with null power for explosion)
			unitLost = this.affectIntegrity(affected, power, combined);

			this.shieldArrTemp[i].amount -= affected;
			this.shieldArrTemp[i].integrity -= affected * shieldNorm;

			this.shieldArrTemp[destDivId].amount += (affected - unitLost);
			this.shieldArrTemp[destDivId].integrity += (affected - unitLost) * (shieldLeft);
		}
	}
}

/**
 * Acknoledge a number of distinct units hit with a certain combined power.
 * Will now affect the integrity and amount of unit.
 * @param {Number} amount amount of units hit
 * @param {Number} power Power of each hit
 * @param {Number} combined Power on each unit
 */
ogl.util.Group.prototype.affectIntegrity = function(amount, power, combined) {
	unitDestroyed = 0;
	//Integrity consumed by the hit
	consumedIntegrity = power/this.unitHull;

	amountInWave = this.getAmountUnitInWave();
	if(amountInWave == 0)
		return unitDestroyed;

	//Manage stables
	for(var i = 0; i < this.integArr.length; i++)
	{
		if(this.integArrInWave[i].amount / amountInWave > 0)
		{
			var affected = amount * this.integArrInWave[i].amount / amountInWave;

			var integrityNorm = this.integArrInWave[i].getIntegrityNorm();

			var offExplosion = Math.max(0, integrityNorm - 0.7);
			var combinedLocal = combined;
			if($consumedIntegrity > 0)
				combinedLocal = combined*(consumedIntegrity - offExplosion)/consumedIntegrity;
			first = combined - Math.floor(combined);
			combinedLocal = Math.floor(combinedLocal);

			var nonExplodingRatio = 1.0;
			if(integrityNorm-consumedIntegrity <= 0)
				nonExplodingRatio = 0.0;
			else if(integrityNorm-consumedIntegrity <= 0.7)
			{
				if(offExplosion > 0)
					nonExplodingRatio *= Math.max(0, 0.7 - first*power/combined/this.unitHull);
				if(combined > 0)
					forvar j = 1; j <= combined; j++) //Find a way to get rid of this for loop, time consuming
						nonExplodingRatio *= integrityNorm-(consumedIntegrity*j)/combined;
				if((first % 1.0) != 0.0)
					nonExplodingRatio *= integrityNorm-consumedIntegrity/combined; //TODO remove p_combined from equation I think
			}
			nonExplodingRatio = Math.max(0.0, nonExplodingRatio);

			var explodingRatio = 1 - nonExplodingRatio;

			var nonExploding = affected * nonExplodingRatio;

			integrityLeft = Math.max(0.0, integrityNorm - consumedIntegrity);
			destDivId = this.getDivIDFromIntegrityNorm(integrityLeft);

			this.integArrTemp[i].amount -= Math.max(0.0, affected);
			this.integArrTemp[i].integrity -= Math.max(0.0, affected * $integrityNorm);
			if(this.integArrTemp[i].amount < 0.001)
			{
				this.integArrTemp[i].amount = 0.0;
				this.integArrTemp[i].integrity = 0.0;
			}

			this.integArrTemp[destDivId].amount += Math.max(0.0, nonExploding);
			this.integArrTemp[destDivId].integrity += Math.max(0.0, nonExploding * (integrityLeft));

			if(affected * explodingRatio > 0)
			{
				unitDestroyed += affected * explodingRatio;
				//this->debug( "_Explosions : ".number_format($affected * $explodingRatio, 2)." : ".number_format($explodingRatio, 2)."<br/>" );
			}
		}
	}

	return unitDestroyed;
}

/**
 * Modify the group according to the temporary changes made
 */
ogl.util.Group.prototype.applyRound = function(amount, power, combined) {
	for (var i in this.integArrTemp)
		this.integArr[i] = this.integArrTemp[i].clone();
}
