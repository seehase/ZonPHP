document.addEventListener('DOMContentLoaded', function () {
    var itemContainers = [].slice.call(document.querySelectorAll('.board-column-content'));
    var columnGrids = [];
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

    console.log("Holg was herexxxxxxxxxxxxxxxxxxxxxxxxx");
});

