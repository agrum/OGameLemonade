var Risk = {};
Risk.Model = {};
Risk.View = {};

var pendingLink = null;
var attacker = null;
var potentialDefenders = [];
var defender = null;

Risk.Model.Territory = Backbone.Model.extend({
    idAttribute: '_id',
    defaults: {
      name: '',
      color: [0,0,0],
      shade: 1,
      path: '',
      mode: '',
      hovering: false,
      trigger: '',
      animationToken: 0,
      animationDirection: false
    },
    url: function() {
      return '/territory/'+ (this.isNew() ? '' : this.id +'/');
    }
  });

Risk.Model.Link = Backbone.Model.extend({
    idAttribute: '_id',
    defaults: {
      name: '',
      map: '',
      territories: []
    },
    url: function() {
      return '/link/' + (this.isNew() ? '' : this.id +'/');
    }
  });

Risk.Model.Map = Backbone.Model.extend({
    idAttribute: '_id',
    defaults: {
      name: '',
      territories: []
    },
    url: function() {
      return '/map/'+ (this.isNew() ? '' : this.id +'/');
    }
  });

Risk.View.Territory = Backbone.SnapSvgView.extend({
      initialize: function(){
          var model = this.model;
          this.listenTo(model, "change", this.changeRender);

          // Set the element of the view
          this.setElement(map.canvas.path(model.get("path")));
          map.group.add(this.el);

          this.render();
      },

      events: {
          // Any raphael event
          "click": "selectMode",
          "mouseover": "inColor",
          "mouseout": "outColor"
      },

      selectMode: function(evt){
        var attackerAnimationToken;
        var defenderAnimationToken;
        var cancelClick = false;
        //Activate model as defender if potential clicked
        if(this.model.get('mode') == 'potentialDefender')
        {
          for(var potentialDefendersIndex1 in potentialDefenders)
          {
            defenderAnimationToken = potentialDefenders[potentialDefendersIndex1].get('animationToken');
            if(potentialDefenders[potentialDefendersIndex1] == this.model){
              defender = potentialDefenders[potentialDefendersIndex1];
              defender.set({
                'mode': 'defender',
                'animationToken': defenderAnimationToken+1});
              attackerAnimationToken = this.model.get('animationToken');
              attacker.set({
                'animationToken': attackerAnimationToken+1});
            }
            else {
              potentialDefenders[potentialDefendersIndex1].set({
                'mode': '',
                'animationToken': defenderAnimationToken+1,
                'animationDirection': false});
            }
          }
          potentialDefenders = [];
        }
        //Otehrwise, if there was an attacker, deactivate all
        else if(attacker)
        {
          if(attacker == this.model)
            cancelClick = true;
          attackerAnimationToken = this.model.get('animationToken');
          attacker.set({
            'mode': '',
            'animationToken': attackerAnimationToken+1,
            'animationDirection': false});
          attacker = null;
          for(var potentialDefendersIndex in potentialDefenders)
          {
            defenderAnimationToken = potentialDefenders[potentialDefendersIndex].get('animationToken');
            potentialDefenders[potentialDefendersIndex].set({
              'mode': '',
              'animationToken': defenderAnimationToken+1,
              'animationDirection': false});
          }
          potentialDefenders = [];
          if(defender)
          {
            defender.set({
              'mode': '',
              'animationToken': attackerAnimationToken+1,
              'animationDirection': false});
            defender = null;
          }
        }
        //If the attacker has been clicked, do not select again
        if(cancelClick)
          return;
        //If there is no attacker, define this model as the one
        if(attacker === null)
        {
          attacker = this.model;
          attackerAnimationToken = this.model.get('animationToken');
          this.model.set({
            'mode': 'attacker',
            'animationToken': attackerAnimationToken+1,
            'animationDirection': false});
          var links = this.model.get('links');
          for(var i in links)
          {
            var linkedTerritories = map.links[links[i]].get('territories');
            var oppositeTerritoryId = (linkedTerritories[0] == this.model.get('_id') ? linkedTerritories[1] : linkedTerritories[0]);
            potentialDefenders.push(map.territories[oppositeTerritoryId]);
            defenderAnimationToken = map.territories[oppositeTerritoryId].get('animationToken');
            map.territories[oppositeTerritoryId].set({
              'mode': 'potentialDefender',
              'animationToken': defenderAnimationToken+1,
              'animationDirection': false});
          }
        }
      },
      createLink: function(evt){
        if(pendingLink === null)
        {
          pendingLink = new Risk.Model.Link({
            'name': this.model.get('name'),
            'map': map.model.get('_id'),
            'territories': [this.model.get('_id')]
          });
          console.log("linkA");
        }
        else {
          var name = pendingLink.get('name');
          var territories = pendingLink.get('territories');
          territories.push(this.model.get('_id'));
          pendingLink.set('territories', territories);
          pendingLink.set('name', name + '-' + this.model.get('name'));
          pendingLink.save();
          pendingLink = null;
          console.log("linkB");
        }
      },

      inColor: function(evt){
        if(!this.model.get("mode"))
          this.model.set('hovering', true);
      },

      outColor: function(evt){
        if(!this.model.get("mode"))
          this.model.set('hovering', false);
      },

      changeRender: function() {
        this.render();
      },

      animationRender: function(token) {
        if(this.model.get("animationToken") == token)
          this.model.set("animationDirection", !this.model.get('animationDirection'));
      },

      render: function(){
        var self = this;
        var model = this.model;
        var colorModified = [];
        var color = model.get("color");
        var shade = model.get("shade");
        var hovering = model.get("hovering");
        var mode = model.get("mode");
        var animationToken = model.get("animationToken");
        var animationDirection = model.get("animationDirection");
        for(var i = 0; i < 3; i++)
        {
          colorModified[i] = Math.min(255, Math.floor(color[i] * shade + (hovering ? 10 : 0)));
        }
        var colorString = 'rgb('+colorModified[0]+','+colorModified[1]+','+colorModified[2]+')';
        var patternPath = map.canvas.path("M10-5-10,15M15,0,0,15M0-5-20,15").attr({fill: "none", strokeWidth: 5});
        if(mode)
        {
          var blinkColor = 'white';
          if(mode == 'attacker')
            blinkColor = 'rgb(255, 50, 50)';
          else if(mode == 'potentialDefender')
            blinkColor = 'rgb(50, 255, 50)';
          else if(mode == 'defender')
            blinkColor = 'rgb(50, 50, 250)';
          var styleB = {fill: "none", strokeWidth: 8, stroke: colorString};
          var styleA = {fill: "none", strokeWidth: 4, stroke: blinkColor};
          if(animationDirection)
          {
            patternPath.attr(styleA);
            patternPath.animate(styleB, 1000, null, function() {self.animationRender(animationToken);});
          }
          else {
            patternPath.attr(styleB);
            patternPath.animate(styleA, 1000, null, function() {self.animationRender(animationToken);});
          }
        }
        patternPath = patternPath.pattern(0, 0, 10, 10);

        if(mode)
          this.el.attr({fill: patternPath});
        else
          this.el.attr({fill: colorString});

        this.el.attr({"stroke-alignment": "inner"});
        this.el.attr({stroke: "rgba(0,0,0,0.25)"});
      }

  });

Risk.View.Map = Backbone.View.extend({
    el: '#map',
    initialize: function() {// create a wrapper around native canvas element (with id="c")
      this.canvas = Snap(1024, 792);
      var rect = this.canvas.rect(0, 0, 1024, 660);
      this.group = this.canvas.group();
      this.group.attr({'transform': 's1,0.86'});
      this.group.add(rect);
      rect.attr("fill", "#444");
      rect.attr("stroke", "#444");
      this.territoryViews = [];

      this.model = new Risk.Model.Map({_id: "6e379d3c-3f57-4a92-ac7c-ffc0f6803b21"});
      this.listenTo(this.model, 'change', this.acquireChildren);
      this.model.fetch();
    },
    acquireChildren: function(event) {
      var mapTerritories = this.model.get("territories");
      this.territories = [];
      for(var i in mapTerritories)
      {
        var territory = new Risk.Model.Territory(mapTerritories[i]);
        this.territories[mapTerritories[i]._id] = territory;
        this.territoryViews[mapTerritories[i]._id] = new Risk.View.Territory({model: territory});
      }
      var mapLinks = this.model.get("links");
      this.links = [];
      for(var iteLink in mapLinks)
      {
        var link = new Risk.Model.Link(mapLinks[iteLink]);
        this.links[mapLinks[iteLink]._id] = link;
      }
    }
  });

var map = new Risk.View.Map();
