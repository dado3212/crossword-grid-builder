<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            // Respects 'Request Desktop Site'
			if (preg_match("/(iPhone|iPod|iPad|Android|BlackBerry)/i", $_SERVER["HTTP_USER_AGENT"])) {
				?><meta name="viewport" content="width=device-width, initial-scale=1.0"><?php
			}
        ?>
        <title>Crossword Builder</title>
        <link rel="stylesheet" type="text/css" href="main.css">
        <script src="main.js"></script>
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
        <div id="num"></div>
        <div id="gridSize" style="display: none;">
            <button data-size="21" class="selected" onclick="gridSizeClick(this)">
                21
            </button>
            <button data-size="23" onclick="gridSizeClick(this)">
                23
            </button>
        </div>
    </body>
</html>