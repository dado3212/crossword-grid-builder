<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            // Respects 'Request Desktop Site'
			if (preg_match("/(iPhone|iPod|iPad|Android|BlackBerry)/i", $_SERVER["HTTP_USER_AGENT"])) {
				?><meta name="viewport" content="width=device-width, initial-scale=1.0"><?php
			}

            include("php/secret.php");

            $PDO = getDatabase();
            $historical_crosswords = $PDO->prepare(
                "SELECT min(`date`) as min_date, max(`date`) as max_date
                FROM historical_crosswords"
            );
            $historical_crosswords->execute();
            $date_info = $historical_crosswords->fetch();
        ?>
        <title>Crossword Grid Builder</title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <script src="main.js"></script>
    </head>
    <body>
        <div class="wrapper">
            <div id="grid">
            </div>
            <div id="gridInfo">
                <table>
                    <tr><td>Num Words</td><td id="words">0</td></tr>
                    <tr><td>Num Blocks</td><td id="blocks">0</td></tr>
                    <tr><td>Is Valid</td><td id="valid">Yes</td></tr>
                </table>
                <div>Symmetry</div>
                <!-- TODO: Add in proper icons for this -->
                <button title="Rotational" class="selected" data-format="0" onclick="selectionTypeClick(this)">
                    Rotational
                </button>
                <button title="Mirror" data-format="1" onclick="selectionTypeClick(this)">
                    Mirror
                </button>
                <br />
                <label for="heatmap">Show Heatmap</label>
                <input type="checkbox" name="heatmap" value="heatmap" checked onclick="showHeatmapClick(this)">

            </div>
        </div>
        <div id="date">
            <div>Day of the Week</div>
            <button title="Monday" data-day="Monday" class="selected" onclick="dayClick(this)">
                Mo
            </button>
            <button title="Tuesday" data-day="Tuesday" onclick="dayClick(this)">
                Tu
            </button>
            <button title="Wednesday" data-day="Wednesday" onclick="dayClick(this)">
                We
            </button>
            <button title="Thursday" data-day="Thursday" onclick="dayClick(this)">
                Th
            </button>
            <button title="Friday" data-day="Friday" onclick="dayClick(this)">
                Fr
            </button>
            <button title="Saturday" data-day="Saturday" onclick="dayClick(this)">
                Sa
            </button>
            <button title="Sunday" data-day="Sunday" onclick="dayClick(this)">
                Su
            </button>
        </div>
        <div id="gridSize" style="display: none;">
            <button data-size="21" class="selected" onclick="gridSizeClick(this)">
                21
            </button>
            <button data-size="23" onclick="gridSizeClick(this)">
                23
            </button>
        </div>
        <div id="calendar">
            <input type="date" name="minDate" min="<?php echo $date_info['min_date']; ?>" max="<?php echo $date_info['max_date']; ?>" value="<?php echo $date_info['min_date']; ?>" onchange="dateChange()">
            -
            <input type="date" name="maxDate" min="<?php echo $date_info['min_date']; ?>" max="<?php echo $date_info['max_date']; ?>" value="<?php echo $date_info['max_date']; ?>" onchange="dateChange()">
            <button onclick="updateDates(this)" disabled>⟳</button>
        </div>
        <div id="num"></div>
    </body>
</html>