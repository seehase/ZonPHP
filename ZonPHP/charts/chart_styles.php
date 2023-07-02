<?php
/**
 * general options for all charts
 */
$chart_lang_de = "lang: {
                     decimalPoint: ',',
                     thousandsSep: '.',
                     loading: 'Daten werden geladen...',
                     exportButtonTitle: \"Exportieren\",
                     printButtonTitle: \"Drucken\",
                     rangeSelectorFrom: \"Von\",
                     rangeSelectorTo: \"Bis\",
                     rangeSelectorZoom: \"Zeitraum\",
                     downloadPNG: 'Download als PNG-Bild',
                     downloadJPEG: 'Download als JPEG-Bild',
                     downloadPDF: 'Download als PDF-Dokument',
                     downloadSVG: 'Download als SVG-Bild',
                     resetZoom: \"Zoom zurücksetzen\",
                     resetZoomTitle: \"Zoom zurücksetzen\"
                       },";

$chart_lang_nl = "lang: {
                    loading: 'Wordt geladen...',
                    exportButtonTitle: \"Exporteren\",
                    printButtonTitle: \"Printen\",
                    rangeSelectorFrom: \"Vanaf\",
                    rangeSelectorTo: \"Tot\",
                    rangeSelectorZoom: \"Periode\",
                    downloadPNG: 'Download als PNG',
                    downloadJPEG: 'Download als JPEG',
                    downloadPDF: 'Download als PDF',
                    downloadSVG: 'Download als SVG',
                    resetZoom: 'Reset',
                    resetZoomTitle: 'Reset',
                    thousandsSep: '.',
                    decimalPoint: ','
                    },";

$chart_lang_fr = "lang: {
                    loading: 'Chargement...',
                    exportButtonTitle: \"Exporter\",
                    printButtonTitle: \"Imprimer\",
                    rangeSelectorFrom: \"Du\",
                    rangeSelectorTo: \"au\",
                    rangeSelectorZoom: \"Période\",
                    downloadPNG: 'Télécharger en PNG',
                    downloadJPEG: 'Télécharger en JPEG',
                    downloadPDF: 'Télécharger en PDF',
                    downloadSVG: 'Télécharger en SVG',
                    resetZoom: \"Réinitialiser le zoom\",
                    resetZoomTitle: \"Réinitialiser le zoom\",
                    thousandsSep: \" \",
                    decimalPoint: ','
                    },";

$chart_lang = "";
if (isset($_SESSION['language'])) {
    if ($_SESSION['language'] == 'de') {
        $chart_lang = $chart_lang_de;
    } else if ($_SESSION['language'] == 'nl') {
        $chart_lang = $chart_lang_nl;
    } else if ($_SESSION['language'] == 'fr') {
        $chart_lang = $chart_lang_fr;
    }
}

if ($isIndexPage == true) {
    $displayType = "'none'";
	$export = "false";
} else {
    $displayType = "'block'";
	$export = "true";
}

$chart_options =
    "{     
     accessibility: {
     	  enabled: false
  	 },
     exporting: {
        enabled: $export
     },
     title: {
         text: '',    
         style: {
             display: 'none'
         } 
     },
     subtitle: {
         text: '',
         style: {
             display: $displayType
         }
     },            
     chart: {
                zoomType: 'xy',
                backgroundColor: '" . $colors['color_chartbackground'] . "',
                alignThresholds: false,
            },
     legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'bottom',
                floating: false,
                backgroundColor: '" . $colors['color_chartbackground'] . "',
                enabled: $show_legende,
     },                                
     tooltip: {},
     plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function () {
                                if (this.options.url.length > 0)
                                {
                                    location.href = this.options.url;
                                }
                            }
                        }
                    }
                },
                spline: {
                    lineWidth: 1,
                    states: {
                        hover: {
                            lineWidth: 2
                        }
                    },
                    marker: {
                        enabled: false,
                        radius: 2,
                        states: {
                            hover: {
                                enabled: true,
                                symbol: 'circle',
                                radius: 4,
                                lineWidth: 1
                            }
                        }
                    },
                    threshold: 0,
                },
                
                column: {
                    lineWidth: 1,
                    grouping: false,
                    shadow: false,
                    borderWidth: 0,
                    borderRadius: 3,
                },
                line: {
                    lineWidth: 2,
                    states: {
                        hover: {
                            lineWidth: 3
                        }
                    },
                    marker: {
                        enabled: false,
                        states: {
                            hover: {
                                enabled: true,
                                symbol: 'circle',
                                radius: 2,
                                lineWidth: 1
                            }
                        }
                    }
                }

            },
    credits: {
                enabled: false
            },
    reflow: true,                
    }";
//introducing new variable $shortmonthcategories to replace the fixed arrays in chart_lang_xx
//for use in year_chart, last_years_chart and cumulative_chart
//format follows ICU and Unicode
//skipping months, weekdays and shortMonths from chart_lang_xx
$formatter->setPattern('LLL');
$shortmonthcategories = "";
for ($i = 1; $i <= 12; $i++) {
    // get month names in current locale
    $shortmonthcategories .= '"' . str_replace('.', '', datefmt_format($formatter, mktime(0, 0, 0, $i,15))) . '",';
}
$shortmonthcategories = substr($shortmonthcategories, 0, -1);
?>
