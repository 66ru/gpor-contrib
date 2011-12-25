	(function($){
		$.fn.news_stat = function(opts){
		opts = $.extend({}, $.fn.news_stat.defaults, opts);
			return this.each(function(){
				$.fn.news_stat.instances[$(this).attr('id')] = new NewsStat(this, opts, $(this).attr('id') );
				return $.fn.news_stat.instances[$(this).attr('id')];
			});
		};

		$.fn.news_stat.instances = new Object();
		$.fn.news_stat_refresh = function(){
			
		};

		// default options
		$.fn.news_stat.defaults = {
			'viewsStatContainer' : 'viewsStat',
			'commentsStatContainer' : 'commentsStat',
			'headerContainer' : 'header',
			'viewsGraphContainer' : 'viewsStatGraph',
			'commentsGraphContainer' : 'commentsStatGraph',
			'feedContainer' : 'feed',
			'statData' : [],
			'feedData' : [],
			'graphOptions' : {
				width: 640,
				height: 430,
				vAxis: {title: ""},
				hAxis: {title: "days"},
				seriesType: "bars",
				//series: {5: {type: "line"}},
				animation:{
					duration: 1000,
					easing: 'out'
				},
			},
			'currentSectionId' : '',
			'currentSectionName' : ''
		};

		var NewsStat = function(obj, o, instance_id){
		    var header = $("#"+o.headerContainer).find("h1");
		    var viewsStatContainer = $("#"+o.viewsStatContainer);
		    var viewsGraph = false;
		    var viewsCurrent = $(viewsStatContainer).find(".statConainer-current");
		    var viewsTop = $(viewsStatContainer).find(".statConainer-top-list");

		    var commentsStatContainer = $("#"+o.commentsStatContainer);
		    var commentsGraph = false;
		    var commentsCurrent = $(commentsStatContainer).find(".statConainer-current");
		    var commentsTop = $(commentsStatContainer).find(".statConainer-top-list");
		    
		    var feedContainer = $("#"+o.feed).find(".feed-list");
		    
		    var statData = o.statData;
		    var feedData = o.feedData;
		    
		    var currentSectionId = o.currentSectionId;
		    var currentSectionName = o.currentSectionName;

		    var refreshFeed = function () {
		    }
		    
		    var gotoNextSection = function () {
                $.ajax({
                    url: '?currentSectionId='+currentSectionId+'&action=next',
                    type: 'post',
                    data: {},
                    dataType: 'json',
                    success: function(data) {
                    	refreshStat(data);
                    }
                });

		    }
		    
		    var refreshStat = function (data) {
			    currentSectionId = data.currentSectionId;
			    currentSectionName = data.currentSectionName;

			    refreshHeader();
		    	refreshViewsCurrent();
		    	refreshCommentsCurrent();
		    	refreshViewsGraph();
		    	refreshCommentsGraph();
		    	refreshViewsTop();
		    	refreshCommentsTop();
		    }
		    
		    var refreshHeader = function () {
		    }
		    
		    var refreshViewsCurrent = function () {
		    }
		    
		    var refreshCommentsCurrent = function () {
		    }
		    
		    var refreshViewsGraph = function () {
		    }

		    var refreshCommentsGraph = function () {
		    }

		    var refreshViewsTop = function () {
		    }

		    var refreshCommentsTop = function () {
		    }
		    
		    var initViewsGraph = function (statData) {
		    	var data = [];
		    	var rowData1 = [['day', 'views']];
		    	if (statData)
		    	{
		    		for (i in statData)
		    		{
		    			rowData1.push([i, statData[i]]); 
		    		}
		    	}
		    	
		    	data[0] = google.visualization.arrayToDataTable(rowData1);

		    	var current = 0;
		    	viewsGraph = new google.visualization.ComboChart(document.getElementById(o.viewsGraphContainer));
		    	
		    	viewsGraph.draw(data[current], o.graphOptions);
		    }

		    var initCommentsGraph = function (statData) {
		    	var data = [];
		    	var rowData1 = [['day', 'comments']];
		    	if (statData)
		    	{
		    		for (i in statData)
		    		{
		    			rowData1.push([i, statData[i]]); 
		    		}
		    	}
		    	
		    	data[0] = google.visualization.arrayToDataTable(rowData1);

		    	var current = 0;
		    	commentsGraph = new google.visualization.ComboChart(document.getElementById(o.commentsGraphContainer));
		    	commentsGraph.draw(data[current], o.graphOptions);
		    }


		    // init
		    initViewsGraph (statData['viewsStat']);
		    initCommentsGraph (statData['commentsStat']);
		    setInterval(function(){gotoNextSection();}, 1000*10);

		};
	})(jQuery);
