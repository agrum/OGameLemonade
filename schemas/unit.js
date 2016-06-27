'use strict';

var RapidFire = {
  unit: String,
  fire: Number
};

exports = module.exports = function(app, mongoose) {
  var schema = new mongoose.Schema({
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
		rapidFireArray:[ RapidFire ]
  });
  app.db.model('Unit', schema);
};
