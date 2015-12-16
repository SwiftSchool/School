/*
     * Sparkline in Card panel
     */
    (function() {
      $("#sparkcard1").conSparkline([76,78,87,65,43,35,23,25,12,14,27,35,32,37,31,46,43,32,36,57,78,87,82,75,58,54,70,23,54,67,34,23,87,12,43,65,23,76,32,55], {
        type: 'bar',
        width: '100%',
        height: 20,
        barColor: '#2196f3'
      });
    }());
    
    /*
     * Flot Line Chart
     */
    (function() {
      var chart = $("#flotLineChart");
      var data1 = {
        data: [[1, 50], [2, 58], [3, 45], [4, 62],[5, 55],[6, 65],[7, 61],[8, 70],[9, 65],[10, 70],[11, 53],[12, 49]],
        label: "Mails"
      };
      var data2 = {
        data: [[1, 25], [2, 31], [3, 23], [4, 48],[5, 38],[6, 40],[7, 47],[8, 55],[9, 43],[10,50],[11,37],[12, 29]],
        label: "SMS"
      };
      var data3 = {
        data: [[1, 4], [2, 13], [3, 7], [4, 17],[5, 20],[6, 24],[7, 13],[8, 17],[9, 10],[10,17],[11,6],[12, 3]],
        label: "Invoices"
      };
      var options = {
        series: {
          lines: {
            show: true,
            lineWidth: 1,
            fill: true, 
            fillColor: { colors: [ { opacity: 0.1 }, { opacity: 0.13 } ] }
          },
          points: {
            show: true, 
            lineWidth: 2,
            radius: 3
          },
          shadowSize: 0,
          stack: true
        },
        grid: {
          hoverable: true, 
          clickable: true, 
          tickColor: "#f9f9f9",
          borderWidth: 0
        },
        legend: {
          // show: false
          backgroundOpacity: 0,
          labelBoxBorderColor: "#fff"
        },  
        colors: ["#3f51b5", "#009688", "#2196f3"],
        xaxis: {
          ticks: [[1, "Jan"], [2, "Feb"], [3, "Mar"], [4,"Apr"], [5,"May"], [6,"Jun"], 
                     [7,"Jul"], [8,"Aug"], [9,"Sep"], [10,"Oct"], [11,"Nov"], [12,"Dec"]],
          font: {
            family: "Roboto,sans-serif",
            color: "#ccc"
          }
        },
        yaxis: {
          ticks:7, 
          tickDecimals: 0,
          font: {color: "#ccc"}
        }
      };
      
      function initFlot() {
        $.plot(chart, [data1, data2, data3], options);
        chart.find('.legend table').css('width', 'auto')
             .find('td').css('padding', 5);
      }
      initFlot();
      $(window).on('resize', initFlot);
    
      function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
          position: 'absolute',
          display: 'none',
          top: y - 40,
          left: x - 55,
          color: "#fff",
          padding: '5px 10px',
          'border-radius': '3px',
          'background-color': 'rgba(0,0,0,0.6)'
        }).appendTo("body").fadeIn(200);
      }
    
      var previousPoint = null;
      chart.bind("plothover", function (event, pos, item) {
        if (item) {
          if (previousPoint != item.dataIndex) {
            previousPoint = item.dataIndex;
    
            $("#tooltip").remove();
            var x = item.datapoint[0].toFixed(0),
                y = item.datapoint[1].toFixed(0);
    
            var month = item.series.xaxis.ticks[item.dataIndex].label;
    
            showTooltip(item.pageX, item.pageY,
                        item.series.label + " of " + month + ": " + y);
          }
        }
        else {
          $("#tooltip").remove();
          previousPoint = null;
        }
      });
    }());
    
    
    
    /*
     * Flot Pie Chart
     */
    (function() {
      var chart = $("#flotPieChart");
      var data = [
          { label: "IE",  data: 19.5, color: "#90a4ae"},
          { label: "Safari",  data: 4.5, color: "#7986cb"},
          { label: "Opera",  data: 2.3, color: "#9575cd"},
          { label: "Firefox",  data: 36.6, color: "#4db6ac"},
          { label: "Chrome",  data: 36.3, color: "#64b5f6"}
      ];
      var options = {
        series: {
          pie: {
            innerRadius: 0.5,
            show: true
          }
        },
        grid: {
          hoverable: true
        },
        legend: {
          backgroundOpacity: 0,
          labelBoxBorderColor: "#fff"
        },
        tooltip: true,
        tooltipOpts: {
          content: "%p.0%, %s", // show percentages, rounding to 2 decimal places
          shifts: {
            x: 20,
            y: 0
          },
          defaultTheme: false
        }
      };
    
      function initFlot() {
        $.plot(chart, data, options);
        chart.find('.legend table').css('width', 'auto')
             .find('td').css('padding', 5);
      }
      initFlot();
      $(window).on('resize', initFlot);
    
    }());
    
    
    /*
     * MAP 1
     */
    (function() {
      $('#map1').vectorMap({
        map: 'world_mill_en',
        zoom: 2,
        series: {
          regions: [{
            values: gdpData,
            scale: ['#e3f2fd', '#2196f3'],
            normalizeFunction: 'polynomial'
          }]
        },
        backgroundColor: '#fff',
        onRegionTipShow: function(e, el, code){
          el.html(el.html()+' (GDP - '+gdpData[code]+')');
        }
      });
    }());
    
    setTimeout(function() {
      Materialize.toast('Welcome to Con!', 1000);
    }, 1000);