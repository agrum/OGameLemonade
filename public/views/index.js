var Risk = {};
Risk.Model = {};
Risk.View = {};

Risk.View.MapArray = Backbone.View.extend({
  el: '#map-select',
  initialize: function() {
    this.mapArray = mapArray;
    for(var i in mapArray)
    {
      var option = document.createElement("option");
      option.text = mapArray[i].name;
      option.value = mapArray[i]._id;
      this.el.add(option);
    }
  }
});

var mapArray = new Risk.View.MapArray();
