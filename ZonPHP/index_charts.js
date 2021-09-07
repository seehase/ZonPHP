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
                'action': 'indexpage', 'sensors': '197086:1:Wintergarden:FFF, ' +
                    '196692:1:Cellar:FFF, ' +
                    '18974:1:Loft:FFF, ' +
                    '197086:3:Wintergarden:3B77DB, ' +
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
            '<a href="last_years_overview.php"><div class="' + headerclass + '">' + txt["chart_lastyearoverview"] + '</div></a>' +
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
                'sensors': '197190:1:Outdoor °C:ffa200,196692:1:Cellar °C:1919B7,197086:1:Wintergarden °C:33cc33, 18974:1:Loft °C:FA58F4',
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
                'sensors': '197086:1:Wintergarden °C:ffa200,197086:3:Wintergarden %RH:001570',
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
                'sensors': '197190:3:Outdoor %RH:ffa200,196692:3:Cellar %RH:1919B7,197086:3:Wintergarden %RH:33cc33, 18974:3:Loft %RH:FA58F4',
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
    // Get the modal



    var modal = document.getElementById("myModal");
    var btn = document.getElementById("myBtn");
    var span = document.getElementsByClassName("close")[0];
    var cnt = document.getElementsByClassName("board")[0];

    var itemElem = document.createElement('div');
    itemElem.innerHTML =
        '        <div class="board-column todo">' +
        '            <div class="board-column-header">Available Charts</div>' +
        '            <div class="board-column-content-wrapper">' +
        '                <div class="board-column-content">' +
        '                    <div class="board-item"><div class="board-item-content">aaa</div></div>' +
        '                    <div class="board-item"><div class="board-item-content">bbb</div></div>' +
        '                    <div class="board-item"><div class="board-item-content">ccc</div></div>' +
        '                    <div class="board-item"><div class="board-item-content">dddd</div></div>' +
       // '                </div>' +
        '            </div>';
        cnt.appendChild(itemElem);
        itemElem = document.createElement('div');
    itemElem.innerHTML =
        '        <div class="board-column working">' +
        '            <div class="board-column-header">Active Charts</div>' +
        '            <div class="board-column-content-wrapper">' +
        '                <div class="board-column-content">' +
        '                    <div class="board-item"><div class="board-item-content"><span>Item #</span>8</div></div>' +
        '                    <div class="board-item"><div class="board-item-content"><span>Item #</span>9</div></div>' +
        '                    <div class="board-item"><div class="board-item-content"><span>Item #</span>10</div></div>' +
        '            </div>\n';
     cnt.appendChild(itemElem);

    var boardGrid = new Muuri('.board', {
        layoutDuration: 400,
        layoutEasing: 'ease',
        dragEnabled: false,
        dragSortInterval: 0,
        dragStartPredicate: {
            handle: '.board-column-header'
        },
        dragReleaseDuration: 400,
        dragReleaseEasing: 'ease'
    });

    modal.style.display = "block";
    var itemContainers = [].slice.call(document.querySelectorAll('.board-column-content'));
    var columnGrids = [];

    // Define the column grids so we can drag those
    // items around.
    itemContainers.forEach(function (container) {
        // Instantiate column grid.
        var gridLayout = new Muuri(container, {
            items: '.board-item',
            layoutDuration: 400,
            layoutEasing: 'ease',
            dragEnabled: true,
            dragSort: function () {
                return columnGrids;
            },
            dragSortInterval: 0,
            dragContainer: document.body,
            dragReleaseDuration: 400,
            dragReleaseEasing: 'ease'
        })
            .on('dragStart', function (item) {
                // Let's set fixed widht/height to the dragged item
                // so that it does not stretch unwillingly when
                // it's appended to the document body for the
                // duration of the drag.
                item.getElement().style.width = item.getWidth() + 'px';
                item.getElement().style.height = item.getHeight() + 'px';
            })
            .on('dragReleaseEnd', function (item) {
                // Let's remove the fixed width/height from the
                // dragged item now that it is back in a grid
                // column and can freely adjust to it's
                // surroundings.
                item.getElement().style.width = '';
                item.getElement().style.height = '';
                // Just in case, let's refresh the dimensions of all items
                // in case dragging the item caused some other items to
                // be different size.
                columnGrids.forEach(function (grid) {
                    grid.refreshItems();
                });
            })
            .on('layoutStart', function () {
                // Let's keep the board grid up to date with the
                // dimensions changes of column grids.
                boardGrid.refreshItems().layout();
            });

        // Add the column grid reference to the column grids
        // array, so we can access it later on.
        columnGrids.push(gridLayout);

    });

    // When the user clicks on the button, open the modal
     modal.style.display = "block";

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }


}