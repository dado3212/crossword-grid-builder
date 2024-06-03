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