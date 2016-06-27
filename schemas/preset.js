'use strict';

exports = module.exports = function(app, mongoose) {
  var schema = new mongoose.Schema({
    _id: String,
    data: [ Number ]
  });
  app.db.model('Preset', schema);
};
