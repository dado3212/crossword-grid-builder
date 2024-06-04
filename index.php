<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Crossword Grid Builder</title>

        <!-- Meta tags -->
        <meta name="robots" content="index, follow, archive">
        <meta name="description" content="Design crossword grids by leaning on the shoulders of giants with a historic heatmap of NYTimes crossword grid patterns!">
        <meta charset="utf-8" />
        <meta http-equiv="Cache-control" content="public">

        <!-- Semantic Markup -->
        <meta property="og:title" content="Crossword Grid Builder">
        <meta property="og:type" content="website">
        <meta property="og:image" content="https://alexbeals.com/projects/catan/assets/preview.png">
        <meta property="og:url" content="https://alexbeals.com/projects/crossword">
        <meta property="og:description" content="Design crossword grids by leaning on the shoulders of giants with a historic heatmap of NYTimes crossword grid patterns!">

        <meta name="twitter:card" content="summary">
        <meta name="twitter:creator" content="@alex_beals">

        <!-- Favicons -->
        <link rel="apple-touch-icon" sizes="180x180" href="./assets/favicon/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="./assets/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="./assets/favicon/favicon-16x16.png">
        <link rel="manifest" href="./assets/favicon/site.webmanifest">
        <link rel="mask-icon" href="./assets/favicon/safari-pinned-tab.svg" color="#c77070">
        <link rel="shortcut icon" href="./assets/favicon/favicon.ico">
        <meta name="msapplication-TileColor" content="#b91d47">
        <meta name="msapplication-config" content="./assets/favicon/browserconfig.xml">
        <meta name="theme-color" content="#ffffff">
        
        <link rel="stylesheet" type="text/css" href="main.css">
        <script src="main.js"></script>

        <?php
            // Respects 'Request Desktop Site'
			if (preg_match("/(iPhone|iPod|iPad|Android|BlackBerry)/i", $_SERVER["HTTP_USER_AGENT"])) {
				?><meta name="viewport" content="width=device-width, initial-scale=1.0"><?php
			}
        ?>
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
                <div id="loading" style="display: none;">
                </div>
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
        <?php
            include("php/secret.php");

            $PDO = getDatabase();
            $historical_crosswords = $PDO->prepare(
                "SELECT min(`date`) as min_date, max(`date`) as max_date
                FROM historical_crosswords"
            );
            $historical_crosswords->execute();
            $date_info = $historical_crosswords->fetch();
        ?>
        <div id="calendar">
            <input type="date" name="minDate" min="<?php echo $date_info['min_date']; ?>" max="<?php echo $date_info['max_date']; ?>" value="<?php echo $date_info['min_date']; ?>" onchange="dateChange()">
            -
            <input type="date" name="maxDate" min="<?php echo $date_info['min_date']; ?>" max="<?php echo $date_info['max_date']; ?>" value="<?php echo $date_info['max_date']; ?>" onchange="dateChange()">
            <button onclick="updateDates(this)" disabled>⟳</button>
        </div>
        <div id="num"></div>
    </body>
</html>