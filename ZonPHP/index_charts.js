var grid = [];
document.addEventListener('DOMContentLoaded', function () {
    grid = new Muuri('.grid', {
        dragEnabled: true,
        layout: {
            fillGaps: true
        }
    }).on('move', function () {
        saveLayout(grid);
    });
    var layout = window.localStorage.getItem('layout');
    if (layout) {
        console.log("stored Layout " + layout);
        loadLayout(grid, layout);
    } else {
        grid.layout(true);
        console.log("Layout default");
    }

    function serializeLayout(grid) {
        var itemIds = grid.getItems().map(function (item) {
            return item.getElement().getAttribute('data-id');
        });
        console.log(JSON.stringify(itemIds));
        return JSON.stringify(itemIds);
    }

    function saveLayout(grid) {
        var layout = serializeLayout(grid);
        window.localStorage.setItem('layout', layout);
        console.log("Layout saved");
    }

    function loadLayout(grid, serializedLayout) {
        var layout = JSON.parse(serializedLayout);
        var currentItems = grid.getItems();
        var currentItemIds = currentItems.map(function (item) {
            return item.getElement().getAttribute('data-id')
        });
        var newItems = [];
        var itemId;
        var itemIndex;

        for (var i = 0; i < layout.length; i++) {
            itemId = layout[i];
            itemIndex = currentItemIds.indexOf(itemId);
            if (itemIndex > -1) {
                newItems.push(currentItems[itemIndex])
            }
        }

        grid.sort(newItems, {layout: 'instant'});
        console.log("Layout loaded");
    }

    var headerclass = "jqx-window-header jqx-window-header-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp"

    /* load all charts according order */
    function loadCharts() {
        // todo: only load activted charts
        // todo: list day/month for all inverters and acrivate/deactivate
        addTemerature();
        addWeewx();
        addDay();
        addMonth();
        addYear();
        addAllYears();
        addCumulative();
        addLastYears();
        addBest();
        addWorst();
        addAllTemp();
        addAllHumidty();
        addIndoorSensors();
        loadLayout(grid, layout);
    }

    function addDay() {
        var id = "id_Day";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div  class="item-content card"> ' +
            '<a href="day_overview.php"><div class="' + headerclass + '">' + daytext + '</div> </a> ' +
            '<div id="' + id + '">' +

            '</div>' +
            '</div>' +
            '</div>';

        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/day_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addMonth() {
        var id = "id_Month";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div  class="item-content card"> ' +
            '<a href="month_overview.php"><div class="' + headerclass + '">' + txt["chart_monthoverview"] + '</div> </a> ' +
            '<div id="' + id + '">' +

            '</div>' +
            '</div>' +
            '</div>';

        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/month_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addAllYears() {
        var id = "id_AllYears";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div  class="item-content card"> ' +
            '<a href="all_years_overview.php"><div class="' + headerclass + '">' + txt["chart_allyearoverview"] + '</div></a>' +
            '<div id="' + id + '">' +

            '</div>' +
            '</div>' +
            '</div>';

        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/all_years_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addWorst() {
        var id = "id_Worst";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<a href="top31_overview.php?Max_Min=top"><div class="' + headerclass + '">' + txt["slechtste"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/top31_chart.php',
            type: 'post',
            data: {'action': 'indexpage', 'topflop': 'flop'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addBest() {
        var id = "id_Best";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<a href="top31_overview.php?Max_Min=top"><div class="' + headerclass + '">' + txt["beste"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/top31_chart.php',
            type: 'post',
            data: {'action': 'indexpage', 'topflop': 'top'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addYear() {
        var id = "id_Year";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<a href="year_overview.php"><div class="' + headerclass + '">' + txt["chart_yearoverview"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/year_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            },
        });
    }

    function addCumulative() {
        var id = "id_Cumulative";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<a href="cumulative_overview.php"><div class="' + headerclass + '">' + txt["chart_cumulativeoverview"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/cumulative_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            },
        });
    }

    function addTemerature() {
        var id = "id_Temperature";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card" style="background-color: #'+ colors['color_chartbackground'] + '"> ' +
            '<a href="sensor_gauge_overview.php"><div class="' + headerclass + '">' + txt["chart_gauge"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/sensor_gauge.php',
            type: 'post',
            data: {
                'action': 'indexpage', 'sensors': '197086:1:Sleep:FFF, ' +
                    '196692:1:Cellar:FFF, ' +
                    '18974:1:Loft:FFF, ' +
                    '197086:3:Sleep:3B77DB, ' +
                    '196692:3:Cellar:3B77DB, ' +
                    '18974:3:Loft:3B77DB', 'id': 'Current'
            },
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }


    function addLastYears() {
        var id = "id_LastYears";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<a href="last_years_chart.php"><div class="' + headerclass + '">' + txt["chart_lastyearoverview"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/last_years_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            },
        });
    }

    function addWeewx() {
        var id = "id_Weewx";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card" style="background-color: #'+ colors['color_chartbackground'] + '"> ' +
            '<a href="/weewx/index.html"><div class="' + headerclass + '">' + txt["chart_weewx"] + '</div></a>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/weewx_all_values.php',
            type: 'post',
            data: { 'id': 'Current'
            },
            cache: false,
            success: function (chart) {
                $(container).append(chart);

            }
        });
    }

    function addAllTemp() {
        var id = "id_AllTemp";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<div class="' + headerclass + '">' + txt["chart_all_temp"] + '</div>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/sensor_chart.php',
            type: 'post',
            data: {
                'action': 'indexpage',
                'sensors': '197190:1:Outdoor °C:ffa200,196692:1:Cellar °C:1919B7,197086:1:Sleep °C:33cc33, 18974:1:Loft °C:FA58F4',
                'id': 'all_temp'
            },
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addIndoorSensors() {
        var id = "id_IndoorSensors";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<div class="' + headerclass + '">' + txt["chart_indoor"] + '</div>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/sensor_chart.php',
            type: 'post',
            data: {
                'action': 'indexpage',
                'sensors': '197086:1:Sleep °C:ffa200,197086:3:Sleep %RH:001570',
                'id': 'indoor',
                'title': 'title'
            },
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addAllHumidty() {
        var id = "id_AllHumudity";
        var itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '<div class="item-content card"> ' +
            '<div class="' + headerclass + '">' + txt["chart_all_humidity"] + '</div>' +
            '<div id="' + id + '">' +
            '</div>' +
            '</div>';
        var itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        var container = $('#' + id);
        $.ajax({
            url: 'charts/sensor_chart.php',
            type: 'post',
            data: {
                'action': 'indexpage',
                'sensors': '197190:3:Outdoor %RH:ffa200,196692:3:Cellar %RH:1919B7,197086:3:Sleep %RH:33cc33, 18974:3:Loft %RH:FA58F4',
                'id': 'all_humidity'
            },
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    loadCharts();
});

function myTest(){
    var layout = window.localStorage.getItem('layout');
    console.log("xxx grid " + grid);
    console.log("The TEST2");
}