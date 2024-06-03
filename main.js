let grid = [];

const formattingStates = {
    ROTATIONAL: 0,
    MIRROR: 1,
};

let format = formattingStates.ROTATIONAL;

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
    // Build the historical grids info
    historicalGrids = JSON.parse(historicalCrosswords)['Monday'];

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