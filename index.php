<!DOCTYPE html>
<html lang="en">
    <head>
        <style>
            #grid {
                border: 1px solid black;
                display: flex;
                flex-direction: column;
                width: calc((15px + 1px * 2) * 15);
            }

            .row {
                display: flex;
            }

            .cell {
                width: 15px;
                height: 15px;
                border: 1px solid black;

                cursor: pointer;
            }
            .cell.selected {
                background-color: black;
            }
            .cell.selected.hovered {
                background-color: #333;
            }
            .cell:not(.selected).hovered {
                background-color: #ccc;
            }

            .cell .popularity-display {
                width: 100%;
                height: 100%;
            }
            .cell.selected .popularity-display {
                display: none;
            }
            .cell.hovered .popularity-display {
                display: none;
            }

            button {
                cursor: pointer;
            }

            button.selected {
                background-color: gray;
            }
        </style>
        <script>
            let grid = [];

            const formattingStates = {
                ROTATIONAL: 0,
                MIRROR: 1,
            };

            let format = formattingStates.ROTATIONAL;

            /** Temporary for not having internet */
            let historicalGrids = [];

            function createEmptyGrid() {
                const emptyGrid = [];
                for (let r = 0; r < 15; r++) {
                    emptyGrid.push([]);
                    for (let c = 0; c < 15; c++) {
                        emptyGrid[r].push(0);
                    }
                }
                return emptyGrid;
            }

            function generateHistoricalGrid(format) {
                // For now, just assume rotational
                const grid = createEmptyGrid();
                for (let r = 0; r < 8; r++) {
                    for (let c = 0; c < 15; c++) {
                        const isFilled = Math.random() >= 0.5;
                        grid[r][c] = isFilled;
                        grid[15 - r - 1][15 - c - 1] = isFilled;
                    }
                }
                return grid;
            }

            for (let i = 0; i < 20; i++) {
                historicalGrids.push(generateHistoricalGrid());
            }

            historicalGrids = [
            [[false, false, false, true, false, false, false, true, false, false, false, true, false, false, false], [false, false, false, false, false, false, false, true, false, false, false, false, false, false, false], [false, false, false, false, false, false, false, false, false, false, false, false, false, false, false], [true, true, true, false, false, false, false, false, false, false, false, false, true, true, true], [false, false, false, true, true, false, false, false, false, true, true, true, false, false, false], [false, false, false, false, false, false, false, false, false, false, false, false, false, false, false], [false, false, false, false, false, true, true, false, false, false, false, false, false, false, false], [true, true, false, false, false, false, false, true, false, false, false, false, false, true, true], [false, false, false, false, false, false, false, false, true, true, false, false, false, false, false], [false, false, false, false, false, false, false, false, false, false, false, false, false, false, false], [false, false, false, true, true, true, false, false, false, false, true, true, false, false, false], [true, true, true, false, false, false, false, false, false, false, false, false, true, true, true], [false, false, false, false, false, false, false, false, false, false, false, false, false, false, false], [false, false, false, false, false, false, false, true, false, false, false, false, false, false, false], [false, false, false, true, false, false, false, true, false, false, false, true, false, false, false]]
            ];

            // For a given historical grid it checks it against the currently selected
            // grid and returns a boolean if it's still a plausible end state
            function isValidHistoricalGrid(historicalGrid) {
                for (let r = 0; r < 15; r++) {
                    for (let c = 0; c < 15; c++) {
                        if (grid[r][c].classList.contains('selected') &&  !historicalGrid[r][c]) {
                            return false;
                        }
                    }
                }
                return true;
            }

            function renderGrid() {
                let status = createEmptyGrid();
                let max = 0;
                let numGrids = 0;

                // Calculate the summed occurrences
                for (let i = 0; i < historicalGrids.length; i++) {
                    let tempGrid = historicalGrids[i];
                    // Filter out grids that don't match
                    if (!isValidHistoricalGrid(tempGrid)) {
                        continue;
                    }
                    numGrids += 1;
                    for (let r = 0; r < tempGrid.length; r++) {
                        for (let c = 0; c < tempGrid[r].length; c++) {
                            if (tempGrid[r][c]) {
                                status[r][c] += 1;
                                if (status[r][c] > max) {
                                    max = status[r][c];
                                }
                            }
                        }
                    }
                }
                // Delete the old rendering
                document.querySelectorAll('.popularity-display').forEach((e) => e.remove());
                // Render out the new ones, normalized
                for (let r = 0; r < status.length; r++) {
                    for (let c = 0; c < status[r].length; c++) {
                        const perc = status[r][c] / max;

                        let popularityDisplay = document.createElement('div');
                        popularityDisplay.classList.add('popularity-display');
                        popularityDisplay.style.backgroundColor = 'rgba(255, 0, 0, ' + perc + ')';
                        grid[r][c].appendChild(popularityDisplay);
                    }
                }
                // Update the max num display
                document.getElementById('num').innerHTML = numGrids + ' historical grids matching.';
            }

            function optionClick(option) {
                console.log(option.innerHTML);
                if (option.classList.contains('selected')) {
                    // Do nothing if it's already selected
                    return;
                }

                // Clear the others as selected and select the new choise
                // TODO: Should this just be a radio button?
                document.querySelectorAll('#options button').forEach((e) => e.classList.remove('selected'));
                option.classList.add('selected');

                // TODO: Yikes
                format = parseInt(option.dataset.format);
            }

            function dayClick(day) {
                if (day.classList.contains('selected')) {
                    // Do nothing if it's already selected
                    return;
                }

                // Clear the others as selected and select the new choise
                // TODO: Should this just be a radio button?
                document.querySelectorAll('#date button').forEach((e) => e.classList.remove('selected'));
                day.classList.add('selected');

                // TODO: Actually do something with this (needs server hookups)
            }

            function getCorrespondingCell(cell) {
                // Based on mirror or rotational symmetry handle the other cell that should
                // be added (Javascript handles negative numbers oh so magically)
                let otherCell;
                switch (format) {
                    case formattingStates.ROTATIONAL:
                        otherCell = grid[15 - cell.dataset.row - 1][15 - cell.dataset.col - 1];
                        break;
                    case formattingStates.MIRROR:
                        otherCell = grid[cell.dataset.row][15 - cell.dataset.col - 1];
                        break;
                }
                return otherCell;
            }


            function gridCellClick(cell) {
                cell.classList.toggle('selected');
                let newState = cell.classList.contains('selected');

                let otherCell = getCorrespondingCell(cell);

                if (newState) {
                    otherCell.classList.add('selected');
                } else {
                    otherCell.classList.remove('selected');
                }
                renderGrid();
            }

            function gridCellHoverChange(cell, isHovered) {
                let otherCell = getCorrespondingCell(cell);
                if (isHovered) {
                    cell.classList.add('hovered');
                    otherCell.classList.add('hovered');
                } else {
                    cell.classList.remove('hovered');
                    otherCell.classList.remove('hovered');
                }
            }

            window.onload = () => {
                // Build the grid, both UI and code
                const gridElement = document.getElementById('grid');
                for (let r = 0; r < 15; r++) {
                    // UI
                    let row = document.createElement('div');
                    row.classList.add('row');
                    gridElement.appendChild(row);

                    // Code
                    grid.push([]);
                    for (let c = 0; c < 15; c++) {
                        // UI
                        let cell = document.createElement('div');
                        cell.classList.add('cell');
                        cell.dataset.row = r;
                        cell.dataset.col = c;
                        cell.addEventListener('click', (_) => gridCellClick(cell));
                        cell.addEventListener('mouseover', (_) => gridCellHoverChange(cell, true));
                        cell.addEventListener('mouseout', (_) => gridCellHoverChange(cell, false));

                        row.appendChild(cell);

                        // Code
                        grid[r].push(cell);
                    }
                }

                renderGrid();
            };
        </script>
    </head>
    <body>
        <div id="grid">
        </div>
        <div id="options">
            <div>Symmetry</div>
            <!-- TODO: Add in proper icons for this -->
            <button title="Rotational" class="selected" data-format="0" onclick="optionClick(this)">
                Rotational
            </button>
            <button title="Mirror" data-format="1" onclick="optionClick(this)">
                Mirror
            </button>
        </div>
        <div id="date">
            <div>Day of the Week</div>
            <button title="Rotational" class="selected" onclick="dayClick(this)">
                Mo
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                Tu
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                We
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                Th
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                Fr
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                Sa
            </button>
            <button title="Mirror" onclick="dayClick(this)">
                Su
            </button>
        </div>
        <div id="num"></div>
    </body>
</html>