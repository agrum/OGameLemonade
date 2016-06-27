'use strict';

exports = module.exports = function(app, mongoose) {
  var schema = new mongoose.Schema({
    _id: {
      type: String,
      default: require('../utils/helpers').generateGUID
    }
  });
  app.db.model('Composition', schema);
};
