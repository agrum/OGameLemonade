'use strict';

exports = module.exports = function(app) {
  app.use('/', require('./routes/index'));
  app.use('/fightsim', require('./routes/fightsim'));
  app.use('/benchmark', require('./routes/benchmark'));

  app.use(function(req, res, next) {
    var err = new Error('Not Found');
    err.status = 404;
    next(err);
  });
};
