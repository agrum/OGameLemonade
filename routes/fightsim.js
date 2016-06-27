var express = require('express');
var router = express.Router();

router.get('/', function(req, res, next) {
  var getUnitCollection = function(callback) {
      req.app.db.models.Unit.find({}, function(err, units){
        if (err) {
          return callback(err, null);
        }

        return callback(null, units);
      });
    };

  var getPresetCollection = function(callback) {
      req.app.db.models.Preset.find({}, function(err, preset){
        if (err) {
          return callback(err, null);
        }

        return callback(null, preset);
      });
    };

  var finish = function(err, results) {
    if (err) {
      return workflow.emit('exception', err);
    }

    res.render('fightsim',
    {
      title: 'OGameLemonade',
      currentYear: new Date().getFullYear(),
      units: results[0],
      presets: results[1]
    });
  };

  require('async').parallel([getUnitCollection, getPresetCollection], finish);
});

module.exports = router;
