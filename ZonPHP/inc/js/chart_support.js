function findDatasetById(datasets, name) {
    for (let i in datasets) {
        let label = datasets[i].datasetId;
        if (name === label) {
            return i;
        }
    }
    return -1; // not found
}

// copy array and set y-value to 0
function cloneAndResetY(originalArray) {
    let newArray = [];
    for (let i in originalArray) {
        newArray[i] = {x: originalArray[i].x, y: 0};
    }
    return newArray;
}

function stripLastChar(value) {
    if (value.length > 0)
        return value.substring(0, value.length - 1);
}

function getPlugin() {
    return {
        id: 'customCanvasBackgroundColor',
        beforeDraw:
            (chart, args, options) => {
                const {ctx} = chart;
                ctx.save();
                ctx.globalCompositeOperation = 'destination-over';
                ctx.fillStyle = options.color || '#99ffff';
                ctx.fillRect(0, 0, chart.width, chart.height);
                ctx.restore();
            }
    }
}

function getCustomLegendClickHandler() {
    return function (e, legendItem, legend) {
        let chart = legend.chart;
        Chart.defaults.plugins.legend.onClick(e, legendItem, legend);
        let data = chart.data;
        let avgSum = [];
        let expectedSum = [];
        let cumSum = []
        let maxSum = [];
        let refSum = [];

        for (let i in data.datasets) {
            let meta = chart.getDatasetMeta(i);
            let dataset = chart.data.datasets[i];
            let isHidden = meta.hidden === null ? false : meta.hidden;
            if (dataset.isData && !isHidden) {
                if (cumSum.length === 0) {
                    cumSum = cloneAndResetY(dataset.dataCUM)
                }
                // avg
                for (let ii in dataset.data) {
                    if (avgSum[ii] == null) avgSum[ii] = 0;
                    avgSum[ii] = avgSum[ii] + dataset.averageValue;
                }
                // expected
                for (let ii in dataset.data) {
                    if (expectedSum[ii] == null) expectedSum[ii] = 0;
                    expectedSum[ii] = expectedSum[ii] + dataset.expectedValue;
                }
                // max
                for (let ii in dataset.data) {
                    if (maxSum[ii] == null) maxSum[ii] = 0;
                    if (dataset.dataMAX[ii] != null) {
                        maxSum[ii] = maxSum[ii] + dataset.dataMAX[ii].y;
                    }
                }
                // cum
                for (let ii in dataset.data) {
                    if (dataset.dataCUM[ii].y != null) {
                        cumSum[ii].y = cumSum[ii].y + dataset.dataCUM[ii].y;
                    }
                }
                // ref per month
                for (let ii in dataset.data) {
                    if (refSum[ii] == null) refSum[ii] = 0;
                    if (dataset.dataREF[ii] != null) {
                        refSum[ii] = refSum[ii] + dataset.dataREF[ii].y;
                    }
                }
            }
        }
        let avgIDX = findDatasetById(data.datasets, "avg");
        if (avgIDX > 0) {
            data.datasets[avgIDX].data = avgSum;
        }
        let expectedIDX = findDatasetById(data.datasets, "expected");
        if (expectedIDX > 0) {
            data.datasets[expectedIDX].data = expectedSum;
        }
        let cumIDX = findDatasetById(data.datasets, "cum");
        if (cumIDX > 0) {
            data.datasets[cumIDX].data = cumSum;
        }
        let maxIDX = findDatasetById(data.datasets, "max");
        if (maxIDX > 0) {
            data.datasets[maxIDX].data = maxSum;
        }
        let refIDX = findDatasetById(data.datasets, "ref");
        if (refIDX > 0) {
            data.datasets[refIDX].data = refSum;
        }
        /*        let mySubTitle = {
                    text: buildSubtitle(legend),
                    display: true,
                };

                chart.options.plugins.subtitle = mySubTitle;

         */
        chart.update();
    };
}

function myPrompt() {
    let dialog = document.querySelector("#prompt");
    dialog.show();
}

function checkInverters() {
    let checkbox = document.getElementById("all_inverter")
    let inverters = document.getElementsByName("inverters")
    inverters.forEach(function (inverter) {
        inverter.checked = checkbox.checked;
    })
}

function updateCheckedInverters() {
    let allChecked = true
    let checkbox = document.getElementById("all_inverter")
    let inverters = document.getElementsByName("inverters")
    inverters.forEach(function (inverter) {
        if (inverter.checked === false) {
            allChecked = false;
        }
    })
    checkbox.checked = allChecked;
}

function checkYears() {
    let checkbox = document.getElementById("all_year")
    let years = document.getElementsByName("years")
    years.forEach(function (year) {
        year.checked = checkbox.checked
    })
}

function updateCheckedYears() {
    let allChecked = true
    let checkbox = document.getElementById("all_year")
    let years = document.getElementsByName("years")
    years.forEach(function (year) {
        if (year.checked === false) {
            allChecked = false;
        }
    })
    checkbox.checked = allChecked;
}

function checkMonths() {
    let checkbox = document.getElementById("all_months")
    let months = document.getElementsByName("months")
    months.forEach(function (month) {
        month.checked = checkbox.checked
    })
}

function updateCheckedMonths() {
    let allChecked = true
    let checkbox = document.getElementById("all_months")
    let months = document.getElementsByName("months")
    months.forEach(function (month) {
        if (month.checked === false) {
            allChecked = false;
        }
    })
    checkbox.checked = allChecked;
}

function myCancel() {
    let dialog = document.querySelector("#prompt");
    dialog.close();
}

function getSelectedMonths() {
    const months = document.getElementsByName("months")
    let selectedMonths = "";
    months.forEach(function (month) {
        if (month.checked) {
            selectedMonths = selectedMonths + month.value + ","
        }
    })
    return stripLastChar(selectedMonths)
}

function getSelectedYears() {
    let years = document.getElementsByName("years")
    let selectedYears = "";
    years.forEach(function (year) {
        if (year.checked) {
            selectedYears = selectedYears + year.value + ","
        }
    })
    return stripLastChar(selectedYears)
}

function getSelectedInverters() {
    let inverters = document.getElementsByName("inverters")
    let selectedInverters = "";
    inverters.forEach(function (inverter) {
        if (inverter.checked) {
            selectedInverters = selectedInverters + inverter.value + ","
        }
    })
    return stripLastChar(selectedInverters)
}

function customGradientBackground(context) {
    const {dataIndex, datasetIndex, element} = context;

    let {height, base} = element.getProps(["base", "height"], true);
    let ygTop, ygBottom;
    if (!height) {
        const vScale = context.chart.getDatasetMeta(context.datasetIndex).vScale;
        const stacksY = context.parsed?._stacks?.y?._visualValues ?? [context.parsed.y];
        let yMax = stacksY[0], yMin = 0;
        for (let i = 0; i < datasetIndex; i++) {
            if (!Number.isFinite(stacksY[i + 1])) {
                break;
            }
            yMin = yMax;
            yMax += stacksY[i + 1];
        }
        [ygTop, ygBottom] = [yMax, yMin].map(vScale.getPixelForValue, vScale);
        if (!ygBottom) {
            return false;
        }
    } else {
        ygTop = base - height;
        ygBottom = base;
    }
    const gradientFill = context.chart.ctx.createLinearGradient(0, ygBottom, 0, ygTop);


    let myChart = context.chart.data;
    const myColors = context.chart.data.myColors;
    let inverter = myChart.datasets[context.datasetIndex].inverter;
    let maxIdx = 0;
    let minCol;
    let maxCol;
    if (inverter != null) {
        minCol = myColors[inverter].min;
        maxCol = myColors[inverter].max;
        maxIdx = context.chart.data.maxIndex; // myChart.datasets[context.datasetIndex].maxIndex;
    } else {
        minCol = '#003399';
        maxCol = '#F82F04';
    }
    if (maxIdx > 0) {
        maxIdx = maxIdx - 1;
    }

    // use datasetIndex to identify the dataset and dataIndex to identify the bar within the set
    if (datasetIndex === maxIdx) {
        //    gradientFill.addColorStop(0.0, '#F82F04');  // first dataset starts from blue
    } else {
        //  gradientFill.addColorStop(0.0, minCol); // second dataset starts from green
    }
    // bars
    if (dataIndex === maxIdx) {
        // max bar
        gradientFill.addColorStop(0.0, '#AC0001');
        gradientFill.addColorStop(1.0, '#FC000D');
    } else {
        gradientFill.addColorStop(0.0, minCol);
        gradientFill.addColorStop(1.0, maxCol);
    }

    return gradientFill;
}