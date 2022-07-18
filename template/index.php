<!DOCTYPE html>
<html style="height:100%">
    <head>
        <meta charset="UTF-8" />
        <title>Auslastungs-Monitoring</title>

        <link href="<?= $baseUrl; ?>/public/css/w3.css" rel="stylesheet" />
        <link href="<?= $baseUrl; ?>/public/css/leaflet.css" rel="stylesheet" />
    </head>
    <body class="w3-light-gray" style="height:100%">
        <div class="w3-sidebar w3-white w3-container" style="width:20%">
            <h2>Filter</h2>
            <form>
                <div class="w3-row w3-padding-16">
                    <div class="w3-bar">
                        <div class="w3-bar-item" style="width:50%;padding-left:2px">
                            <label for="inputDateFrom">Von</label>
                            <input id="inputDateFrom" type="date" value="2021-11-01" class="w3-input w3-border inputFilter" />
                        </div>
                        <div class="w3-bar-item" style="width:50%;padding-right:2px">
                            <label for="inputDateUntil">Bis</label>
                            <input id="inputDateUntil" type="date" value="2021-11-30" class="w3-input w3-border inputFilter" />
                        </div>
                    </div>
                </div>
                <div class="w3-row w3-padding-16">
                    <label for="selectRouteName">Linie</label>
                    <select id="selectRouteName" class="w3-select inputFilter">
                        <?php foreach($routes as $route): ?>
                        <option value="<?= $route ?>">Linie <?= $route ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="w3-row w3-padding-16">
                    <label for="selectDayType">Tagesart</label>
                    <select id="selectDayType" class="w3-select inputFilter">
                        <option value="MoFr">Montag-Freitag</option>
                        <option value="Sa">Samstag</option>
                        <option value="So">Sonntag</option>
                    </select>
                </div>
                <div class="w3-row w3-padding-16">
                    <label for="selectDayType">Fahrtrichtung</label>
                    <select id="selectDirection" class="w3-select inputFilter">
                        <option value="0">Beide</option>
                        <option value="1">Hinfahrt</option>
                        <option value="2">Rückfahrt</option>
                    </select>
                </div>
                <div class="w3-row w3-padding-64">
                    <label>Auslastungsgrad</label>
                    <div class="w3-bar">
                        <button id="buttonFilterOccupationHigh" class="w3-bar-item w3-button w3-gray active buttonFilterOccupation" style="width:33%">Hoch</button>
                        <button id="buttonFilterOccupationMedium" class="w3-bar-item w3-button w3-light-gray buttonFilterOccupation" style="width:33%">Normal</button>
                        <button id="buttonFilterOccupationLow" class="w3-bar-item w3-button w3-light-gray buttonFilterOccupation" style="width:33%">Niedrig</button>
                    </div>
                </div>
            </form>
        </div>
        <main class="w3-main" style="margin-left:20%;height:100%;">
            <section id="sectionTripTable" class="w3-container" style="height:33%;min-height:250px;">
                <h3>Linie <span class="spanRouteName">0</span> / Richtung 1</h3>
                <div id="divTripsDirection1" class="w3-bar" style="overflow-x:scroll;white-space:nowrap;padding:8px 0;">
                </div>
                <h3>Linie <span class="spanRouteName">0</span> / Richtung 2</h3>
                <div id="divTripsDirection2" class="w3-bar" style="overflow-x:scroll;white-space:nowrap;padding:8px 0;">
                </div>
            </section>
            <section id="sectionMapView" style="height:67%;">
            </section>
        </main>
        <script type="text/javascript" src="<?= $baseUrl; ?>/public/js/jquery.js"></script>
        <script type="text/javascript" src="<?= $baseUrl; ?>/public/js/leaflet.js"></script>
        <script type="text/javascript" src="<?= $baseUrl; ?>/public/js/app.js"></script>
    </body>
</html>
