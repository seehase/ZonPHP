let grid = [];

function docReady(fn) {
    // see if DOM is already available
    if (document.readyState === "complete" || document.readyState === "interactive") {
        // call on next available tick
        setTimeout(fn, 1);
    } else {
        document.addEventListener("DOMContentLoaded", fn);
    }
}

function showModalImage(myid) {
    // https://www.w3schools.com/howto/howto_css_modal_images.asp
    const img = document.getElementById(myid);
    const modalImg = document.getElementById("modal-image");
    const captionText = document.getElementById("caption");
    const modal = document.getElementById("myModal");

    modal.style.display = "block";
    modalImg.src = img.src;
    captionText.innerHTML = img.alt;
    modal.onclick = function () {
        modal.style.display = "none";
    }
}

function load_charts() {
    grid = new Muuri('.grid', {
        dragEnabled: false,
        layout: {
            fillGaps: true
        }
    }).on('move', function () {
        saveLayout(grid);
    });

    const layout = window.localStorage.getItem('layout');
    if (layout) {

        loadLayout(grid, layout);
    } else {
        grid.layout(true);
    }

    function serializeLayout(grid) {
        const itemIds = grid.getItems().map(function (item) {
            return item.getElement().getAttribute('data-id');
        });
        return JSON.stringify(itemIds);
    }

    function saveLayout(grid) {
        const layout = serializeLayout(grid);
        window.localStorage.setItem('layout', layout);
    }

    function loadLayout(grid, serializedLayout) {
        const layout = JSON.parse(serializedLayout);
        const currentItems = grid.getItems();
        const currentItemIds = currentItems.map(function (item) {
            return item.getElement().getAttribute('data-id')
        });
        const newItems = [];
        let itemId;
        let itemIndex;

        for (let i = 0; i < layout.length; i++) {
            itemId = layout[i];
            itemIndex = currentItemIds.indexOf(itemId);
            if (itemIndex > -1) {
                newItems.push(currentItems[itemIndex])
            }
        }
        grid.sort(newItems, {layout: 'instant'});
    }

    const headerclass = "jqx-window-header jqx-window-header-index-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp";

    /* load all charts according order */

    function loadCard(name) {
        name = name.toUpperCase();
        switch (name) {
            case 'DAY':
                addDay();
                break;
            case 'MONTH':
                addMonth();
                break;
            case 'YEAR':
                addYear();
                break;
            case 'ALLYEARS':
                addAllYears();
                break;
            case 'CUMULATIVE':
                addCumulative();
                break;
            case 'YEARPERMONTH':
                addLastYears();
                break;
            case 'FARM':
                addFarm();
                break;
            case 'PLANTS':
                addPlants();
                break;
            case 'IMAGES':
                addImages();
                break;
            case 'TOP':
                addBest();
                break;
            default:
                console.log("Sorry, we are out of " + name);
        }
    }

    function loadCharts() {
        cardlayout.map(it => loadCard(it))
    }

    // loadLayout(grid, layout);

    function addDay() {
        const id = "id_Day";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div  class="item-content card"> ' +
            '       <a href="./pages/day.php"><div id="chart_header_day" class="' + headerclass + '">' + daytext + " - " + chartdatestring + '</div> </a> ' +
            '       <div id="' + id + '">' +
            '   </div>' +
            '</div>';

        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
        $.ajax({
            url: 'charts/day_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (Chart) {
                $(container).append(Chart);
            }
        });
    }

    function addMonth() {
        const id = "id_Month";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div  class="item-content card"> ' +
            '       <a href="./pages/month.php"><div id="chart_header_month" class="' + headerclass + '">' + txt["chart_month_view"] + " - " + chartmonthstring + " " + chartyearstring + '</div> </a> ' +
            '       <div id="' + id + '">' +
            '   </div>' +
            '</div>';

        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
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
        const id = "id_AllYears";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div  class="item-content card"> ' +
            '       <a href="./pages/years.php"><div id="chart_header_allYears" class="' + headerclass + '">' + txt["chart_years_view"] + '</div></a>' +
            '       <div id ="' + id + '">' +
            '   </div>' +
            '</div>';

        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
        $.ajax({
            url: 'charts/years_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }


    function addBest() {
        const id = "id_Best";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div class="item-content card"> ' +
            '       <a href="./pages/ranking.php"><div id="chart_header" class="' + headerclass + '">' + txt["ranking"] + '</div></a>' +
            '       <div id="' + id + '">' +
            '   </div>' +
            '   </div>';
        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
        $.ajax({
            url: 'charts/ranking_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            }
        });
    }

    function addYear() {
        const id = "id_Year";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div class="item-content card"> ' +
            '       <a href="./pages/year.php"><div id="chart_header_year" class="' + headerclass + '">' + txt["chart_year_view"] + " - " + chartyearstring + '</div></a>' +
            '       <div    id="' + id + '">' +
            '   </div>' +
            '</div>';
        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
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
        const id = "id_Cumulative";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div class  ="item-content card"> ' +
            '       <a href="./pages/years_cumulative.php"><div id="chart_header" class="' + headerclass + '">' + txt["chart_years_cumulative_view"] + '</div></a>' +
            '       <div id="' + id + '">' +
            '   </div>' +
            '</div>';
        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
        $.ajax({
            url: 'charts/years_cumulative_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            },
        });
    }

    function addLastYears() {
        const id = "id_LastYears";
        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div class="item-content card"> ' +
            '       <a href="./pages/months.php"><div id="chart_header" class="' + headerclass + '">' + txt["chart_months_view"] + '</div></a>' +
            '       <div id="' + id + '">' +
            '   </div>' +
            '</div>';
        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);

        const container = $('#' + id);
        $.ajax({
            url: 'charts/months_chart.php',
            type: 'post',
            data: {'action': 'indexpage'},
            cache: false,
            success: function (chart) {
                $(container).append(chart);
            },
        });
    }

    function addFarm() {
        const id = "id_farm";
        let plants = farm['plants'];
        let plantText = "";
        for (const key in plants) {
            plantText += txt['plant'] + ' : ' + key + '<br>';
            plantText += txt['startdate'] + ' : ' + plants[key]['installationDate'] + '<br>';
            plantText += txt['capacity'] + ' : ' + plants[key]['capacity'] + '<br><hr>'
        }

        const itemTemplate = '' +
            '<div class="item h4 w4" data-id="' + id + '">' +
            '   <div class="item-content card" style="background-color: ' + theme['color_chartbackground'] + ';"> ' +
            '      <div id="chart_header" class="' + headerclass + '">' + txt["card_farm_information"] + '</div>' +
            '      <div class="index_chart" id="' + id + '" ">' +
            '          <div class="chart-container" >' +
            '             <br>' +
            '             <h1>' + farm['name'] + '</h1>' +
            '             <br>' +
            '             ' + txt['website'] + ' : ' + farm['website'] + '<br>' +
            '             ' + txt['location'] + ' : ' + farm['location'] + '<br>' +
            '             ' + txt['startdate'] + ' : ' + farm['installationDate'] + '<br>' +
            '             ' + txt['capacity'] + ' : ' + farm['totalCapacity'] + '<br>' +
            '             ' + txt['importer'] + ' : ' + farm['importer'] +
            '             ' + '<hr>' + plantText +
            '          </div>' +
            '       </div>' +
            '    </div>' +
            '</div>';
        const itemElem = document.createElement('div');
        itemElem.innerHTML = itemTemplate;
        grid.add(itemElem.firstChild);
    }

    function addPlants() {
        let plants = farm['plants'];
        for (const key in plants) {
            let id = "id_plant_" + key;
            const itemTemplate = '' +
                '<div class="item h4 w4" data-id="' + id + '">' +
                '   <div class="item-content card" style="background-color: ' + theme['color_chartbackground'] + ';"> ' +
                '      <div id="chart_header" class="' + headerclass + '">' + txt['plant'] + ' - ' + key + '</div>' +
                '      <div class="index_chart" id="' + id + '" ">' +
                '          <div class="chart-container" >' +
                '                 <br>' +
                '                 ' + plants[key]['description'] + '<br>' +
                '          </div>' +
                '       </div>' +
                '    </div>' +
                '</div>';
            const itemElem = document.createElement('div');
            itemElem.innerHTML = itemTemplate;
            grid.add(itemElem.firstChild);
        }
    }

    function addImages() {
        for (const key in images) {
            let uri = '';
            if (images[key]['uri'].indexOf("http") >= 0) {
                uri = images[key]['uri'];
            } else {
                uri = './images/' + images[key]['uri'];
            }
            let id = "id_image_" + key;
            const itemTemplate = '' +
                '<div class="item h4 w4" data-id="' + id + '">' +
                '   <div class="item-content card" style="background-color: ' + theme['color_chartbackground'] + ';"> ' +
                '      <div id="chart_header" class="' + headerclass + '">' + images[key]['title'] + '</div>' +
                '      <div class="index_chart" id="' + "chart_" + id + '">' +
                '         <div class="chart-container" >' +
                '            <br><p>' + images[key]['description'] + '</p> ' +
                '            <div>' +
                '               <img class="index-image" id="' + id + '" onclick="showModalImage(\'' + id + '\')" src="' + uri + '" alt="' + images[key]['title'] + '" width="400" height="300"> ' +
                '            </div>' +
                '         </div>' +
                '     </div>' +
                '   </div>' +
                '</div>';
            const itemElem = document.createElement('div');
            itemElem.innerHTML = itemTemplate;
            grid.add(itemElem.firstChild);
        }
    }

    loadCharts();
}
