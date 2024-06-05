let grid = [];

const formattingStates = {
    ROTATIONAL: 0,
    MIRROR: 1,
};

let format = formattingStates.ROTATIONAL;
let day_of_week = 'Monday';
let grid_size = 15;
let numBlocks = 0;
let showHeatmap = true;
let minDate;
let maxDate;

let historicalGrids = [];

function decodeBinary(base64String) {
    const binaryString = atob(base64String);

    // Convert binary string to byte array
    const byteArray = new Uint8Array(binaryString.length);
    for (let i = 0; i < binaryString.length; i++) {
        byteArray[i] = binaryString.charCodeAt(i);
    }

    // Convert byte array back to the original binary format (if needed)
    let originalBinaryString = '';
    for (let byte of byteArray) {
        originalBinaryString += byte.toString(2).padStart(8, '0');
    }
    return originalBinaryString;
}

function buildGrid() {
    // Build the grid, both UI and code
    const gridElement = document.getElementById('grid');
    gridElement.innerHTML = '';
    grid = [];
    numBlocks = 0;
    for (let r = 0; r < grid_size; r++) {
        // UI
        let row = document.createElement('div');
        row.classList.add('row');
        gridElement.appendChild(row);

        // Code
        grid.push([]);
        for (let c = 0; c < grid_size; c++) {
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
}

function createEmptyGrid() {
    const emptyGrid = [];
    for (let r = 0; r < grid_size; r++) {
        emptyGrid.push([]);
        for (let c = 0; c < grid_size; c++) {
            emptyGrid[r].push(0);
        }
    }
    return emptyGrid;
}

// For a given historical grid it checks it against the currently selected
// grid and returns a boolean if it's still a plausible end state
function isValidHistoricalGrid(historicalGrid) {
    for (let r = 0; r < grid_size; r++) {
        for (let c = 0; c < grid_size; c++) {
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
                if (tempGrid[r][c] && !grid[r][c].classList.contains('selected')) {
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
    if (showHeatmap) {
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
    }
    // Update the max num display
    document.getElementById('num').innerHTML = numGrids + ' historical grids matching.';

    // Update the options view
    document.querySelector('#gridInfo #blocks').innerHTML = numBlocks;
    let numWords = 0;
    let minWordLength = null;
    let currentWordLength = 0;
    for (let r = 0; r < grid.length; r++) {
        for (let c = 0; c < grid[r].length; c++) {
            // Black square
            if (grid[r][c].classList.contains('selected')) {
                if (currentWordLength != 0) {
                    numWords += 1;
                    if (minWordLength == null || currentWordLength < minWordLength) {
                        minWordLength = currentWordLength;
                    }
                    currentWordLength = 0;
                }
            // White square
            } else {
                currentWordLength += 1;
            }
        }
        if (currentWordLength != 0) {
            numWords += 1;
            if (minWordLength == null || currentWordLength < minWordLength) {
                minWordLength = currentWordLength;
            }
            currentWordLength = 0;
        }
    }
    for (let c = 0; c < grid[0].length; c++) {
        for (let r = 0; r < grid.length; r++) {
            // Black square
            if (grid[r][c].classList.contains('selected')) {
                if (currentWordLength != 0) {
                    numWords += 1;
                    if (minWordLength == null || currentWordLength < minWordLength) {
                        minWordLength = currentWordLength;
                    }
                    currentWordLength = 0;
                }
            // White square
            } else {
                currentWordLength += 1;
            }
        }
        if (currentWordLength != 0) {
            numWords += 1;
            if (minWordLength == null || currentWordLength < minWordLength) {
                minWordLength = currentWordLength;
            }
            currentWordLength = 0;
        }
    }
    document.querySelector('#gridInfo #words').innerHTML = numWords;
    document.querySelector('#gridInfo #valid').innerHTML = (minWordLength >= 3 ? 'Yes' : 'No');
    document.querySelector('#gridInfo #valid').className = (minWordLength >= 3 ? 'valid' : 'invalid');
}

function dateChange() {
    document.querySelector('#calendar button').disabled = false;
}

function updateDates(button) {
    // Disable the button, it should only work after
    button.disabled = true;
    minDate = document.querySelector('#calendar input[name="minDate"]').value;
    maxDate = document.querySelector('#calendar input[name="maxDate"]').value;

    // Fetch the historical grids info
    fetchHistoricGrids();
}

function showHeatmapClick(checkbox) {
    showHeatmap = checkbox.checked;

    renderGrid();
}

function selectionTypeClick(selectionType) {
    if (selectionType.classList.contains('selected')) {
        // Do nothing if it's already selected
        return;
    }

    // Clear the others as selected and select the new choise
    // TODO: Should this just be a radio button?
    document.querySelectorAll('#gridInfo button').forEach((e) => e.classList.remove('selected'));
    selectionType.classList.add('selected');

    // TODO: Yikes
    format = parseInt(selectionType.dataset.format);
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

    day_of_week = day.dataset.day;
    // Adjust whether the size selector is set
    if (day_of_week === 'Sunday') {
        grid_size = 21;
        document.querySelectorAll('#gridSize button').forEach((e) => e.classList.remove('selected'));
        document.querySelector('#gridSize button[data-size="21"]').classList.add('selected');
        document.getElementById('gridSize').style.display = 'block';
        buildGrid();
    } else {
        if (grid_size != 15) {
            grid_size = 15;
            document.getElementById('gridSize').style.display = 'none';
            buildGrid();
        }
    }

    // Fetch the historical grids info
    fetchHistoricGrids();
}

function gridSizeClick(gridSize) {
    if (gridSize.classList.contains('selected')) {
        // Do nothing if it's already selected
        return;
    }

    // Clear the others as selected and select the new choise
    // TODO: Should this just be a radio button?
    document.querySelectorAll('#gridSize button').forEach((e) => e.classList.remove('selected'));
    gridSize.classList.add('selected');

    // Fetch the historical grids info
    grid_size = parseInt(gridSize.dataset.size);
    buildGrid();
    fetchHistoricGrids();
}

function getCorrespondingCell(cell) {
    // Based on mirror or rotational symmetry handle the other cell that should
    // be added (Javascript handles negative numbers oh so magically)
    let otherCell;
    switch (format) {
        case formattingStates.ROTATIONAL:
            otherCell = grid[grid_size - cell.dataset.row - 1][grid_size - cell.dataset.col - 1];
            break;
        case formattingStates.MIRROR:
            otherCell = grid[cell.dataset.row][grid_size - cell.dataset.col - 1];
            break;
    }
    return otherCell;
}


function gridCellClick(cell) {
    cell.classList.toggle('selected');
    let newState = cell.classList.contains('selected');

    let otherCell = getCorrespondingCell(cell);

    if (newState) {
        numBlocks += 1;
        if (cell !== otherCell) {
            otherCell.classList.add('selected');
            numBlocks += 1;
        }
    } else {
        numBlocks -= 1;
        if (cell !== otherCell) {
            otherCell.classList.remove('selected');
            numBlocks -= 1;
        }
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

function fetchHistoricGrids() {
    // Start the loading indicator
    const loadingElement = document.getElementById('loading');
    loadingElement.style.display = 'block';
    loadingElement.classList.remove('error');
    loadingElement.innerHTML = 'Loading...';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'php/historical_data.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function (data) {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (data.target.status !== 200) {
                loadingElement.innerHTML = 'Error: ' + JSON.parse(data.target.response)['message'];
                loadingElement.classList.add('error');
            } else {
                loadingElement.style.display = 'none';
                console.log(data.target.response);
                const info = JSON.parse(data.target.response);
                // Decode the base64 encoded grids
                const properGrids = [];
                for (let i = 0; i < info['grids'].length; i++) {
                    let tempGrid = [];
                    let decoded = decodeBinary(info['grids'][i]).substring(0, grid_size ** 2);
                    let rowIndex = -1;
                    for (let j = 0; j < decoded.length; j++) {
                        if (j % grid_size === 0) {
                            tempGrid.push([]);
                            rowIndex += 1;
                        }
                        if (decoded[j] == '0') {
                            tempGrid[rowIndex].push(false);
                        } else {
                            tempGrid[rowIndex].push(true);
                        }
                    }
                    properGrids.push(tempGrid);
                }
                historicalGrids = properGrids;
                const wordRange = info['word_range'];
                document.querySelector('#gridInfo #words').title = wordRange[0] + ' - ' + wordRange[1];
                const blockRange = info['block_range'];
                document.querySelector('#gridInfo #blocks').title = blockRange[0] + ' - ' + blockRange[1];

                renderGrid();
            }
        }
    }
    xhr.send('day=' + day_of_week + '&size=' + grid_size + '&minDate=' + minDate + '&maxDate=' + maxDate);
}

window.onload = () => {
    minDate = document.querySelector('#calendar input[name="minDate"]').value;
    maxDate = document.querySelector('#calendar input[name="maxDate"]').value;

    // Fetch the historical grids info
    fetchHistoricGrids();

    // Build the grid, both UI and code
    buildGrid();

    renderGrid();
};